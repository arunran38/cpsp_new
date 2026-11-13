<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    use HasFactory;

    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'seat_id',
        'type',
        'title',
        'message',
        'link',
        'icon',
        'color',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Targeted User (if specific to a user)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relationship: Targeted Seat (if directed to a seat like CPSP 1)
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id', 'seat_id');
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for notifications accessible to the current user and their active seat
     */
    public function scopeForRecipient($query, User $user, ?int $seatId = null)
    {
        $currentSeatId = $seatId ?? $user->currentSeatUser()?->seat_id;

        return $query->where(function ($q) use ($user, $currentSeatId) {
            $q->where('user_id', $user->user_id);

            if ($currentSeatId) {
                $q->orWhere('seat_id', $currentSeatId);
            }
        });
    }

    /**
     * Mark this notification as read
     */
    public function markAsRead(): bool
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => now()])->save();
            return true;
        }
        return false;
    }
}

