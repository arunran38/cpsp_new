<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetitionForwarding extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'petition_forwarding_id';

    protected $fillable = [
        'petition_id',
        'from_seat_id',
        'to_unit_id',
        'director_remarks',
        'forwarded_date',
        'vr_ref_no',
        'vr_date',
        'vr_remarks',
        'vr_received_at_cpsp_date',
        'processed_by_user_id',
    ];

    /**
     * Relationship: User who processed this forwarding
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id', 'user_id');
    }

    /**
     * Relationship: Associated Petition
     */
    public function petition(): BelongsTo
    {
        return $this->belongsTo(Petition::class, 'petition_id', 'petition_id');
    }

    /**
     * Relationship: Source Seat
     */
    public function fromSeat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'from_seat_id', 'seat_id');
    }

    /**
     * Relationship: Destination Unit
     */
    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'to_unit_id', 'unit_id');
    }

    /**
     * Statistics: Count of Verification Reports
     */
    public static function countOfVr(): int
    {
       return self::whereNotNull('vr_ref_no')->count();
    }
}