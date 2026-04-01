<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Seat extends Model
{
    use HasFactory;

    protected $primaryKey = 'seat_id';

    protected $fillable = [
        'seat_name',
        'is_active',
    ];

    /**
     * Relationship: Associated Units (Many-to-Many)
     */
    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'seat_unit', 'seat_id', 'unit_id');
    }

    /**
     * Relationship: User Assignments
     */
    public function seatUsers(): HasMany
    {
        return $this->hasMany(SeatUser::class, 'seat_id', 'seat_id');
    }

    /**
     * Relationship: Current Active Assignment
     */
    public function activeAssignment(): HasOne
    {
        return $this->hasOne(SeatUser::class, 'seat_id', 'seat_id')
            ->where('is_active', true);
    }

    /**
     * Alias for activeAssignment occupant
     */
    public function occupant(): HasOne
    {
        return $this->activeAssignment()->withDefault();
    }

    /**
     * Relationship: Petitions assigned to this seat
     */
    public function petitionsReceived(): HasMany
    {
        return $this->hasMany(Petition::class, 'seat_id', 'seat_id');
    }

    /**
     * Relationship: Forwardings sent from this seat
     */
    public function forwardingsOut(): HasMany
    {
        return $this->hasMany(PetitionForwarding::class, 'from_seat_id', 'seat_id');
    }

    /**
     * Relationship: Decisions made by this seat
     */
    public function decisionsMade(): HasMany
    {
        return $this->hasMany(Decision::class, 'decided_by_seat_id', 'seat_id');
    }

    // --- Scopes ---

    /**
     * Scope for Eager Loading Statistics with Counts
     */
    public function scopeWithStatistics(Builder $query, $from = null, $to = null): Builder
    {
        return $query->withCount([
            'petitionsReceived as petitions_all_count' => fn($q) => $this->filterDates($q, $from, $to, 'date_of_petition_received'),
            
            'petitionsReceived as petitions_received_count' => fn($q) => 
                $this->filterDates($q, $from, $to, 'date_of_petition_received')->where('status', Petition::STATUS_RECEIVED),
            
            'petitionsReceived as forwardings_out_count' => function($q) use ($from, $to) {
                $q->where('status', Petition::STATUS_FORWARDED);
                if ($from || $to) {
                    $q->whereHas('forwardings', fn($f) => $this->filterDates($f, $from, $to, 'forwarded_date'));
                }
            },
            
            'petitionsReceived as vr_received_count' => function($q) use ($from, $to) {
                $q->where('status', Petition::STATUS_VR_RECEIVED);
                if ($from || $to) {
                    $q->whereHas('forwardings', fn($f) => $this->filterDates($f, $from, $to, 'vr_date'));
                }
            },
            
            'petitionsReceived as decisions_made_count' => function($q) use ($from, $to) {
                $q->whereIn('status', [Petition::STATUS_CLOSED, Petition::STATUS_SENT_TO_GOVT]);
                if ($from || $to) {
                    $q->whereHas('decision', fn($d) => $this->filterDates($d, $from, $to, 'decision_date'));
                }
            },

            // Final Decision Type Breakdown
            'petitionsReceived as decisions_pe_count' => fn($q) => $this->filterDecisionType($q, 'PE', $from, $to),
            'petitionsReceived as decisions_sc_count' => fn($q) => $this->filterDecisionType($q, 'SC', $from, $to),
            'petitionsReceived as decisions_qv_count' => fn($q) => $this->filterDecisionType($q, 'QV', $from, $to),
            'petitionsReceived as decisions_icell_count' => fn($q) => $this->filterDecisionType($q, 'ICell', $from, $to),
            'petitionsReceived as decisions_closed_count' => fn($q) => $this->filterDecisionType($q, 'Closed', $from, $to),
            'petitionsReceived as decisions_sent_count' => fn($q) => $this->filterDecisionType($q, 'Sent to Govt', $from, $to),
        ]);
    }

    private function filterDates($query, $from, $to, $column)
    {
        if ($from) $query->where($column, '>=', $from);
        if ($to) $query->where($column, '<=', $to);
        return $query;
    }

    private function filterDecisionType($query, $type, $from, $to)
    {
        $query->whereIn('status', [Petition::STATUS_CLOSED, Petition::STATUS_SENT_TO_GOVT]);
        return $query->whereHas('decision', function($q) use ($type, $from, $to) {
            $q->where('decision_remarks', $type);
            $this->filterDates($q, $from, $to, 'decision_date');
        });
    }

    // --- Helpers ---

    public static function countVacant(): int
    {
        return self::where('is_active', true)
            ->whereDoesntHave('seatUsers', fn($q) => $q->where('is_active', true))
            ->count();
    }

    public static function getVacant(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)
            ->whereDoesntHave('seatUsers', fn($q) => $q->where('is_active', true))
            ->get();
    }
}
