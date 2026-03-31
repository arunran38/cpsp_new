<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petition;
use Illuminate\Support\Facades\Storage;

class TrashController extends Controller
{
    /**
     * Display a listing of trashed petitions.
     */
    public function index()
    {
        // Only fetch petitions that have been soft deleted
        $trashedPetitions = Petition::onlyTrashed()->with(['user', 'seat'])->orderBy('deleted_at', 'desc')->paginate(15);
        
        return view('admin.trash', compact('trashedPetitions'));
    }

    /**
     * Restore the specified soft-deleted petition.
     */
    public function restore($id)
    {
        $petition = Petition::withTrashed()->findOrFail($id);
        
        // This will intuitively trigger model events (static::restoring) to restore relations
        $petition->restore();

        return redirect()->route('admin.trash.index')->with('success', 'Petition #'.$petition->petition_no.' has been restored successfully.');
    }

    /**
     * Permanently remove the specified petition and its files.
     */
    public function forceDelete($id)
    {
        $petition = Petition::withTrashed()->with('uploads')->findOrFail($id);

        // Delete associated public physical files permanently
        foreach ($petition->uploads as $upload) {
            if (Storage::disk('public')->exists($upload->file_path)) {
                Storage::disk('public')->delete($upload->file_path);
            }
        }
        
        // This will intuitively trigger model events (static::deleting) to forceDelete relations
        $petition->forceDelete();

        return redirect()->route('admin.trash.index')->with('success', 'Petition permanently deleted.');
    }
}
