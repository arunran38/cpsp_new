<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\PetitionDraft;
use App\Services\HashidService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetitionDraftController extends Controller
{
    use AuthorizesRequests;

    /**
     * Save or update draft form data for a user.
     */
    public function save(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'type' => 'required|in:create,update',
            'petition_id' => 'nullable',
            'step' => 'required|integer|min:1|max:4',
            'form_data' => 'required|array',
        ]);

        $type = $validated['type'];
        $petitionId = null;

        if ($type === 'update') {
            $rawId = $validated['petition_id'] ?? null;
            if (!$rawId) {
                return response()->json(['success' => false, 'message' => 'Petition ID required for update draft.'], 422);
            }
            $petitionId = HashidService::decode($rawId) ?? $rawId;
            $petition = Petition::findOrFail($petitionId);
            $this->authorize('update', $petition);
        } else {
            $this->authorize('create', Petition::class);
        }

        $draft = PetitionDraft::updateOrCreate(
            [
                'user_id' => $user->user_id,
                'type' => $type,
                'petition_id' => $petitionId,
            ],
            [
                'step' => $validated['step'],
                'form_data' => $validated['form_data'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Draft saved successfully.',
            'saved_at' => $draft->updated_at->format('h:i A'),
            'updated_at_iso' => $draft->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Clear draft form data for a user.
     */
    public function clear(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'type' => 'required|in:create,update',
            'petition_id' => 'nullable',
        ]);

        $type = $validated['type'];
        $petitionId = null;

        if ($type === 'update' && !empty($validated['petition_id'])) {
            $rawId = $validated['petition_id'];
            $petitionId = HashidService::decode($rawId) ?? $rawId;
        }

        $query = PetitionDraft::where('user_id', $user->user_id)->where('type', $type);
        if ($petitionId) {
            $query->where('petition_id', $petitionId);
        } else {
            $query->whereNull('petition_id');
        }

        $deleted = $query->delete();

        return response()->json([
            'success' => true,
            'message' => 'Draft cleared successfully.',
            'deleted' => $deleted,
        ]);
    }
}

