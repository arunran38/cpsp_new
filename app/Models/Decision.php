<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Decision extends Model
{
    use SoftDeletes, HasFactory;

    protected $primaryKey = 'decision_id';

    protected $fillable = [
        'petition_id',
        'decided_by_seat_id',
        'decision_remarks',
        'final_remarks',
        'decision_date',
        'processed_by_user_id',
    ];

    /**
     * Relationship: User who processed this decision
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
     * Relationship: Seat that made the decision
     */
    public function decidedBySeat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'decided_by_seat_id', 'seat_id');
    }

    /**
     * Helper to get user-friendly label for decision code
     */
    public static function getDecisionLabel(?string $code): string
    {
        if (empty($code)) return 'N/A';
        $labels = [
            'VC' => 'Vigilance Case (VC)',
            'VE' => 'Vigilance Enquiry (VE)',
            'PE' => 'Preliminary Enquiry (PE)',
            'SC' => 'Surprise Check (SC)',
            'CV' => 'Confidential Verification (CV)',
            'ICell' => 'Intelligence Cell (I Cell)',
            'Closed' => 'Closed',
            'Sent to Govt' => 'Sent to Govt'
        ];
        return $labels[$code] ?? $code;
    }
}
