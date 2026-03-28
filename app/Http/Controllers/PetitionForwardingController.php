<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petition;
use App\Models\PetitionForwarding;
use App\Models\Decision;
use App\Models\Upload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PetitionForwardingController extends Controller
{
    // Director Forwarding a Petition
    public function store(Request $request)
    {
        $request->validate([
            'action' => 'required|in:Forward_To_Unit,Sent_to_Govt,Close',
            'director_remarks' => 'required|string',
            'to_unit_id' => 'required_if:action,Forward_To_Unit|nullable|exists:units,unit_id',
            'final_order_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,webp|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $petition = Petition::findOrFail($request->petition_id);
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $seatUser = $user ? $user->seatUsers()->first() : null;
            $currentSeatId = $seatUser ? $seatUser->seat_id : 1;

            if ($request->action === 'Forward_To_Unit') {
                PetitionForwarding::create([
                    'petition_id' => $petition->petition_id,
                    'from_seat_id' => $currentSeatId,
                    'to_unit_id' => $request->to_unit_id,
                    'director_remarks' => $request->director_remarks,
                    'forwarded_date' => now(),
                ]);
                $petition->update(['status' => 'Forwarded']);
            } else {
                $status = $request->action === 'Sent_to_Govt' ? 'Sent_to_Govt' : 'Closed';
                $decisionRemarks = $request->action === 'Sent_to_Govt' ? 'Sent to Govt' : 'Closed'; 

                Decision::create([
                    'petition_id' => $petition->petition_id,
                    'decided_by_seat_id' => $currentSeatId,
                    'decision_remarks' => $decisionRemarks,
                    'final_remarks' => $request->director_remarks,
                    'decision_date' => now(),
                ]);
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
            }

            DB::commit();
            return back()->with('success', 'Petition processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing petition: ' . $e->getMessage());
        }
    }

    // Unit Submitting Verification Report
    public function updateVr(Request $request, $id)
    {
        $request->validate([
            'vr_ref_no' => 'required|string|max:255',
            'vr_date' => 'required|date',
            'vr_received_at_cpsp_date' => 'nullable|date',
            'vr_remarks' => 'nullable|string',
            'vr_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $forwarding = PetitionForwarding::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $forwarding->update([
                'vr_ref_no' => $request->vr_ref_no,
                'vr_date' => $request->vr_date,
                'vr_received_at_cpsp_date' => $request->vr_received_at_cpsp_date,
                'vr_remarks' => $request->vr_remarks,
            ]);

            $forwarding->petition->update(['status' => 'VR_Received']);

            if ($request->hasFile('vr_file')) {
                $file = $request->file('vr_file');
                $path = $file->store('petitions/vr', 'public');

                Upload::create([
                    'petition_id' => $forwarding->petition_id,
                    'category' => Upload::CATEGORY_VERIFICATION_REPORT,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'uploaded_by' => Auth::id(),
                ]);
            }

            DB::commit();
            return back()->with('success', 'Verification Report submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error submitting Verification Report: ' . $e->getMessage());
        }
    }

    // CPSP Receiving Verification Report
    public function receiveVr(Request $request, $id)
    {
        $request->validate([
            'vr_received_at_cpsp_date' => 'required|date',
        ]);

        $forwarding = PetitionForwarding::findOrFail($id);
        
        $forwarding->update([
            'vr_received_at_cpsp_date' => $request->vr_received_at_cpsp_date,
        ]);

        return back()->with('success', 'Verification Report received at CPSP.');
    }
}
