<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\Upload;
use App\Services\HashidService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrashController extends Controller
{
    /**
     * Display a listing of trashed petitions.
     */
    public function index(Request $request): View
    {
        if (!Auth::user()->canAccess('view recycle bin')) {
            abort(403, 'Unauthorized access to recycle bin.');
        }

        $search = trim((string) $request->input('search'));
        $nature = $request->input('nature');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $baseTrashedQuery = Petition::onlyTrashed()->excludeTSectionIfNotAuthorized(Auth::user());

        $query = (clone $baseTrashedQuery)
            ->with([
                'user',
                'seat',
                'unit',
                'updatedByUser',
                'updatedBySeat',
                'addresses' => fn($q) => $q->withTrashed()->with(['district', 'designation', 'department']),
                'uploads' => fn($q) => $q->withTrashed(),
            ]);

        // Search across receipt_no, file_no, description, complainant name, or phone
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('file_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('addresses', function ($aq) use ($search) {
                      $aq->withTrashed()
                         ->where('person_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($nature)) {
            $query->where('nature_of_petition', $nature);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('deleted_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('deleted_at', '<=', $dateTo);
        }

        $trashedPetitions = $query->orderBy('deleted_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Metrics for top cards
        $totalTrashed = (clone $baseTrashedQuery)->count();
        $trashedThisMonth = (clone $baseTrashedQuery)->where('deleted_at', '>=', now()->startOfMonth())->count();
        $trashedWithFiles = (clone $baseTrashedQuery)->whereHas('uploads', fn($q) => $q->withTrashed())->count();

        // Available natures for filter
        $availableNatures = (clone $baseTrashedQuery)
            ->whereNotNull('nature_of_petition')
            ->where('nature_of_petition', '!=', '')
            ->distinct()
            ->orderBy('nature_of_petition')
            ->pluck('nature_of_petition');

        return view('admin.trash', compact(
            'trashedPetitions',
            'totalTrashed',
            'trashedThisMonth',
            'trashedWithFiles',
            'availableNatures',
            'search',
            'nature',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Display or return JSON details of the specified soft-deleted petition.
     */
    public function show(Request $request, string|int $id): View|JsonResponse
    {
        if (!Auth::user()->canAccess('view recycle bin')) {
            abort(403, 'Unauthorized access to recycle bin.');
        }

        $realId = HashidService::decode($id) ?? $id;

        $petition = Petition::onlyTrashed()->with([
            'user',
            'seat',
            'unit',
            'updatedByUser',
            'updatedBySeat',
            'addresses' => fn($q) => $q->withTrashed()->with(['district', 'designation', 'department']),
            'uploads' => fn($q) => $q->withTrashed(),
            'originalPetition' => fn($q) => $q->withTrashed(),
        ])->findOrFail($realId);

        if ($request->wantsJson() || $request->ajax()) {
            $complainants = $petition->addresses
                ->where('person_type', 'Complainant')
                ->values()
                ->map(function ($addr) {
                    return [
                        'name' => $addr->person_name ?: 'N/A',
                        'phone' => $addr->phone ?: null,
                        'email' => $addr->email ?: null,
                        'aadhar' => $addr->Aadhar_number ?: null,
                        'district' => $addr->district->district_name ?? null,
                        'address' => $addr->full_address ?: null,
                        'pincode' => $addr->pincode ?: null,
                    ];
                });

            $accused = $petition->addresses
                ->where('person_type', 'Accused')
                ->values()
                ->map(function ($addr) {
                    return [
                        'name' => $addr->person_name ?: 'N/A',
                        'designation' => $addr->designation->designation_name ?? null,
                        'department' => $addr->department->department_name ?? null,
                        'phone' => $addr->phone ?: null,
                        'district' => $addr->district->district_name ?? null,
                        'address' => $addr->full_address ?: null,
                    ];
                });

            $uploads = $petition->uploads->map(function ($upload) {
                return [
                    'id' => $upload->upload_id,
                    'name' => $upload->original_filename ?: basename($upload->file_path),
                    'category' => $upload->file_category ?? 'Document',
                    'download_url' => route('admin.trash.download', $upload->upload_id),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $petition->petition_id,
                    'receipt_no' => $petition->receipt_no ?? ('ID #' . $petition->petition_id),
                    'file_no' => $petition->file_no,
                    'nature' => $petition->nature_of_petition,
                    'mode' => $petition->mode_of_petition_received,
                    'status' => $petition->status,
                    'date_received' => $petition->date_of_petition_received ? Carbon::parse($petition->date_of_petition_received)->format('d M, Y') : null,
                    'deleted_at' => Carbon::parse($petition->deleted_at)->format('d M, Y h:i A'),
                    'deleted_at_human' => Carbon::parse($petition->deleted_at)->diffForHumans(),
                    'creator' => $petition->user->name ?? 'Unknown',
                    'seat' => $petition->seat->seat_name ?? 'N/A',
                    'unit' => $petition->unit->unit_name ?? 'N/A',
                    'description' => $petition->description ?: 'No description provided.',
                    'proposed_action' => $petition->proposed_action ?: 'None',
                    'complainants' => $complainants,
                    'accused' => $accused,
                    'uploads' => $uploads,
                    'restore_url' => route('admin.trash.restore', $petition->petition_id),
                    'force_delete_url' => route('admin.trash.forceDelete', $petition->petition_id),
                    'can_restore' => Auth::user()->canAccess('restore recycle bin'),
                    'can_delete' => Auth::user()->canAccess('delete recycle bin'),
                ]
            ]);
        }

        return view('admin.trash_show', compact('petition'));
    }

    /**
     * Download attachment for a trashed petition.
     */
    public function downloadAttachment(string|int $id)
    {
        if (!Auth::user()->canAccess('view recycle bin')) {
            abort(403, 'Unauthorized access to recycle bin.');
        }

        $realUploadId = HashidService::decode($id) ?? $id;
        $upload = Upload::withTrashed()->findOrFail($realUploadId);

        if (!Storage::disk('public')->exists($upload->file_path)) {
            abort(404, 'File not found on storage.');
        }

        return response()->download(
            Storage::disk('public')->path($upload->file_path),
            $upload->original_filename ?? basename($upload->file_path)
        );
    }

    /**
     * Restore the specified soft-deleted petition.
     */
    public function restore(string|int $id): RedirectResponse
    {
        if (!Auth::user()->canAccess('restore recycle bin')) {
            abort(403, 'Unauthorized to restore petitions.');
        }

        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::onlyTrashed()->findOrFail($realId);
        $petition->restore();

        $receiptNo = $petition->receipt_no ?? ('ID ' . $petition->petition_id);
        return redirect()->route('admin.trash.index')->with('success', 'Petition #'.$receiptNo.' restored successfully.');
    }

    /**
     * Permanently remove the specified petition and its files.
     */
    public function forceDelete(string|int $id): RedirectResponse
    {
        if (!Auth::user()->canAccess('delete recycle bin')) {
            abort(403, 'Unauthorized to permanently delete petitions.');
        }

        $realId = HashidService::decode($id) ?? $id;

        return DB::transaction(function () use ($realId) {
            try {
                $petition = Petition::withTrashed()
                    ->with(['uploads' => fn($q) => $q->withTrashed()])
                    ->findOrFail($realId);

                $receiptNo = $petition->receipt_no ?? ('ID ' . $petition->petition_id);

                foreach ($petition->uploads as $upload) {
                    if ($upload->file_path && Storage::disk('public')->exists($upload->file_path)) {
                        Storage::disk('public')->delete($upload->file_path);
                    }
                }
                
                $petition->forceDelete();
                return redirect()->route('admin.trash.index')->with('success', 'Petition #'.$receiptNo.' permanently deleted.');
            } catch (\Exception $e) {
                return redirect()->route('admin.trash.index')->with('error', 'Failed to permanently delete petition.');
            }
        });
    }
}
