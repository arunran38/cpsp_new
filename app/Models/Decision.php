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

    public const GOVT_REC_17A = '17 A Sanction';
    public const GOVT_REC_WITH_RECOMMENDATIONS = 'Report with Recommendations';
    public const GOVT_REC_NO_RECOMMENDATIONS = 'No Recommendations';

    public const GOVT_RECOMMENDATION_OPTIONS = [
        self::GOVT_REC_17A => '17 A Sanction',
        self::GOVT_REC_WITH_RECOMMENDATIONS => 'Report with Recommendations',
        self::GOVT_REC_NO_RECOMMENDATIONS => 'No Recommendations',
    ];

    protected $fillable = [
        'petition_id',
        'decided_by_seat_id',
        'final_decision',
        'govt_recommendation_type',
        'directorate_order_number',
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
            'CS' => 'Confidential Verification (CV)',
            'IV' => 'Internal Vigilance (IV)',
            'Internal Vigilance' => 'Internal Vigilance (IV)',
            'ICell' => 'Intelligence Cell (I Cell)',
            'Closed' => 'Closed',
            'Sent to Govt' => 'Sent to Govt',
            'Sent to SPC' => 'Sent to SPC',
        ];
        return $labels[$code] ?? $code;
    }
}

