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
            $activeSeat = $user->currentSeatUser();
            if ($activeSeat) {
                $seatId = $activeSeat->seat_id;
            }

            $petition = Petition::create([
                'receipt_no' => $data['receipt_no'],
                'date_of_petition_received' => $data['date_of_petition_received'],
                'nature_of_petition' => $data['nature_of_petition'],
                'mode_of_petition_received' => $data['mode_of_petition_received'],
                'mode_of_petition_received_others' => $data['mode_others'] ?? null,
                'description' => $data['description'],
                'proposed_action' => $data['proposed_action'] ?? null,
                'status' => Petition::STATUS_RECEIVED,
                'user_id' => Auth::id(),
                'seat_id' => $seatId,
                'linked_petition_id' => $data['linked_petition_id'] ?? null,
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
                'receipt_no' => $data['receipt_no'],
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
            $email = $person['email'] ?? null;
            $entityType = $person['entity_type'] ?? 'Person';
            $designationId = $person['designation_id'] ?? null;
            $departmentId = $person['department_id'] ?? null;
            $penNumber = $person['pen_number'] ?? null;

            $hasValidAddress = false;
            if (isset($person['addresses']) && is_array($person['addresses'])) {
                foreach ($person['addresses'] as $index => $addr) {
                    if (empty($addr['address'])) {
                        continue;
                    }

                    $hasValidAddress = true;
                    Address::create([
                        'petition_id' => $petitionId,
                        'person_name' => $personName,
                        'person_type' => $type,
                        'entity_type' => $entityType,
                        'designation_id' => $designationId,
                        'department_id' => $departmentId,
                        'address_type' => $addr['address_type'] ?? 'Temporary',
                        'is_primary' => ($index === 0),
                        'phone' => $phone,
                        'email' => $email,
                        'pen_number' => $penNumber,
                        'full_address' => $addr['address'],
                        'district_id' => !empty($addr['district_id']) ? $addr['district_id'] : null,
                        'pincode' => $addr['pincode'] ?? null,
                    ]);
                }
            }

            // Ensure the person is saved even if no valid address is provided
            if (!$hasValidAddress) {
                Address::create([
                    'petition_id' => $petitionId,
                    'person_name' => $personName,
                    'person_type' => $type,
                    'entity_type' => $entityType,
                    'designation_id' => $designationId,
                    'department_id' => $departmentId,
                    'address_type' => 'Temporary',
                    'is_primary' => true,
                    'phone' => $phone,
                    'email' => $email,
                    'pen_number' => $penNumber,
                    'full_address' => 'Not provided',
                    'district_id' => null,
                    'pincode' => null,
                ]);
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
