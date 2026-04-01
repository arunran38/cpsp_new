<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatUser extends Model
{
    use HasFactory;

    protected $primaryKey = 'seat_user_id';

    protected $fillable = [
        'user_id',
        'seat_id',
        'is_additional',
        'assigned_at',
        'revoked_at',
        'is_active',
    ];

    protected $casts = [
        'is_additional' => 'boolean',
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Relationship: User (Officer) assigned to the seat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id')->withTrashed();
    }

    /**
     * Relationship: The Seat itself
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id', 'seat_id');
    }
}
