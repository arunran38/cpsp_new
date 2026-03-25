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

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $query = Petition::with(['addresses', 'latestForwarding.toUnit', 'decision']);
        
        $this->applyFilters($query, $request);
        
        $petitions = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());
        
        if ($request->ajax()) {
            return view('user.partials.reports_table', compact('petitions', 'tab'))->render();
        }

        return view('user.petition_view', compact('petitions', 'tab'));
    }

    public function reports(Request $request)
    {
        $tab = $request->get('tab', 'all'); // Keep tab for compatibility if needed
        $query = Petition::with(['addresses', 'latestForwarding.toUnit', 'decision']);

        $this->applyFilters($query, $request);

        $petitions = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        if ($request->ajax()) {
            return view('user.partials.reports_table', compact('petitions', 'tab'))->render();
        }

        return view('user.reports', compact('petitions', 'tab'));
    }

    public function export(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $query = Petition::with(['addresses', 'latestForwarding.toUnit', 'decision']);

        switch ($tab) {
            case 'all':
                // No status filter
                break;
            case 'received':
                $query->where('status', 'Received');
                break;
            case 'forwarded':
                $query->where('status', 'Forwarded');
                break;
            case 'vrs':
                $query->where('status', 'VR_Received');
                break;
            case 'decisions':
                $query->whereIn('status', ['Sent_to_Govt', 'Closed']);
                if ($request->filled('status')) {
                    $query->whereHas('decision', function($q) use ($request) {
                        $q->where('decision_remarks', $request->status);
                    });
                }
                break;
        }

        $this->applyFilters($query, $request);
        $petitions = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=petitions_report_" . ($tab ?? 'all') . "_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['#', 'Petition No', 'Received Date', 'Petitioner', 'Respondent', 'Nature', 'Mode', 'Status'];
        if ($tab === 'forwarded') $columns[] = 'Unit';
        if ($tab === 'vrs') { $columns[] = 'VR Ref No'; $columns[] = 'VR Date'; }
        if ($tab === 'decisions') $columns[] = 'Decision';

        $callback = function() use($petitions, $columns, $tab) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($petitions as $index => $petition) {
                $complainants = $petition->addresses->where('person_type', 'Complainant')->pluck('person_name')->implode(', ');
                $accused = $petition->addresses->where('person_type', 'Accused')->pluck('person_name')->implode(', ');

                $row = [
                    $index + 1,
                    $petition->petition_no,
                    $petition->date_of_petition_received,
                    $complainants,
                    $accused,
                    $petition->nature_of_petition,
                    $petition->mode_of_petition_received,
                    $petition->status,
                ];

                if ($tab === 'forwarded') {
                    $row[] = $petition->latestForwarding->toUnit->unit_name ?? 'N/A';
                }
                if ($tab === 'vrs') {
                    $row[] = $petition->latestForwarding->vr_ref_no ?? 'N/A';
                    $row[] = $petition->latestForwarding->vr_date ?? 'N/A';
                }
                if ($tab === 'decisions') {
                    $row[] = $petition->decision->decision_remarks ?? 'N/A';
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    private function applyFilters($query, Request $request)
    {
        $tab = $request->get('tab', 'all');
        
        switch ($tab) {
            case 'received':
                $query->where('status', 'Received');
                break;
            case 'forwarded':
                $query->where('status', 'Forwarded');
                break;
            case 'vrs':
                $query->where('status', 'VR_Received');
                break;
            case 'decisions':
                $query->whereIn('status', ['Sent_to_Govt', 'Closed']);
                break;
        }

        if ($request->filled('petition_no')) {
            $query->where('petition_no', 'like', '%' . $request->petition_no . '%');
        }

        // Status-based date filtering logic
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;
            $status = $request->status;
            $tab = $request->get('tab', 'all');

            $finalDecisionStatuses = ['PE', 'SC', 'QV', 'Closed', 'Sent to Govt', 'ICell'];

            if ($status === 'Received' || $tab === 'received') {
                if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
            } elseif ($status === 'Forwarded' || $tab === 'forwarded') {
                $query->whereHas('forwardings', function($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('forwarded_date', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('forwarded_date', '<=', $dateTo);
                });
            } elseif ($status === 'VR_Received' || $tab === 'vrs') {
                $query->whereHas('forwardings', function($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('vr_date', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('vr_date', '<=', $dateTo);
                });
            } elseif ($status === 'VR_Received_at_cpsp_date') {
                $query->whereHas('forwardings', function($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('vr_received_at_cpsp_date', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('vr_received_at_cpsp_date', '<=', $dateTo);
                });
            } elseif (in_array($status, $finalDecisionStatuses) || $tab === 'decisions') {
                $query->whereHas('decision', function($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('decision_date', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('decision_date', '<=', $dateTo);
                });
            }
        }

        if ($request->filled('complainant_name')) {
            $query->whereHas('addresses', function ($q) use ($request) {
                $q->where('person_type', 'Complainant')
                  ->where('person_name', 'like', '%' . $request->complainant_name . '%');
            });
        }
        if ($request->filled('respondent_name')) {
            $query->whereHas('addresses', function ($q) use ($request) {
                $q->where('person_type', 'Accused')
                  ->where('person_name', 'like', '%' . $request->respondent_name . '%');
            });
        }
        if ($request->filled('nature_of_petition')) {
            $query->where('nature_of_petition', 'like', '%' . $request->nature_of_petition . '%');
        }
        if ($request->filled('mode_of_petition')) {
            $query->where('mode_of_petition_received', $request->mode_of_petition);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            
            if ($status === 'Forwarded') {
                $query->whereHas('forwardings');
            } elseif ($status === 'VR_Received') {
                $query->whereHas('forwardings', function($q) {
                    $q->whereNotNull('vr_date');
                });
            } elseif ($status === 'VR_Received_at_cpsp_date') {
                $query->whereHas('forwardings', function($q) {
                    $q->whereNotNull('vr_received_at_cpsp_date');
                });
            } elseif ($status === 'Received') {
                // All petitions were received at some point, so we don't restrict by status
                // unless explicitly on the 'received' tab.
                if ($request->get('tab', 'all') === 'received') {
                    $query->where('status', 'Received');
                }
            } elseif (in_array($status, ['PE', 'SC', 'QV', 'ICell', 'Closed', 'Sent to Govt'])) {
                // These are decision-based statuses
                $query->whereHas('decision', function($q) use ($status) {
                    $q->where('decision_remarks', $status);
                });
            } else {
                $query->where('status', $status);
            }
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('petition_no', 'like', "%{$search}%")
                  ->orWhereHas('addresses', function ($q2) use ($search) {
                      $q2->whereIn('person_type', ['Complainant', 'Accused'])
                         ->where('person_name', 'like', "%{$search}%");
                  });
            });
        }
    }

    public function show($id)
    {
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
        return view('user.petition_show', compact('petition')); // Assuming show view name
    }

    public function edit($id)
    {
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
        
        // Prevent editing if final decision is taken
        if (in_array($petition->status, ['Closed', 'Sent_to_Govt'])) {
            return redirect()->route('petitions.index')->with('error', 'Cannot edit a petition once a final decision has been taken.');
        }

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

        // Prevent update if final decision is taken
        if (in_array($petition->status, ['Closed', 'Sent_to_Govt'])) {
            return redirect()->route('petitions.index')->with('error', 'Cannot update a petition once a final decision has been taken.');
        }

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
