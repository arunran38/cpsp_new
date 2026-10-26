<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TrashController extends Controller
{
    /**
     * Display a listing of trashed petitions.
     */
    public function index(): View
    {
        if (!Auth::user()->canAccess('view recycle bin')) {
            abort(403, 'Unauthorized access to recycle bin.');
        }

        $trashedPetitions = Petition::onlyTrashed()->with(['user', 'seat'])->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.trash', compact('trashedPetitions'));
    }

    /**
     * Restore the specified soft-deleted petition.
     */
    public function restore(int $id): RedirectResponse
    {
        if (!Auth::user()->canAccess('restore recycle bin')) {
            abort(403, 'Unauthorized to restore petitions.');
        }

        $petition = Petition::withTrashed()->findOrFail($id);
        $petition->restore();

        return redirect()->route('admin.trash.index')->with('success', 'Petition #'.$petition->petition_no.' restored successfully.');
    }

    /**
     * Permanently remove the specified petition and its files.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        if (!Auth::user()->canAccess('delete recycle bin')) {
            abort(403, 'Unauthorized to permanently delete petitions.');
        }

        return DB::transaction(function () use ($id) {
            try {
                $petition = Petition::withTrashed()->with(['uploads' => fn($q) => $q->withTrashed()])->findOrFail($id);

                foreach ($petition->uploads as $upload) {
                    if (Storage::disk('public')->exists($upload->file_path)) {
                        Storage::disk('public')->delete($upload->file_path);
                    }
                }
                
                $petition->forceDelete();
                return redirect()->route('admin.trash.index')->with('success', 'Petition permanently deleted.');
            } catch (\Exception $e) {
                return redirect()->route('admin.trash.index')->with('error', 'Failed to permanently delete petition.');
            }
        });
    }
}
