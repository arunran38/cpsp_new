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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PetitionController extends Controller
{
    public function create()
    {
        return view('user.petition_add');
    }

    public function store(Request $request)
    {
        Log::info('Petition Store Request:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'petition_no' => 'required|unique:petitions,petition_no',
            'date_of_petition_received' => 'required|date',
            'nature_of_petition' => 'required',
            'mode_of_petition_received' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            Log::warning('Petition Validation Failed:', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

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
                        'address_type' => $addr['address_type'] ?? 'Temporary',
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
        $petitions = Petition::with(['addresses', 'latestForwarding', 'decision'])->orderBy('created_at', 'desc')->get();
        return view('user.petition_view', compact('petitions')); // Assuming index view name
    }

    public function forwardedPetitions()
    {
        $petitions = Petition::with(['addresses', 'latestForwarding.toUnit'])->where('status', 'Forwarded')->orderBy('updated_at', 'desc')->get();
        return view('user.forwardings_view', compact('petitions'));
    }

    public function verificationReports()
    {
        $petitions = Petition::with(['addresses', 'latestForwarding.toUnit'])->whereIn('status', ['VR_Received'])->orderBy('updated_at', 'desc')->get();
        return view('user.vr_view', compact('petitions'));
    }

    public function decisions()
    {
        $petitions = Petition::with(['addresses', 'decision'])->whereIn('status', ['Sent_to_Govt', 'Closed'])->orderBy('updated_at', 'desc')->get();
        return view('user.decisions_view', compact('petitions'));
    }

    public function show($id)
    {
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
        return view('user.petition_show', compact('petition')); // Assuming show view name
    }

    public function edit($id)
    {
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
        
        // Format addresses for Alpine.js
        $complainants = $petition->addresses->where('person_type', 'Complainant')->groupBy('person_name')
            ->map(function ($addresses, $name) {
                $first = $addresses->first();
                return [
                    'id' => rand(1000, 9999),
                    'name' => $name,
                    'phone' => $first->phone,
                    'aadhar' => $first->Aadhar_number,
                    'addresses' => $addresses->map(function ($addr) {
                        return [
                            'address_type' => $addr->address_type,
                            'address' => $addr->full_address,
                            'district' => $addr->district,
                            'pincode' => $addr->pincode,
                        ];
                    })->values()->toArray()
                ];
            })->values()->toArray();

        $accused = $petition->addresses->where('person_type', 'Accused')->groupBy('person_name')
            ->map(function ($addresses, $name) {
                $first = $addresses->first();
                return [
                    'id' => rand(1000, 9999),
                    'name' => $name,
                    'phone' => $first->phone,
                    'aadhar' => $first->Aadhar_number,
                    'addresses' => $addresses->map(function ($addr) {
                        return [
                            'address_type' => $addr->address_type,
                            'address' => $addr->full_address,
                            'district' => $addr->district,
                            'pincode' => $addr->pincode,
                        ];
                    })->values()->toArray()
                ];
            })->values()->toArray();

        return view('user.petition_edit', compact('petition', 'complainants', 'accused'));
    }

    public function update(Request $request, $id)
    {
        $petition = Petition::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'petition_no' => 'required|unique:petitions,petition_no,' . $id . ',petition_id',
            'date_of_petition_received' => 'required|date',
            'nature_of_petition' => 'required',
            'mode_of_petition_received' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // 1. Update Petition
            $petition->update([
                'petition_no' => $request->petition_no,
                'date_of_petition_received' => $request->date_of_petition_received,
                'nature_of_petition' => $request->nature_of_petition,
                'mode_of_petition_received' => $request->mode_of_petition_received,
                'mode_of_petition_received_others' => $request->mode_others ?? null,
                'description' => $request->description,
                'proposed_action' => $request->proposed_action ?? $petition->proposed_action,
            ]);

            // 2. Clear existing addresses to rebuild them
            Address::where('petition_id', $petition->petition_id)->delete();

            // 3. Re-process Complainants & Accused
            if ($request->has('complainants') && is_array($request->complainants)) {
                $this->processPersons($request->complainants, 'Complainant', $petition->petition_id);
            }

            if ($request->has('accused') && is_array($request->accused)) {
                $this->processPersons($request->accused, 'Accused', $petition->petition_id);
            }

            DB::commit();
            return redirect()->route('petitions.index')->with('success', 'Petition updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update Failed: ' . $e->getMessage())->withInput();
        }
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
