<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petition;
use App\Models\Decision;
use App\Models\Upload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DecisionController extends Controller
{
    // Final Decision by Director after VR is received
    public function store(Request $request)
    {
        $request->validate([
            'petition_id' => 'required|exists:petitions,petition_id',
            'decision_remarks' => 'required|in:PE,SC,QV,Closed,Sent to Govt,ICell',
            'final_remarks' => 'nullable|string',
            'final_order_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,webp|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $petition = Petition::findOrFail($request->petition_id);
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $seatUser = $user ? $user->currentSeatUser() : null;
            $currentSeatId = $seatUser ? $seatUser->seat_id : 1;

            Decision::create([
                'petition_id' => $petition->petition_id,
                'decided_by_seat_id' => $currentSeatId,
                'decision_remarks' => $request->decision_remarks,
                'final_remarks' => $request->final_remarks,
                'decision_date' => now(),
            ]);

            $status = $request->decision_remarks === 'Sent to Govt' ? 'Sent_to_Govt' : 'Closed';
            $petition->update(['status' => $status]);

            if ($request->hasFile('final_order_file')) {
                $file = $request->file('final_order_file');
                $path = $file->store('Uploads', 'public');

                Upload::create([
                    'petition_id' => $petition->petition_id,
                    'category' => Upload::CATEGORY_FINAL_ORDER,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'uploaded_by' => $user ? $user->user_id : null,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Final Decision recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error recording decision: ' . $e->getMessage());
        }
    }
}
