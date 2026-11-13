<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Create and dispatch a generic notification
     */
    public static function create(array $attributes): AppNotification
    {
        return AppNotification::create([
            'user_id' => $attributes['user_id'] ?? null,
            'seat_id' => $attributes['seat_id'] ?? null,
            'type' => $attributes['type'] ?? 'system',
            'title' => $attributes['title'] ?? 'Notification',
            'message' => $attributes['message'] ?? '',
            'link' => $attributes['link'] ?? null,
            'icon' => $attributes['icon'] ?? 'bell',
            'color' => $attributes['color'] ?? 'indigo',
            'data' => $attributes['data'] ?? null,
        ]);
    }

    /**
     * Send notification to one or multiple seats
     */
    public static function sendToSeat(int|array $seatIds, string $type, string $title, string $message, ?string $link = null, string $icon = 'bell', string $color = 'indigo', array $data = []): array
    {
        $created = [];
        $ids = is_array($seatIds) ? $seatIds : [$seatIds];

        foreach ($ids as $id) {
            $created[] = self::create([
                'seat_id' => $id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'icon' => $icon,
                'color' => $color,
                'data' => $data,
            ]);
        }

        return $created;
    }

    /**
     * Send notification to one or multiple users
     */
    public static function sendToUser(int|array $userIds, string $type, string $title, string $message, ?string $link = null, string $icon = 'bell', string $color = 'indigo', array $data = []): array
    {
        $created = [];
        $ids = is_array($userIds) ? $userIds : [$userIds];

        foreach ($ids as $id) {
            $created[] = self::create([
                'user_id' => $id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'icon' => $icon,
                'color' => $color,
                'data' => $data,
            ]);
        }

        return $created;
    }

    /**
     * Notify seat occupants when a petition is transferred or reassigned to a seat
     */
    public static function notifyPetitionTransferred(Petition $petition, int $toSeatId, ?User $byUser = null): array
    {
        $byName = $byUser ? $byUser->name : (Auth::user()?->name ?? 'Inward Desk');
        $seat = Seat::find($toSeatId);
        $seatName = $seat ? $seat->seat_name : "Seat #{$toSeatId}";

        $link = route('petitions.show', $petition->hashid);

        return self::sendToSeat(
            $toSeatId,
            'petition_transferred',
            "New Petition Assigned ({$petition->receipt_no})",
            "Receipt No. {$petition->receipt_no} has been transferred to {$seatName} by {$byName}.",
            $link,
            'file-input',
            'indigo',
            [
                'petition_id' => $petition->petition_id,
                'receipt_no' => $petition->receipt_no,
                'by_user_id' => $byUser?->user_id ?? Auth::id(),
            ]
        );
    }

    /**
     * Notify Inward officers when a petition is returned from CPSP / T-Section
     */
    public static function notifyPetitionReturned(Petition $petition, string $reason, ?User $byUser = null): array
    {
        $byName = $byUser ? $byUser->name : (Auth::user()?->name ?? 'Desk Officer');
        $inwardSeatIds = Seat::getInwardSeatIds();

        // Also notify original creator user if available
        if ($petition->user_id && $petition->user_id !== Auth::id()) {
            self::sendToUser(
                $petition->user_id,
                'petition_returned',
                "Petition Returned to Inward ({$petition->receipt_no})",
                "Receipt No. {$petition->receipt_no} has been returned to Inward. Reason: {$reason}",
                route('inward.enter_petition', ['tab' => 'returned']),
                'undo-2',
                'rose',
                ['petition_id' => $petition->petition_id, 'receipt_no' => $petition->receipt_no, 'reason' => $reason]
            );
        }

        $link = route('inward.enter_petition', ['tab' => 'returned']);

        return self::sendToSeat(
            $inwardSeatIds,
            'petition_returned',
            "Petition Returned to Inward ({$petition->receipt_no})",
            "Receipt No. {$petition->receipt_no} was returned to Inward by {$byName}. Reason: {$reason}",
            $link,
            'undo-2',
            'rose',
            [
                'petition_id' => $petition->petition_id,
                'receipt_no' => $petition->receipt_no,
                'reason' => $reason,
                'by_user_id' => $byUser?->user_id ?? Auth::id(),
            ]
        );
    }

    /**
     * Notify assigned desk when a Verification Report (VR) is received
     */
    public static function notifyVrReceived(Petition $petition, ?User $byUser = null): array
    {
        if (!$petition->seat_id) {
            return [];
        }

        $link = route('petitions.show', $petition->hashid);

        return self::sendToSeat(
            $petition->seat_id,
            'vr_received',
            "Verification Report Received ({$petition->receipt_no})",
            "Verification Report (VR) for Receipt No. {$petition->receipt_no} has been recorded.",
            $link,
            'file-check',
            'emerald',
            [
                'petition_id' => $petition->petition_id,
                'receipt_no' => $petition->receipt_no,
            ]
        );
    }

    /**
     * Notify desk when final decision is taken
     */
    public static function notifyDecision(Petition $petition, string $decision, ?User $byUser = null): array
    {
        if (!$petition->seat_id) {
            return [];
        }

        $link = route('petitions.show', $petition->hashid);

        return self::sendToSeat(
            $petition->seat_id,
            'final_decision',
            "Final Decision Recorded ({$petition->receipt_no})",
            "Final decision '{$decision}' has been recorded for Receipt No. {$petition->receipt_no}.",
            $link,
            'scale',
            'teal',
            [
                'petition_id' => $petition->petition_id,
                'receipt_no' => $petition->receipt_no,
                'decision' => $decision,
            ]
        );
    }
}

