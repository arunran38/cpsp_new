<?php

namespace App\Services;

use App\Models\Petition;
use App\Models\Address;
use App\Models\Upload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Exception;

class PetitionService
{
    /**
     * Store a new petition.
     */
    public function store(array $data, array $files = []): Petition
    {
        return DB::transaction(function () use ($data, $files) {
            $user = Auth::user();
            if (!$user) {
                throw new Exception("User not authenticated.");
            }

            $seatId = null;
            if ($user->role !== 'admin' || session('is_impersonating_seat')) {
                $activeSeat = $user->currentSeatUser();
                if ($activeSeat) {
                    $seatId = $activeSeat->seat_id;
                }
            }

            $petition = Petition::create([
                'petition_no' => $data['petition_no'],
                'date_of_petition_received' => $data['date_of_petition_received'],
                'nature_of_petition' => $data['nature_of_petition'],
                'mode_of_petition_received' => $data['mode_of_petition_received'],
                'mode_of_petition_received_others' => $data['mode_others'] ?? null,
                'description' => $data['description'],
                'proposed_action' => $data['proposed_action'] ?? null,
                'status' => Petition::STATUS_RECEIVED,
                'user_id' => Auth::id(),
                'seat_id' => $seatId,
            ]);

            if (isset($data['complainants'])) {
                $this->processPersons($data['complainants'], 'Complainant', $petition->petition_id);
            }

            if (isset($data['accused'])) {
                $this->processPersons($data['accused'], 'Accused', $petition->petition_id);
            }

            if (!empty($files)) {
                $this->handleUploads($petition, $files);
            }

            return $petition;
        });
    }

    /**
     * Update an existing petition.
     */
    public function update(Petition $petition, array $data, array $files = [], array $deletedAttachments = []): Petition
    {
        return DB::transaction(function () use ($petition, $data, $files, $deletedAttachments) {
            $petition->update([
                'petition_no' => $data['petition_no'],
                'date_of_petition_received' => $data['date_of_petition_received'],
                'nature_of_petition' => $data['nature_of_petition'],
                'mode_of_petition_received' => $data['mode_of_petition_received'],
                'mode_of_petition_received_others' => $data['mode_others'] ?? null,
                'description' => $data['description'],
                'proposed_action' => $data['proposed_action'] ?? $petition->proposed_action,
            ]);

            // Rebuild addresses
            Address::where('petition_id', $petition->petition_id)->delete();

            if (isset($data['complainants'])) {
                $this->processPersons($data['complainants'], 'Complainant', $petition->petition_id);
            }

            if (isset($data['accused'])) {
                $this->processPersons($data['accused'], 'Accused', $petition->petition_id);
            }

            if (!empty($files)) {
                $this->handleUploads($petition, $files);
            }

            if (!empty($deletedAttachments)) {
                $this->handleDeletedAttachments($petition, $deletedAttachments);
            }

            return $petition;
        });
    }

    /**
     * Process multiple persons (Complainants/Accused) and their addresses.
     */
    public function processPersons(array $persons, string $type, int $petitionId): void
    {
        foreach ($persons as $person) {
            if (empty($person['name'])) {
                continue;
            }

            $personName = $person['name'];
            $phone = $person['phone'] ?? null;

            if (isset($person['addresses']) && is_array($person['addresses'])) {
                foreach ($person['addresses'] as $index => $addr) {
                    if (empty($addr['address'])) {
                        continue;
                    }

                    Address::create([
                        'petition_id' => $petitionId,
                        'person_name' => $personName,
                        'person_type' => $type,
                        'address_type' => $addr['address_type'] ?? 'Temporary',
                        'is_primary' => ($index === 0),
                        'phone' => $phone,
                        'full_address' => $addr['address'],
                        'district_id' => !empty($addr['district_id']) ? $addr['district_id'] : null,
                        'pincode' => $addr['pincode'] ?? null,
                    ]);
                }
            }
        }
    }

    /**
     * Handle file uploads for a petition.
     */
    private function handleUploads(Petition $petition, array $files): void
    {
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $path = Storage::disk('public')->putFileAs("petitions/{$petition->petition_id}", $file, $filename);

            Upload::create([
                'petition_id' => $petition->petition_id,
                'category' => Upload::CATEGORY_PETITION_DOCUMENT,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'uploaded_by' => Auth::id() ?? 1,
            ]);
        }
    }

    /**
     * Handle deletion of attachments.
     */
    private function handleDeletedAttachments(Petition $petition, array $uploadIds): void
    {
        foreach ($uploadIds as $id) {
            $upload = Upload::where('petition_id', $petition->petition_id)->find($id);
            if ($upload) {
                if (Storage::disk('public')->exists($upload->file_path)) {
                    Storage::disk('public')->delete($upload->file_path);
                }
                $upload->delete();
            }
        }
    }
}
