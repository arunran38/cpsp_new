<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\Upload;
use App\Models\Seat;
use App\Http\Requests\StorePetitionRequest;
use App\Http\Requests\UpdatePetitionRequest;
use App\Services\PetitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PetitionController extends Controller
{
    use AuthorizesRequests;

    protected $petitionService;

    public function __construct(PetitionService $petitionService)
    {
        $this->petitionService = $petitionService;
    }

    /**
     * Show the form for creating a new petition.
     */
    public function create(): View
    {
        $districts = \App\Models\District::orderBy('district_id')->get();
        $designations = \App\Models\DesignationList::orderBy('designation_name')->get();
        $departments = \App\Models\DepartmentList::orderBy('department_name')->get();
        return view('user.petition_add', compact('districts', 'designations', 'departments'));
    }

    /**
     * Store a newly created petition in storage.
     */
    public function store(StorePetitionRequest $request): RedirectResponse
    {
        try {
            if ($request->filled('duplicate_link_number')) {
                $originalPetition = Petition::where('receipt_no', $request->input('duplicate_link_number'))->first();
                
                if ($originalPetition) {
                    $petition = new Petition();
                    $petition->receipt_no = $request->receipt_no;
                    $petition->date_of_petition_received = $request->date_of_petition_received;
                    
                    // Set minimal required fields for duplicate stub
                    $petition->mode_of_petition_received = 'others';
                    $petition->mode_of_petition_received_others = 'Duplicate Entry';
                    $petition->nature_of_petition = 'others';
                    $petition->description = 'Registered as a duplicate of Receipt No: ' . $originalPetition->receipt_no;
                    
                    $petition->linked_petition_id = $originalPetition->petition_id;
                    $petition->user_id = auth()->id();
                    $petition->status = Petition::STATUS_CLOSED; // Auto-close duplicate entry
                    $petition->save();
                }
                
                return redirect()->route('petitions.create');
            }

            $this->petitionService->store($request->validated(), $request->file('evidence_files') ?? []);
            
            return redirect()->route('petitions.index')->with('success', 'Petition submitted successfully.');
        } catch (\Exception $e) {
            Log::error('Petition Store Exception: ' . $e->getMessage());
            return back()->with('error', 'Failed to save petition: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display a listing of petitions.
     */
    public function index(Request $request)
    {
        $petitions = $this->getPetitionsQuery($request)->paginate(10)->appends($request->query());
        $tab = $request->get('tab', 'all');

        if ($request->ajax()) {
            return view('user.partials.reports_table', compact('petitions', 'tab'))->render();
        }

        return view('user.petition_view', compact('petitions', 'tab'));
    }

    /**
     * Display the reports view.
     */
    public function reports(Request $request)
    {
        $petitions = $this->getPetitionsQuery($request)->paginate(10)->appends($request->query());
        $tab = $request->get('tab', 'all');

        $seats = [];
        if (Auth::user()->role === 'admin') {
            $seats = Seat::where('is_active', true)->get()->sortBy('seat_name', SORT_NATURAL | SORT_FLAG_CASE);
        }

        $departments = \App\Models\DepartmentList::orderBy('department_name')->pluck('department_name', 'id')->toArray();

        if ($request->ajax()) {
            return view('user.partials.reports_table', compact('petitions', 'tab'))->render();
        }

        return view('user.reports', compact('petitions', 'tab', 'seats', 'departments'));
    }

    /**
     * Export petitions to Excel.
     */
    public function export(Request $request): HttpResponse
    {
        $tab = $request->get('tab', 'all');
        $petitions = $this->getPetitionsQuery($request)->get();

        $filename = "petitions_report_" . $tab . "_" . date('Y-m-d') . ".xls";
        
        // Prepare metadata for the export view
        $requestedColumns = $request->get('custom_columns');
        if ($requestedColumns && is_array($requestedColumns)) {
            $columns = $requestedColumns;
        } else {
            $columns = ['#', 'Receipt No', 'Received Date', 'Complainant Name & Address', 'Suspect Name & Address', 'Nature', 'Description', 'Mode', 'Proposed Action', 'Present Status', 'Final Recommendation'];
            if (Auth::user()->role === 'admin') $columns[] = 'Seat';
            if ($tab === 'forwarded') $columns[] = 'Unit';
            if ($tab === 'vrs') { $columns[] = 'VR Ref No'; $columns[] = 'VR Date'; }
            if ($tab === 'decisions') $columns[] = 'Decision';
        }

        $minDate = $petitions->min('date_of_petition_received');
        $maxDate = $petitions->max('date_of_petition_received');
        $dateFrom = $request->filled('date_from') ? date('d/m/Y', strtotime($request->date_from)) : ($minDate ? date('d/m/Y', strtotime($minDate)) : '...');
        $dateTo = $request->filled('date_to') ? date('d/m/Y', strtotime($request->date_to)) : ($maxDate ? date('d/m/Y', strtotime($maxDate)) : date('d/m/Y'));
        $mainHeading = "DETAILS OF PETITIONS RECEIVED FROM $dateFrom TO $dateTo";

        $content = view('exports.petitions_export', compact('petitions', 'tab', 'columns', 'mainHeading'))->render();

        return response($content)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Display the specified petition.
     */
    public function show($id): View
    {
        $petition = Petition::with([
            'addresses.designation', 
            'addresses.department', 
            'uploads', 
            'originalPetition.addresses.designation', 
            'originalPetition.addresses.department', 
            'duplicates'
        ])->findOrFail($id);
        $this->authorize('view', $petition);
        return view('user.petition_show', compact('petition'));
    }

    /**
     * Show the form for editing the specified petition.
     */
    public function edit($id): View|RedirectResponse
    {
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
        $this->authorize('update', $petition);

        if (in_array($petition->status, [Petition::STATUS_CLOSED, Petition::STATUS_SENT_TO_GOVT]) && Auth::user()->role !== 'admin') {
            return redirect()->route('petitions.index')->with('error', 'Cannot edit a petition once a final decision has been taken.');
        }

        // Format addresses for Alpine.js
        $complainants = $this->formatAddressesForAlpine($petition, 'Complainant');
        $accused = $this->formatAddressesForAlpine($petition, 'Accused');

        $districts = \App\Models\District::orderBy('district_id')->get();
        $designations = \App\Models\DesignationList::orderBy('designation_name')->get();
        $departments = \App\Models\DepartmentList::orderBy('department_name')->get();
        return view('user.petition_edit', compact('petition', 'complainants', 'accused', 'districts', 'designations', 'departments'));
    }

    /**
     * Update the specified petition in storage.
     */
    public function update(UpdatePetitionRequest $request, $id): RedirectResponse
    {
        $petition = Petition::findOrFail($id);
        $this->authorize('update', $petition);

        try {
            $this->petitionService->update(
                $petition, 
                $request->validated(), 
                $request->file('evidence_files') ?? [], 
                $request->deleted_attachments ?? []
            );
            return redirect()->route('petitions.index')->with('success', 'Petition updated successfully.');
        } catch (\Exception $e) {
            Log::error('Petition Update Error: ' . $e->getMessage());
            return back()->with('error', 'Update Failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified petition from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $petition = Petition::findOrFail($id);
        $this->authorize('delete', $petition);

        if (in_array($petition->status, [Petition::STATUS_CLOSED, Petition::STATUS_SENT_TO_GOVT]) && Auth::user()->role !== 'admin') {
            return redirect()->route('petitions.index')->with('error', 'Cannot delete a petition after a final decision has been issued.');
        }

        $petition->delete();
        return redirect()->route('petitions.index')->with('success', 'Petition moved to trash successfully.');
    }

    /**
     * Link an existing petition to another as a duplicate.
     */
    public function linkDuplicate(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'original_receipt_no' => 'required|string',
        ]);

        $petition = Petition::findOrFail($id);
        $this->authorize('update', $petition);

        if ($petition->linked_petition_id) {
            return back()->with('error', 'This petition is already linked as a duplicate.');
        }

        $originalPetition = Petition::where('receipt_no', $request->original_receipt_no)->first();

        if (!$originalPetition) {
            return back()->with('error', 'Original petition not found. Please check the receipt number.');
        }

        if ($originalPetition->petition_id === $petition->petition_id) {
            return back()->with('error', 'Cannot link a petition to itself.');
        }

        // If the target petition is already a duplicate, resolve to its root original petition
        while ($originalPetition->linked_petition_id) {
            $originalPetition = Petition::find($originalPetition->linked_petition_id);
            if (!$originalPetition) {
                return back()->with('error', 'Could not resolve the root original petition.');
            }
        }

        $petition->update([
            'linked_petition_id' => $originalPetition->petition_id,
            'previous_status' => $petition->status,
            'status' => Petition::STATUS_DUPLICATE,
        ]);

        // Merge Details: Replicate Addresses (Suspects/Complainants)
        foreach ($petition->addresses as $address) {
            $newAddress = $address->replicate();
            $newAddress->petition_id = $originalPetition->petition_id;
            $newAddress->save();
        }

        // Merge Details: Replicate Uploads (Attachments)
        foreach ($petition->uploads as $upload) {
            $newUpload = $upload->replicate();
            $newUpload->petition_id = $originalPetition->petition_id;
            $newUpload->save();
        }

        return redirect()->route('petitions.show', $petition->petition_id)->with('success', 'Petition successfully linked as a duplicate of ' . $originalPetition->receipt_no . '. The suspects and attachments have been copied to the original petition.');
    }

    /**
     * Unlink a petition that was mistakenly marked as duplicate.
     */
    public function unlinkDuplicate(Request $request, $id): RedirectResponse
    {
        $petition = Petition::findOrFail($id);
        $this->authorize('update', $petition);

        if (!$petition->linked_petition_id) {
            return back()->with('error', 'This petition is not currently linked to any other petition.');
        }

        $originalPetition = $petition->originalPetition;

        $petition->update([
            'linked_petition_id' => null,
            'status' => $petition->previous_status ?: Petition::STATUS_RECEIVED,
            'previous_status' => null,
        ]);

        if ($originalPetition) {
            return redirect()->route('petitions.show', $originalPetition->petition_id)->with('success', 'Petition has been unlinked successfully and its previous status restored.');
        }

        return redirect()->route('petitions.index')->with('success', 'Petition has been unlinked successfully and its previous status restored.');
    }

    /**
     * Search petitions for the autocomplete link duplicate feature
     */
    public function searchDuplicates(Request $request)
    {
        $query = $request->get('q');
        $excludeId = $request->get('exclude');

        if (!$query) {
            return response()->json([]);
        }

        $petitions = Petition::where(function($q) use ($query) {
            $q->where('receipt_no', 'LIKE', "%{$query}%")
              ->orWhere('nature_of_petition', 'LIKE', "%{$query}%")
              ->orWhereHas('addresses', function ($q2) use ($query) {
                  $q2->where('person_name', 'LIKE', "%{$query}%");
              });
        });

        if ($excludeId) {
            $petitions->where('petition_id', '!=', $excludeId);
        }

        // We allow linking to duplicate petitions; the backend will automatically resolve to the root original.

        $results = $petitions->take(10)->get()->map(function ($petition) {
            $complainant = $petition->addresses->where('person_type', 'Complainant')->first();
            $name = $complainant ? $complainant->person_name : 'No Name';
            return [
                'id' => $petition->receipt_no, // We return receipt_no as id because the form submits original_receipt_no
                'text' => "{$petition->receipt_no} - {$petition->nature_of_petition} ({$name})"
            ];
        });

        return response()->json($results);
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment($uploadId)
    {
        $upload = Upload::findOrFail($uploadId);
        
        if ($upload->petition_id) {
            $petition = Petition::findOrFail($upload->petition_id);
            $this->authorize('view', $petition);
        } elseif (Auth::user()->role !== 'admin' && $upload->uploaded_by !== Auth::id()) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($upload->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($upload->file_path, $upload->original_filename);
    }

    /**
     * Check if a petition number exists.
     */
    public function checkPetitionNo(Request $request)
    {
        $request->validate(['receipt_no' => 'required|string|max:255']);
        $exists = Petition::where('receipt_no', $request->receipt_no);
        if ($request->filled('petition_id')) {
            $exists->where('petition_id', '!=', $request->petition_id);
        }
        return response()->json(['exists' => $exists->exists()]);
    }

    /**
     * Check if a file number exists.
     */
    public function checkFileNo(Request $request)
    {
        $request->validate([
            'file_no' => 'required|string|max:255',
            'petition_id' => 'required|integer'
        ]);
        return response()->json(['exists' => Petition::where('file_no', $request->file_no)->where('petition_id', '!=', $request->petition_id)->exists()]);
    }

    /**
     * Check for potential duplicate petitions based on names, phones, and PENs.
     */
    public function checkDuplicates(Request $request)
    {
        $complainants = $request->input('complainants', []);
        $accused = $request->input('accused', []);

        $c_phones = collect($complainants)->pluck('phone')->filter()->toArray();
        $c_names = collect($complainants)->pluck('name')->filter()->map(fn($n) => trim($n))->toArray();

        $a_phones = collect($accused)->pluck('phone')->filter()->toArray();
        $a_pens = collect($accused)->pluck('pen_number')->filter()->toArray();
        $a_names = collect($accused)->pluck('name')->filter()->map(fn($n) => trim($n))->toArray();

        // Need at least one identifier from both sides to find a meaningful duplicate
        if ((empty($c_names) && empty($c_phones)) || (empty($a_names) && empty($a_phones) && empty($a_pens))) {
            return response()->json([]);
        }

        $query = Petition::query()->with(['decision', 'addresses.district']);

        // Must match a complainant
        $query->whereHas('addresses', function ($q) use ($c_phones, $c_names) {
            $q->where('person_type', 'Complainant');
            $q->where(function ($sub) use ($c_phones, $c_names) {
                if (!empty($c_phones)) {
                    $sub->orWhereIn('phone', $c_phones);
                }
                if (!empty($c_names)) {
                    foreach ($c_names as $name) {
                        if (strlen($name) > 3) {
                            $sub->orWhere('person_name', 'LIKE', '%' . $name . '%');
                        }
                    }
                }
            });
        });

        // Must ALSO match an accused
        $query->whereHas('addresses', function ($q) use ($a_phones, $a_pens, $a_names) {
            $q->where('person_type', 'Accused');
            $q->where(function ($sub) use ($a_phones, $a_pens, $a_names) {
                if (!empty($a_phones)) {
                    $sub->orWhereIn('phone', $a_phones);
                }
                if (!empty($a_pens)) {
                    $sub->orWhereIn('pen_number', $a_pens);
                }
                if (!empty($a_names)) {
                    foreach ($a_names as $name) {
                        if (strlen($name) > 3) {
                            $sub->orWhere('person_name', 'LIKE', '%' . $name . '%');
                        }
                    }
                }
            });
        });

        $duplicates = $query->orderBy('date_of_petition_received', 'desc')->take(5)->get()->map(function($p) {
            $complainants = $p->addresses->where('person_type', 'Complainant');
            $primaryComplainant = $complainants->first();
            $complainantText = $primaryComplainant ? $primaryComplainant->person_name . ($primaryComplainant->district ? ', ' . $primaryComplainant->district->district_name : '') : 'N/A';
            if ($complainants->count() > 1) {
                $complainantText .= ' (+' . ($complainants->count() - 1) . ' others)';
            }

            $accused = $p->addresses->where('person_type', 'Accused');
            $primaryAccused = $accused->first();
            $accusedText = $primaryAccused ? $primaryAccused->person_name . ($primaryAccused->district ? ', ' . $primaryAccused->district->district_name : '') : 'N/A';
            if ($accused->count() > 1) {
                $accusedText .= ' (+' . ($accused->count() - 1) . ' others)';
            }

            return [
                'petition_id' => $p->petition_id,
                'receipt_no' => $p->receipt_no,
                'date' => date('d-m-Y', strtotime($p->date_of_petition_received)),
                'complainant' => $complainantText,
                'accused' => $accusedText,
                'description' => \Illuminate\Support\Str::limit(strip_tags($p->description), 100),
                'status' => $p->status,
                'decision' => $p->decision ? $p->decision->decision_remarks : null,
            ];
        });

        return response()->json($duplicates);
    }

    /**
     * Helper to build the filtered petitions query.
     */
    private function getPetitionsQuery(Request $request)
    {
        $query = Petition::with(['addresses', 'latestForwarding.toUnit', 'latestForwarding.processedBy', 'decision.processedBy', 'user', 'seat', 'originalPetition.addresses']);
        $user = Auth::user();

        // Security: Filter by user/seat if not unrestricted admin
        if ($user->role !== 'admin' || session('is_impersonating_seat')) {
            $currentSeat = $user->currentSeatUser();
            if ($currentSeat) {
                $query->where('seat_id', $currentSeat->seat_id);
            } else {
                $query->where('user_id', $user->id);
            }
        }

        // Apply filters using scopes
        $query->filterByTab($request->get('tab', 'all'))
              ->filterByStatus($request->status, $request->get('tab', 'all'))
              ->search($request->search, $request->search_type)
              ->filterDates($request->date_from, $request->date_to, $request->status, $request->get('tab', 'all'));

        // Hide duplicate (linked) petitions from general list, unless explicitly searched
        if (!$request->filled('receipt_no') && !$request->filled('search')) {
            $query->whereNull('linked_petition_id');
        }

        // Additional field filters
        if ($request->filled('receipt_no')) {
            $query->where('receipt_no', 'like', '%' . $request->receipt_no . '%');
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
        if ($request->filled('department_id')) {
            $query->whereHas('addresses', fn($q) => $q->where('department_id', $request->department_id));
        }

        // Address-based filters
        if ($request->filled('complainant_name')) {
            $query->whereHas('addresses', fn($q) => $q->where('person_type', 'Complainant')->where('person_name', 'like', '%' . $request->complainant_name . '%'));
        }
        if ($request->filled('respondent_name')) {
            $query->whereHas('addresses', fn($q) => $q->where('person_type', 'Accused')->where('person_name', 'like', '%' . $request->respondent_name . '%'));
        }

        return $query->latest();
    }

    /**
     * Format addresses for Alpine.js editing.
     */
    private function formatAddressesForAlpine(Petition $petition, string $type): array
    {
        return $petition->addresses->where('person_type', $type)->groupBy('person_name')
            ->map(function ($addresses, $name) {
                $first = $addresses->first();
                return [
                    'id' => rand(1000, 9999),
                    'name' => (string) $name,
                    'phone' => (string) $first->phone,
                    'entity_type' => $first->entity_type ?? 'Person',
                    'designation_id' => (string) $first->designation_id,
                    'department_id' => (string) $first->department_id,
                    'pen_number' => (string) $first->pen_number,
                    'addresses' => $addresses->map(fn($addr) => [
                        'address_type' => (string) $addr->address_type,
                        'address' => (string) $addr->full_address,
                        'district_id' => (string) $addr->district_id,
                        'pincode' => (string) $addr->pincode,
                    ])->values()->all()
                ];
            })->values()->all();
    }
}
