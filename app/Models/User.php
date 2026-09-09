<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number',
        'role',
        'designation',
        'other_designation',
        'pen',
        'photo',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relationship: Profile Photo (Upload)
     */
    public function profilePhoto(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'photo', 'upload_id');
    }

    /**
     * Relationship: Seat Assignments
     */
    public function seatUsers(): HasMany
    {
        return $this->hasMany(SeatUser::class, 'user_id', 'user_id');
    }

    /**
     * Get the currently active SeatUser based on session state, 
     * or fallback to the primary active seat.
     */
    public function currentSeatUser(): ?SeatUser
    {
        $currentSeatId = session('current_seat_id');

        if ($currentSeatId) {
            $seatUser = $this->seatUsers()->where('seat_id', $currentSeatId)->where('is_active', true)->first();
            if ($seatUser) {
                return $seatUser;
            }
        }

        $defaultSeatUser = $this->seatUsers()
            ->where('is_active', true)
            ->orderBy('is_additional', 'asc')
            ->first();
        if ($defaultSeatUser) {
            session(['current_seat_id' => $defaultSeatUser->seat_id]);
            return $defaultSeatUser;
        }

        return null;
    }

    /**
     * Attributes which should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Statistics helper for user role
     */
    public static function countUser(): int
    {
        return self::where('role', 'user')->count();
    }   
}
