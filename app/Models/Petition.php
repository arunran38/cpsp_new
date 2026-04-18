<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Petition extends Model
{
    use SoftDeletes, HasFactory;

    // Status Constants
    public const STATUS_RECEIVED = 'Received';
    public const STATUS_FORWARDED = 'Forwarded';
    public const STATUS_VR_RECEIVED = 'VR_Received';
    public const STATUS_SENT_TO_GOVT = 'Sent_to_Govt';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_CLOSED_BY_GOVT = 'Closed_by_Govt';

    protected $primaryKey = 'petition_id';

    protected $fillable = [
        'petition_no',
        'date_of_petition_received',
        'nature_of_petition',
        'mode_of_petition_received',
        'mode_of_petition_received_others',
        'description',
        'proposed_action',
        'status',
        'user_id',
        'seat_id',
    ];

    /**
     * Relationship: User (Creator)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id')->withTrashed();
    }

    /**
     * Relationship: Assigned Seat
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id', 'seat_id');
    }

    /**
     * Relationship: Addresses (Complainants/Accused)
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'petition_id');
    }

    /**
     * Relationship: Uploaded Documents
     */
    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class, 'petition_id');
    }

    /**
     * Relationship: Forwarding History
     */
    public function forwardings(): HasMany
    {
        return $this->hasMany(PetitionForwarding::class, 'petition_id', 'petition_id')->orderBy('created_at', 'desc');
    }

    /**
     * Relationship: Latest Forwarding
     */
    public function latestForwarding(): HasOne
    {
        return $this->hasOne(PetitionForwarding::class, 'petition_id', 'petition_id')->latestOfMany('petition_forwarding_id');
    }

    /**
     * Relationship: Final Decision
     */
    public function decision(): HasOne
    {
        return $this->hasOne(Decision::class, 'petition_id', 'petition_id');
    }

    // --- Scopes ---

    public function scopeFilterByTab(Builder $query, string $tab): Builder
    {
        return match ($tab) {
            'received' => $query->where('status', self::STATUS_RECEIVED),
            'forwarded' => $query->where('status', self::STATUS_FORWARDED),
            'vrs' => $query->where('status', self::STATUS_VR_RECEIVED),
            'decisions' => $query->whereIn('status', [self::STATUS_SENT_TO_GOVT, self::STATUS_CLOSED]),
            default => $query,
        };
    }

    public function scopeFilterByStatus(Builder $query, ?string $status, string $tab = 'all'): Builder
    {
        if (empty($status)) return $query;

        $finalDecisionStatuses = ['PE', 'SC', 'QV', 'ICell', 'Closed', 'Sent to Govt'];

        if ($status === self::STATUS_FORWARDED) {
            return $query->whereHas('forwardings');
        } 
        
        if ($status === self::STATUS_VR_RECEIVED) {
            return $query->whereHas('forwardings', fn($q) => $q->whereNotNull('vr_date'));
        } 
        
        if ($status === 'VR_Received_at_cpsp_date') {
            return $query->whereHas('forwardings', fn($q) => $q->whereNotNull('vr_received_at_cpsp_date'));
        } 
        
        if ($status === self::STATUS_RECEIVED && $tab === 'received') {
            return $query->where('status', self::STATUS_RECEIVED);
        }

        if ($status === 'All_Final_Decisions') {
            return $query->whereHas('decision');
        }

        if (in_array($status, $finalDecisionStatuses)) {
            return $query->whereHas('decision', fn($q) => $q->where('decision_remarks', $status));
        }

        return $query->where('status', $status);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('petition_no', 'like', "%{$search}%")
                ->orWhereHas('addresses', function ($q2) use ($search) {
                    $q2->whereIn('person_type', ['Complainant', 'Accused'])
                        ->where(function ($q3) use ($search) {
                            $q3->where('person_name', 'like', "%{$search}%")
                               ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
        });
    }

    public function scopeFilterDates(Builder $query, ?string $dateFrom, ?string $dateTo, ?string $status, string $tab = 'all'): Builder
    {
        if (!$dateFrom && !$dateTo) return $query;

        $finalDecisionStatuses = ['PE', 'SC', 'QV', 'Closed', 'Sent to Govt', 'ICell'];

        $column = 'date_of_petition_received';
        $relation = null;

        if ($status === self::STATUS_FORWARDED || $tab === 'forwarded') {
            $relation = 'forwardings';
            $column = 'forwarded_date';
        } elseif ($status === self::STATUS_VR_RECEIVED || $tab === 'vrs') {
            $relation = 'forwardings';
            $column = 'vr_date';
        } elseif ($status === 'VR_Received_at_cpsp_date') {
            $relation = 'forwardings';
            $column = 'vr_received_at_cpsp_date';
        } elseif ($status === 'All_Final_Decisions' || in_array($status, $finalDecisionStatuses) || $tab === 'decisions') {
            $relation = 'decision';
            $column = 'decision_date';
        }

        if ($relation) {
            return $query->whereHas($relation, function ($q) use ($dateFrom, $dateTo, $column) {
                if ($dateFrom) $q->whereDate($column, '>=', $dateFrom);
                if ($dateTo) $q->whereDate($column, '<=', $dateTo);
            });
        }

        if ($dateFrom) $query->whereDate($column, '>=', $dateFrom);
        if ($dateTo) $query->whereDate($column, '<=', $dateTo);

        return $query;
    }

    /**
     * Statistics helpers
     */
    public static function countForwardedPetitions(): int
    {
        return self::where('status', self::STATUS_FORWARDED)->count();
    }

    public static function countPendingPetitions(): int
    {
        return self::where('status', self::STATUS_RECEIVED)->count();
    }

    public static function countVrPetitions(): int
    {
        return self::where('status', self::STATUS_VR_RECEIVED)->count();
    }

    public static function countDecisionPetitions(): int
    {
        return self::where('status', self::STATUS_SENT_TO_GOVT)->count();
    }

    public static function countClosedPetitions(): int
    {
        return self::where('status', self::STATUS_CLOSED_BY_GOVT)->count();
    }

    protected static function booted(): void
    {
        static::deleting(function ($petition) {
            if ($petition->isForceDeleting()) {
                $petition->addresses()->forceDelete();
                $petition->uploads()->forceDelete();
                $petition->forwardings()->forceDelete();
                $petition->decision()->forceDelete();
            } else {
                $petition->addresses()->delete();
                $petition->uploads()->delete();
                $petition->forwardings()->delete();
                $petition->decision()->delete();
            }
        });

        static::restoring(function ($petition) {
            $petition->addresses()->withTrashed()->restore();
            $petition->uploads()->withTrashed()->restore();
            $petition->forwardings()->withTrashed()->restore();
            $petition->decision()->withTrashed()->restore();
        });
    }
}
