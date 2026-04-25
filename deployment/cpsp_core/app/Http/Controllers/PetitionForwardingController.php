<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\PetitionForwarding;
use App\Models\Decision;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PetitionForwardingController extends Controller
{
    /**
     * Director Forwarding a Petition or making a final decision.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'petition_id' => 'required|exists:petitions,petition_id',
            'action' => 'required|in:Forward_To_Unit,Sent_to_Govt,Close',
            'director_remarks' => 'required|string',
            'to_unit_id' => 'required_if:action,Forward_To_Unit|nullable|exists:units,unit_id',
            'final_order_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,webp|max:10240',
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $petition = Petition::findOrFail($request->petition_id);
                $user = Auth::user();
                $seatUser = $user ? $user->currentSeatUser() : null;
                $currentSeatId = $seatUser ? $seatUser->seat_id : 1;

                if ($request->action === 'Forward_To_Unit') {
                    PetitionForwarding::create([
                        'petition_id' => $petition->petition_id,
                        'from_seat_id' => $currentSeatId,
                        'to_unit_id' => $request->to_unit_id,
                        'director_remarks' => $request->director_remarks,
                        'forwarded_date' => now(),
                        'processed_by_user_id' => Auth::id(),
                    ]);
                    $petition->update(['status' => Petition::STATUS_FORWARDED]);
                } else {
                    $status = $request->action === 'Sent_to_Govt' ? Petition::STATUS_SENT_TO_GOVT : Petition::STATUS_CLOSED;
                    $decisionRemarks = $request->action === 'Sent_to_Govt' ? 'Sent to Govt' : 'Closed'; 

                    Decision::create([
                        'petition_id' => $petition->petition_id,
                        'decided_by_seat_id' => $currentSeatId,
                        'decision_remarks' => $decisionRemarks,
                        'final_remarks' => $request->director_remarks,
                        'decision_date' => now(),
                        'processed_by_user_id' => Auth::id(),
                    ]);
                    $petition->update(['status' => $status]);

                    if ($request->hasFile('final_order_file')) {
                        $this->handleFinalOrderUpload($petition, $request->file('final_order_file'));
                    }
                }

                return back()->with('success', 'Petition processed successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Error processing petition: ' . $e->getMessage());
            }
        });
    }

    /**
     * Unit Submitting Verification Report.
     */
    public function updateVr(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'vr_ref_no' => 'required|string|max:255',
            'vr_date' => 'required|date|before_or_equal:today',
            'vr_received_at_cpsp_date' => 'nullable|date|before_or_equal:today',
            'vr_remarks' => 'nullable|string',
            'vr_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ], [
            'vr_date.before_or_equal' => 'Date cannot be in the future.',
            'vr_received_at_cpsp_date.before_or_equal' => 'Date cannot be in the future.',
        ]);

        return DB::transaction(function () use ($request, $id) {
            try {
                $forwarding = PetitionForwarding::findOrFail($id);
                
                $forwarding->update([
                    'vr_ref_no' => $request->vr_ref_no,
                    'vr_date' => $request->vr_date,
                    'vr_received_at_cpsp_date' => $request->vr_received_at_cpsp_date,
                    'vr_remarks' => $request->vr_remarks,
                    'processed_by_user_id' => Auth::id(),
                ]);

                $forwarding->petition->update(['status' => Petition::STATUS_VR_RECEIVED]);

                if ($request->hasFile('vr_file')) {
                    $this->handleVrUpload($forwarding->petition_id, $request->file('vr_file'));
                }

                return back()->with('success', 'Verification Report submitted successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Error submitting Verification Report: ' . $e->getMessage());
            }
        });
    }

    /**
     * CPSP Receiving Verification Report.
     */
    public function receiveVr(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'vr_received_at_cpsp_date' => 'required|date|before_or_equal:today',
        ], [
            'vr_received_at_cpsp_date.before_or_equal' => 'Date cannot be in the future.',
        ]);

        $forwarding = PetitionForwarding::findOrFail($id);
        $forwarding->update(['vr_received_at_cpsp_date' => $request->vr_received_at_cpsp_date]);

        return back()->with('success', 'Verification Report received at CPSP.');
    }

    /**
     * Pull back forwarding to unit.
     */
    public function pullbackForwarding(int $id): RedirectResponse
    {
        $forwarding = PetitionForwarding::findOrFail($id);

        if ($forwarding->vr_ref_no) {
            return back()->with('error', 'Cannot pull back. VR Report already submitted.');
        }

        return DB::transaction(function () use ($forwarding) {
            try {
                $petition = $forwarding->petition;
                $forwarding->delete();
                $petition->update(['status' => Petition::STATUS_RECEIVED]);

                return back()->with('success', 'Forwarding pulled back successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Error pulling back forwarding: ' . $e->getMessage());
            }
        });
    }

    /**
     * Pull back VR Report.
     */
    public function pullbackVr(int $id): RedirectResponse
    {
        $forwarding = PetitionForwarding::findOrFail($id);
        $petition = $forwarding->petition;

        if (Decision::where('petition_id', $petition->petition_id)->exists()) {
            return back()->with('error', 'Cannot pull back VR. Final decision already made.');
        }

        return DB::transaction(function () use ($forwarding, $petition) {
            try {
                $forwarding->update([
                    'vr_ref_no' => null,
                    'vr_date' => null,
                    'vr_received_at_cpsp_date' => null,
                    'vr_remarks' => null,
                ]);

                $petition->update(['status' => Petition::STATUS_FORWARDED]);

                Upload::where('petition_id', $petition->petition_id)
                    ->where('category', Upload::CATEGORY_VERIFICATION_REPORT)
                    ->delete();

                return back()->with('success', 'VR Report pulled back successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Error pulling back VR: ' . $e->getMessage());
            }
        });
    }

    private function handleFinalOrderUpload(Petition $petition, $file): void
    {
        $path = $file->store('Uploads', 'public');
        Upload::create([
            'petition_id' => $petition->petition_id,
            'category' => Upload::CATEGORY_FINAL_ORDER,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => Auth::id(),
        ]);
    }

    private function handleVrUpload(int $petitionId, $file): void
    {
        $path = $file->store('petitions/vr', 'public');
        Upload::create([
            'petition_id' => $petitionId,
            'category' => Upload::CATEGORY_VERIFICATION_REPORT,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => Auth::id(),
        ]);
    }
}
