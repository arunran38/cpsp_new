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
        return view('user.petition_add', compact('districts'));
    }

    /**
     * Store a newly created petition in storage.
     */
    public function store(StorePetitionRequest $request): RedirectResponse
    {
        try {
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
            $seats = Seat::where('is_active', true)->orderBy('seat_name')->get();
        }

        if ($request->ajax()) {
            return view('user.partials.reports_table', compact('petitions', 'tab'))->render();
        }

        return view('user.reports', compact('petitions', 'tab', 'seats'));
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
        $columns = ['#', 'Petition No', 'Received Date', 'Petitioner', 'Respondent', 'Nature', 'Description', 'Mode', 'Status'];
        if (Auth::user()->role === 'admin') $columns[] = 'Seat';
        if ($tab === 'forwarded') $columns[] = 'Unit';
        if ($tab === 'vrs') { $columns[] = 'VR Ref No'; $columns[] = 'VR Date'; }
        if ($tab === 'decisions') $columns[] = 'Decision';

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
        $petition = Petition::with(['addresses', 'uploads'])->findOrFail($id);
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

        if (in_array($petition->status, [Petition::STATUS_CLOSED, Petition::STATUS_SENT_TO_GOVT])) {
            return redirect()->route('petitions.index')->with('error', 'Cannot edit a petition once a final decision has been taken.');
        }

        // Format addresses for Alpine.js
        $complainants = $this->formatAddressesForAlpine($petition, 'Complainant');
        $accused = $this->formatAddressesForAlpine($petition, 'Accused');

        $districts = \App\Models\District::orderBy('district_id')->get();
        return view('user.petition_edit', compact('petition', 'complainants', 'accused', 'districts'));
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

        if (in_array($petition->status, [Petition::STATUS_CLOSED, Petition::STATUS_SENT_TO_GOVT])) {
            return redirect()->route('petitions.index')->with('error', 'Cannot delete a petition after a final decision has been issued.');
        }

        $petition->delete();
        return redirect()->route('petitions.index')->with('success', 'Petition moved to trash successfully.');
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
        $request->validate(['petition_no' => 'required|string|max:255']);
        return response()->json(['exists' => Petition::where('petition_no', $request->petition_no)->exists()]);
    }

    /**
     * Helper to build the filtered petitions query.
     */
    private function getPetitionsQuery(Request $request)
    {
        $query = Petition::with(['addresses', 'latestForwarding.toUnit', 'decision', 'user', 'seat']);
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
              ->search($request->search)
              ->filterDates($request->date_from, $request->date_to, $request->status, $request->get('tab', 'all'));

        // Additional field filters
        if ($request->filled('petition_no')) {
            $query->where('petition_no', 'like', '%' . $request->petition_no . '%');
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
