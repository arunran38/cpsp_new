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
use Illuminate\Http\UploadedFile;
use App\Models\Seat;

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
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            if (!$user) {
                throw new \Exception("User not authenticated.");
            }
            $activeSeat = $user->seatUsers()->where('is_active', true)->first();
            $seatId = $activeSeat ? $activeSeat->seat_id : null;

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
                'user_id' => Auth::id(),
                'seat_id' => $seatId,
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
                    if (!$file || !$file->isValid()) {
                        continue;
                    }

                    $id = $petition->petition_id;
                    $filename = time() . '_' . $file->getClientOriginalName();
                    
                    $path = Storage::disk('public')->putFileAs("petitions/{$id}", $file, $filename);

                    Upload::create([
                        'petition_id' => $petition->petition_id,
                        'category' => Upload::CATEGORY_PETITION_DOCUMENT,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'uploaded_by' => Auth::id() ?? 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('petitions.index')->with('success', 'Petition submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Petition Store Exception: ' . $e->getMessage());
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
        $petitions = $this->getPetitions($request);
        $tab = $request->get('tab', 'all');
        
        if ($request->ajax()) {
            return view('user.partials.reports_table', compact('petitions', 'tab'))->render();
        }

        return view('user.petition_view', compact('petitions', 'tab'));
    }

    public function reports(Request $request)
    {
        $petitions = $this->getPetitions($request);
        $tab = $request->get('tab', 'all');
        
        $seats = [];
        if (Auth::user()->role === 'admin') {
            $seats = Seat::where('is_active', true)->orderBy('seat_name')->get();
        }

        if ($request->ajax()) {
            return view('user.partials.reports_table', compact('petitions', 'tab'))->render();
        }

        return view('user.reports', compact('petitions', 'tab', 'seats'));
    }

    private function getPetitions(Request $request)
    {
        $query = Petition::with(['addresses', 'latestForwarding.toUnit', 'decision', 'user', 'seat']);
        
        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }
        
        $this->applyFilters($query, $request);
        
        return $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());
    }

    public function export(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $query = Petition::with(['addresses', 'latestForwarding.toUnit', 'decision']);

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

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
                    if ($request->status === 'All_Final_Decisions') {
                        $query->whereHas('decision');
                    } else {
                        $query->whereHas('decision', function($q) use ($request) {
                            $q->where('decision_remarks', $request->status);
                        });
                    }
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
        if (Auth::user()->role === 'admin') $columns[] = 'Seat';
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
                
                if (Auth::user()->role === 'admin') {
                    $row[] = $petition->seat->seat_name ?? 'N/A';
                }

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
        $status = $request->status;

        // 1. Tab-based status filtering
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

        // 2. Explicit status filter (if provided)
        if ($request->filled('status')) {
            $this->applyStatusFilter($query, $status, $tab);
        }

        // 3. Search filter
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

        // 4. Specific field filters
        if ($request->filled('petition_no')) {
            $query->where('petition_no', 'like', '%' . $request->petition_no . '%');
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

        if ($request->filled('seat_id')) {
            $query->where('seat_id', $request->seat_id);
        }

        // 5. Date filters (Status-aware)
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $this->applyDateFilters($query, $request);
        }
    }

    private function applyStatusFilter($query, $status, $tab)
    {
        $finalDecisionStatuses = ['PE', 'SC', 'QV', 'ICell', 'Closed', 'Sent to Govt'];

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
            if ($tab === 'received') {
                $query->where('status', 'Received');
            }
        } elseif ($status === 'All_Final_Decisions') {
            $query->whereHas('decision');
        } elseif (in_array($status, $finalDecisionStatuses)) {
            $query->whereHas('decision', function($q) use ($status) {
                $q->where('decision_remarks', $status);
            });
        } else {
            $query->where('status', $status);
        }
    }

    private function applyDateFilters($query, Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $status = $request->status;
        $tab = $request->get('tab', 'all');
        $finalDecisionStatuses = ['PE', 'SC', 'QV', 'Closed', 'Sent to Govt', 'ICell'];

        if ($status === 'Received' || $tab === 'received') {
            if ($dateFrom) $query->whereDate('date_of_petition_received', '>=', $dateFrom);
            if ($dateTo) $query->whereDate('date_of_petition_received', '<=', $dateTo);
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
        } elseif ($status === 'All_Final_Decisions' || in_array($status, $finalDecisionStatuses) || $tab === 'decisions') {
            $query->whereHas('decision', function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->whereDate('decision_date', '>=', $dateFrom);
                if ($dateTo) $q->whereDate('decision_date', '<=', $dateTo);
            });
        } else {
            // Default to date_of_petition_received (All Petitions case)
            if ($dateFrom) $query->whereDate('date_of_petition_received', '>=', $dateFrom);
            if ($dateTo) $query->whereDate('date_of_petition_received', '<=', $dateTo);
        }
    }

    public function show($id)
    {
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
        $this->authorizePetition($petition);
        return view('user.petition_show', compact('petition')); // Assuming show view name
    }

    public function edit($id)
    {
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
        $this->authorizePetition($petition);
        
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
                    'name' => (string)$name,
                    'phone' => (string)$first->phone,
                    'aadhar' => (string)$first->Aadhar_number,
                    'addresses' => $addresses->map(function ($addr) {
                        return [
                            'address_type' => (string)$addr->address_type,
                            'address' => (string)$addr->full_address,
                            'district' => (string)$addr->district,
                            'pincode' => (string)$addr->pincode,
                        ];
                    })->values()->all()
                ];
            })->values()->all();

        $accused = $petition->addresses->where('person_type', 'Accused')->groupBy('person_name')
            ->map(function ($addresses, $name) {
                $first = $addresses->first();
                return [
                    'id' => rand(1000, 9999),
                    'name' => (string)$name,
                    'phone' => (string)$first->phone,
                    'aadhar' => (string)$first->Aadhar_number,
                    'addresses' => $addresses->map(function ($addr) {
                        return [
                            'address_type' => (string)$addr->address_type,
                            'address' => (string)$addr->full_address,
                            'district' => (string)$addr->district,
                            'pincode' => (string)$addr->pincode,
                        ];
                    })->values()->all()
                ];
            })->values()->all();

        return view('user.petition_edit', compact('petition', 'complainants', 'accused'));
    }

    public function update(Request $request, $id)
    {
        $petition = Petition::findOrFail($id);
        $this->authorizePetition($petition);

        // Prevent update if final decision is taken
        if (in_array($petition->status, ['Closed', 'Sent_to_Govt'])) {
            return redirect()->route('petitions.index')->with('error', 'Cannot update a petition once a final decision has been taken.');
        }

        $request->validate([
            'petition_no' => 'required|unique:petitions,petition_no,' . $id . ',petition_id',
            'date_of_petition_received' => 'required|date',
            'nature_of_petition' => 'required',
            'mode_of_petition_received' => 'required',
            'description' => 'required',
        ]);

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
                    if (!$file || !$file->isValid()) continue;

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

            // 5. Delete marked uploads
            if ($request->has('deleted_attachments') && is_array($request->deleted_attachments)) {
                foreach ($request->deleted_attachments as $uploadId) {
                    $upload = Upload::where('petition_id', $petition->petition_id)->find($uploadId);
                    if ($upload) {
                        if (Storage::disk('public')->exists($upload->file_path)) {
                            Storage::disk('public')->delete($upload->file_path);
                        }
                        $upload->delete();
                    }
                }
            }

            DB::commit();
            return redirect()->route('petitions.index')->with('success', 'Petition updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Petition Update Error: ' . $e->getMessage());
            return back()->with('error', 'Update Failed: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $petition = Petition::findOrFail($id);
        $this->authorizePetition($petition);
        
        // Prevent deletion if final decision is taken
        if (in_array($petition->status, ['Closed', 'Sent_to_Govt'])) {
            return redirect()->route('petitions.index')->with('error', 'Cannot delete a petition after a final decision has been issued.');
        }

        $petition->delete(); // Addresses, Uploads, etc. will cascade soft delete via Model Events

        return redirect()->route('petitions.index')->with('success', 'Petition moved to trash successfully.');
    }

    private function authorizePetition(Petition $petition)
    {
        if (Auth::user()->role !== 'admin' && $petition->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this petition.');
        }
    }
}
