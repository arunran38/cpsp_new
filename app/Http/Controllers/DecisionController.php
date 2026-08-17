<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\Decision;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DecisionController extends Controller
{
    /**
     * Final Decision by Director after VR is received.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'petition_id' => 'required|exists:petitions,petition_id',
            'decision_remarks' => 'required|in:VC,VE,PE,SC,CV,Closed,Sent to Govt,ICell',
            'final_remarks' => 'nullable|string',
            'final_order_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'decision_date' => 'required|date|before_or_equal:today',
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $petition = Petition::findOrFail($request->petition_id);
                $user = Auth::user();
                $seatUser = $user ? $user->currentSeatUser() : null;
                $currentSeatId = $seatUser ? $seatUser->seat_id : 1;

                Decision::create([
                    'petition_id' => $petition->petition_id,
                    'decided_by_seat_id' => $currentSeatId,
                    'decision_remarks' => $request->decision_remarks,
                    'final_remarks' => $request->final_remarks,
                    'decision_date' => $request->decision_date,
                    'processed_by_user_id' => Auth::id(),
                ]);

                $status = $request->decision_remarks === 'Sent to Govt' ? Petition::STATUS_SENT_TO_GOVT : Petition::STATUS_CLOSED;
                $petition->update(['status' => $status]);

                if ($request->hasFile('final_order_file')) {
                    $this->handleFinalOrderUpload($petition->petition_id, $request->file('final_order_file'));
                }

                return back()->with('success', 'Final Decision recorded.');
            } catch (\Exception $e) {
                return back()->with('error', 'Error recording decision: ' . $e->getMessage());
            }
        });
    }

    /**
     * Pull back final decision.
     */
    public function pullbackDecision(int $id): RedirectResponse
    {
        $decision = Decision::findOrFail($id);
        $petition = $decision->petition;

        return DB::transaction(function () use ($decision, $petition) {
            try {
                $decision->delete();

                // Determine the previous status based on forwarding history
                $latestForwarding = $petition->latestForwarding;
                $newStatus = Petition::STATUS_RECEIVED;

                if ($latestForwarding) {
                    $newStatus = $latestForwarding->vr_ref_no ? Petition::STATUS_VR_RECEIVED : Petition::STATUS_FORWARDED;
                }

                $petition->update(['status' => $newStatus]);

                // Delete the Final Order file upload
                Upload::where('petition_id', $petition->petition_id)
                    ->where('category', Upload::CATEGORY_FINAL_ORDER)
                    ->delete();

                return back()->with('success', 'Final Decision pulled back successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Error pulling back decision: ' . $e->getMessage());
            }
        });
    }

    private function handleFinalOrderUpload(int $petitionId, $file): void
    {
        $path = $file->store('Uploads', 'public');
        Upload::create([
            'petition_id' => $petitionId,
            'category' => Upload::CATEGORY_FINAL_ORDER,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => Auth::id(),
        ]);
    }
}
