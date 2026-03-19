<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use Illuminate\Http\Request;
use App\Models\Upload;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PetitionController extends Controller
{
    public function create()
    {
        return view('user.petition_add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'petition_no' => 'required|unique:petitions,petition_no',
            'date_of_petition_received' => 'required|date',
            'nature_of_petition' => 'required',
            'mode_of_petition_received' => 'required',
            'description' => 'required',
            // Validate arrays if necessary
        ]);

        DB::beginTransaction();

        try {
            // 1. Create Petition
            $petition = Petition::create([
                'petition_no' => $request->petition_no,
                'date_of_petition_received' => $request->date_of_petition_received,
                'nature_of_petition' => $request->nature_of_petition,
                'mode_of_petition_received' => $request->mode_of_petition_received,
                'mode_of_petition_received_others' => $request->mode_others ?? null,
                'description' => $request->description,
                'proposed_action' => $request->proposed_action ?? null,
                'status' => 'Received',
            ]);

            // 2. Process Complainants & their Addresses
            if ($request->has('complainants') && is_array($request->complainants)) {
                $this->processPersons($request->complainants, 'Complainant', $petition->petition_id);
            }

            // 3. Process Accused & their Addresses
            if ($request->has('accused') && is_array($request->accused)) {
                $this->processPersons($request->accused, 'Accused', $petition->petition_id);
            }

            // 4. Process Uploads (Evidence Files)
            if ($request->hasFile('evidence_files')) {
                foreach ($request->file('evidence_files') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs("petitions/{$petition->petition_id}", $filename, 'public');

                    Upload::create([
                        'petition_id' => $petition->petition_id,
                        'uploadable_id' => $petition->petition_id,
                        'uploadable_type' => Petition::class,
                        'category' => 'Petition Document',
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'uploaded_by' => Auth::id() ?? 1, // Fallback if no auth 
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('petitions.index')->with('success', 'Petition submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save petition: ' . $e->getMessage())->withInput();
        }
    }

    private function processPersons($personsArray, $personType, $petitionId)
    {
        foreach ($personsArray as $person) {
            // Check if person actually has a name before processing
            if (empty($person['name'])) continue;

            $personName = $person['name'];
            $phone = $person['phone'] ?? null;
            $aadhar = $person['aadhar'] ?? null;

            if (isset($person['addresses']) && is_array($person['addresses'])) {
                foreach ($person['addresses'] as $index => $addr) {
                    if (empty($addr['address'])) continue; // skip empty addresses

                    Address::create([
                        'petition_id' => $petitionId,
                        'person_name' => $personName,
                        'person_type' => $personType,
                        'address_type' => $addr['type'] ?? 'Temporary',
                        'is_primary' => ($index === 0), // First address is primary
                        'phone' => $phone,
                        'Aadhar_number' => $aadhar,
                        'full_address' => $addr['address'],
                        'district' => $addr['district'] ?? '',
                        'pincode' => $addr['pincode'] ?? null,
                    ]);
                }
            }
        }
    }

    public function index()
    {
        $petitions = Petition::with('addresses')->orderBy('created_at', 'desc')->get();
        return view('user.petition_view', compact('petitions')); // Assuming index view name
    }

    public function show($id)
    {
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
        return view('user.petition_show', compact('petition')); // Assuming show view name
    }

    public function edit($id)
    {
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
        return view('user.petition_edit', compact('petition')); // Assuming edit view name
    }

    public function update(Request $request, $id)
    {
        $petition = Petition::findOrFail($id);

        $request->validate([
            'petition_no' => 'required|unique:petitions,petition_no,' . $id . ',petition_id',
            'date_of_petition_received' => 'required|date',
            'mode_of_petition_received' => 'required',

        ]);

        // Basic update for petition data. Handling full nested data update requires similar logic to store.
        $petition->update([
            'petition_no' => $request->petition_no,
            'date_of_petition_received' => $request->date_of_petition_received,
            'nature_of_petition' => $request->nature_of_petition,
            'mode_of_petition_received' => $request->mode_of_petition_received,
            'mode_of_petition_received_others' => $request->mode_others ?? null,
            'description' => $request->description,
            'proposed_action' => $request->proposed_action ?? $petition->proposed_action,
        ]);

        return redirect()->route('petitions.index')->with('success', 'Petition updated successfully.');
    }

    public function destroy($id)
    {
        $petition = Petition::findOrFail($id);
        
        // Delete uploads from storage
        foreach ($petition->uploads as $upload) {
            Storage::disk('public')->delete($upload->file_path);
        }
        
        $petition->delete(); // Addresses and Uploads should cascade delete if set up in DB or can trigger manually.

        return redirect()->route('petitions.index')->with('success', 'Petition deleted successfully.');
    }
}
