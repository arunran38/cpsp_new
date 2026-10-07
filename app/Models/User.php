<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number',
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
        try {
            return self::role('user')->count();
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            return 0;
        }
    }   

    /**
     * Check if the user can access a permission based on their active seat.
     */
    public function canAccess(string $permission): bool
    {
        $currentSeatUser = $this->currentSeatUser();
        $currentSeat = $currentSeatUser ? $currentSeatUser->seat : null;

        // If sitting in a seat, the seat's permissions govern access (except for admin dashboard)
        if ($currentSeat && !session('is_impersonating_seat')) {
            try {
                if ($currentSeat->hasPermissionTo($permission)) {
                    return true;
                }
            } catch (\Exception $e) { }

            // Still allow global admin dashboard access if the user has it personally
            if ($permission === 'access admin dashboard') {
                try {
                    return $this->hasPermissionTo($permission);
                } catch (\Exception $e) { }
            }

            return false;
        }

        // If not in a seat (or impersonating), fallback to personal permissions
        try {
            return $this->hasPermissionTo($permission);
        } catch (\Exception $e) {
            return false;
        }
    }
}
