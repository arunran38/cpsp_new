<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petition;
use App\Models\Decision;
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
        ]);

        DB::beginTransaction();
        try {
            $petition = Petition::findOrFail($request->petition_id);
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $seatUser = $user ? $user->seatUsers()->first() : null;
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

            DB::commit();
            return back()->with('success', 'Final Decision recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error recording decision: ' . $e->getMessage());
        }
    }
}
