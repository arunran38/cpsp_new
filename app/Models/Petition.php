<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use App\Services\HashidService;

class Petition extends Model
{
    use SoftDeletes, HasFactory;

    // Status Constants
    public const STATUS_RECEIVED = 'Received';
    public const STATUS_FORWARDED = 'Forwarded';
    public const STATUS_VR_RECEIVED = 'VR_Received';
    public const STATUS_SENT_TO_GOVT = 'Sent_to_Govt';
    public const STATUS_INTERNAL_VIGILANCE = 'Internal_Vigilance';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_CLOSED_BY_GOVT = 'Closed_by_Govt';
    public const STATUS_DUPLICATE = 'Duplicate';

    protected $primaryKey = 'petition_id';

    protected $fillable = [
        'receipt_no',
        'file_no',
        'file_created_date',
        'date_of_petition_received',
        'nature_of_petition',
        'mode_of_petition_received',
        'mode_of_petition_received_others',
        'description',
        'proposed_action',
        'status',
        'previous_status',
        'user_id',
        'seat_id',
        'unit_id',
        'date_of_petition_received_at_unit',
        'linked_petition_id',
        'is_cpsp_processed',
        'is_returned_to_inward',
        'return_reason',
        'returned_by',
        'returned_at',
        'cpsp_opened_at',
        'cpsp_opened_by_user_id',
        'cpsp_opened_by_seat_id',
        'processed_at',
        'processed_by_user_id',
        'processed_by_seat_id',
        'updated_by_user_id',
        'updated_by_seat_id',
    ];

    protected $casts = [
        'is_cpsp_processed' => 'boolean',
        'is_returned_to_inward' => 'boolean',
        'returned_at' => 'datetime',
        'cpsp_opened_at' => 'datetime',
        'processed_at' => 'datetime',
        'date_of_petition_received_at_unit' => 'date',
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
     * Relationship: Original Petition (if this is a duplicate)
     */
    public function originalPetition(): BelongsTo
    {
        return $this->belongsTo(Petition::class, 'linked_petition_id', 'petition_id');
    }

    /**
     * Relationship: Duplicate Petitions (if this is the original)
     */
    public function duplicates(): HasMany
    {
        return $this->hasMany(Petition::class, 'linked_petition_id', 'petition_id');
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

    /**
     * Relationship: Unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    /**
     * Relationship: User who first opened this Inward petition at CPSP
     */
    public function cpspOpenedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cpsp_opened_by_user_id', 'user_id');
    }

    /**
     * Relationship: Seat from which this Inward petition was first opened
     */
    public function cpspOpenedBySeat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'cpsp_opened_by_seat_id', 'seat_id');
    }

    /**
     * Relationship: User who processed this petition
     */
    public function processedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id', 'user_id')->withTrashed();
    }

    /**
     * Relationship: Seat from which this petition was processed
     */
    public function processedBySeat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'processed_by_seat_id', 'seat_id');
    }

    /**
     * Relationship: User who last updated this petition
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'user_id')->withTrashed();
    }

    /**
     * Relationship: Seat from which this petition was last updated
     */
    public function updatedBySeat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'updated_by_seat_id', 'seat_id');
    }

    /**
     * Track when a CPSP user opens/views an Inward petition for the first time.
     */
    public function markAsOpenedByCpsp(User $user, ?SeatUser $seatUser): void
    {
        if ($this->cpsp_opened_at !== null) {
            return;
        }

        // Only mark if it's an Inward petition being opened by a CPSP seat user (or non-inward seat user)
        $isCpspSeat = $seatUser && $seatUser->seat && !str_contains(strtolower($seatUser->seat->seat_name), 'inward');
        $isInwardUser = $user->hasRole('inward') || $user->hasRole('Inward');

        if (($isCpspSeat || !$isInwardUser) && $this->isFromInward()) {
            $this->forceFill([
                'cpsp_opened_at' => now(),
                'cpsp_opened_by_user_id' => $user->user_id,
                'cpsp_opened_by_seat_id' => $seatUser?->seat_id,
            ])->save();
        }
    }

    /**
     * Check if the petition originated from Inward registration.
     */
    public function isFromInward(): bool
    {
        if (!$this->is_cpsp_processed) {
            return true;
        }

        if ($this->user) {
            if ($this->user->hasRole('inward') || $this->user->hasRole('Inward')) {
                return true;
            }
        }

        if ($this->user_id) {
            $hasInwardSeat = \App\Models\SeatUser::where('user_id', $this->user_id)
                ->whereHas('seat', fn($q) => $q->where('seat_name', 'like', '%Inward%'))
                ->exists();
            if ($hasInwardSeat) {
                return true;
            }
        }

        return $this->nature_of_petition === 'General Inward' || str_contains(strtolower($this->description ?? ''), 'inward');
    }

    /**
     * Relationship: User who returned this petition to Inward
     */
    public function returnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by', 'user_id');
    }

    /**
     * Get the value of the model's route key (uses Hashids).
     */
    public function getRouteKey(): mixed
    {
        return HashidService::encode($this->getKey());
    }

    /**
     * Retrieve the model for a bound value (decodes Hashids).
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $id = HashidService::decode($value);
        return $this->where($field ?? $this->getRouteKeyName(), $id ?? $value)->first();
    }

    /**
     * Accessor for $petition->hashid
     */
    public function getHashidAttribute(): string
    {
        return HashidService::encode($this->getKey());
    }

    /**
     * Static helper to find a petition by its hashid or numeric ID
     */
    public static function findByHashidOrFail(mixed $hashid): self
    {
        $id = HashidService::decode($hashid) ?? $hashid;
        return static::findOrFail($id);
    }

    /**
     * Accessor for dynamic workflow status details.
     * Accurately reflects current lifecycle state (Transferred, Returned, Processed, Forwarded, VR Received, Finalized).
     */
    public function getWorkflowStatusAttribute(): array
    {
        // 1. Returned to Inward
        if ($this->is_returned_to_inward) {
            return [
                'label' => 'Returned',
                'description' => 'Returned to Inward',
                'badge_class' => 'bg-rose-50 text-rose-700 border border-rose-200',
                'pill_class' => 'bg-rose-100 text-rose-800 border border-rose-200',
                'dot_class' => 'bg-rose-500',
                'color' => 'rose',
            ];
        }

        // 2. Transferred from Inward, awaiting CPSP processing
        if (!$this->is_cpsp_processed) {
            return [
                'label' => 'Transferred',
                'description' => 'Transferred to Seat (Pending CPSP Updation)',
                'badge_class' => 'bg-amber-50 text-amber-700 border border-amber-200',
                'pill_class' => 'bg-amber-100 text-amber-800 border border-amber-200',
                'dot_class' => 'bg-amber-500',
                'color' => 'amber',
            ];
        }

        // 3. Duplicate / Linked
        if ($this->linked_petition_id || $this->status === self::STATUS_DUPLICATE) {
            return [
                'label' => 'Duplicate',
                'description' => 'Linked Duplicate',
                'badge_class' => 'bg-slate-50 text-slate-700 border border-slate-200',
                'pill_class' => 'bg-slate-100 text-slate-700 border border-slate-200',
                'dot_class' => 'bg-slate-400',
                'color' => 'slate',
            ];
        }

        // 4. Processed by CPSP, in initial received stage
        if ($this->status === self::STATUS_RECEIVED) {
            return [
                'label' => 'Processed',
                'description' => 'Updated by CPSP',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                'pill_class' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                'dot_class' => 'bg-emerald-500',
                'color' => 'emerald',
            ];
        }

        // 5. Forwarded for Inquiry
        if ($this->status === self::STATUS_FORWARDED) {
            return [
                'label' => 'Forwarded',
                'description' => 'Forwarded for Inquiry',
                'badge_class' => 'bg-blue-50 text-blue-700 border border-blue-200',
                'pill_class' => 'bg-blue-100 text-blue-800 border border-blue-200',
                'dot_class' => 'bg-blue-500',
                'color' => 'blue',
            ];
        }

        // 6. Verification Report (VR) Received
        if ($this->status === self::STATUS_VR_RECEIVED) {
            return [
                'label' => 'VR Received',
                'description' => 'Verification Report Received',
                'badge_class' => 'bg-purple-50 text-purple-700 border border-purple-200',
                'pill_class' => 'bg-purple-100 text-purple-800 border border-purple-200',
                'dot_class' => 'bg-purple-500',
                'color' => 'purple',
            ];
        }

        // 7. Finalized / Decision Taken
        if (in_array($this->status, [self::STATUS_CLOSED, self::STATUS_SENT_TO_GOVT, self::STATUS_INTERNAL_VIGILANCE, self::STATUS_CLOSED_BY_GOVT]) || $this->decision) {
            $decisionLabel = $this->decision ? Decision::getDecisionLabel($this->decision->final_decision) : str_replace('_', ' ', $this->status);
            return [
                'label' => 'Finalized',
                'description' => 'Final : ' . $decisionLabel,
                'badge_class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                'pill_class' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                'dot_class' => 'bg-emerald-600',
                'color' => 'emerald',
            ];
        }

        // Fallback
        $formatted = str_replace('_', ' ', $this->status);
        return [
            'label' => $formatted,
            'description' => $formatted,
            'badge_class' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'pill_class' => 'bg-amber-100 text-amber-800 border border-amber-200',
            'dot_class' => 'bg-amber-500',
            'color' => 'amber',
        ];
    }

    // --- Scopes ---

    public function scopeFilterByTab(Builder $query, string $tab): Builder
    {
        return match ($tab) {
            'inward' => $query->where('is_cpsp_processed', false)->where('is_returned_to_inward', false),
            'received' => $query->where('status', self::STATUS_RECEIVED)->where('is_cpsp_processed', true),
            'forwarded' => $query->where('status', self::STATUS_FORWARDED),
            'vrs' => $query->where('status', self::STATUS_VR_RECEIVED),
            'decisions' => $query->whereIn('status', [self::STATUS_SENT_TO_GOVT, self::STATUS_CLOSED]),
            'all' => $query->where('is_cpsp_processed', true),
            default => $query->where('is_cpsp_processed', true),
        };
    }

    public function scopeFilterByStatus(Builder $query, ?string $status, string $tab = 'all'): Builder
    {
        if (empty($status)) return $query;

        $finalDecisionStatuses = ['VC', 'VE', 'PE', 'SC', 'CV', 'ICell', 'Closed', 'Sent to Govt'];

        if ($status === self::STATUS_FORWARDED) {
            return $query->where('status', self::STATUS_FORWARDED);
        } 
        
        if ($status === self::STATUS_VR_RECEIVED) {
            return $query->where('status', self::STATUS_VR_RECEIVED);
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

        if ($status === 'Pending') {
            return $query->doesntHave('decision');
        }

        if (in_array($status, $finalDecisionStatuses)) {
            return $query->whereHas('decision', fn($q) => $q->where('final_decision', $status));
        }

        return $query->where('status', $status);
    }

    public function scopeSearch(Builder $query, ?string $search, ?string $searchType = null): Builder
    {
        if (empty($search)) return $query;

        return $query->where(function ($q) use ($search, $searchType) {
            // Search receipt_no or file_no if searchType is empty/all or explicitly receipt_no/petition_no
            $matchPetitionNo = empty($searchType) || in_array($searchType, ['petition_no', 'receipt_no', 'file_no']);
            if ($matchPetitionNo) {
                $q->where(function ($qNo) use ($search) {
                    $qNo->where('receipt_no', 'like', "%{$search}%")
                        ->orWhere('file_no', 'like', "%{$search}%");
                });
            }

            $orWhereHas = $matchPetitionNo ? 'orWhereHas' : 'whereHas';

            $q->$orWhereHas('addresses', function ($q2) use ($search, $searchType) {
                // Apply targeted filters based on searchType
                if ($searchType === 'complainant') {
                    $q2->where('person_type', 'Complainant');
                } elseif ($searchType === 'suspect') {
                    $q2->where('person_type', 'Accused')
                       ->where('entity_type', 'Person');
                } elseif ($searchType === 'firm') {
                    $q2->where('person_type', 'Accused')
                       ->where('entity_type', 'Firm');
                } else {
                    // Default to Complainant or Accused
                    $q2->whereIn('person_type', ['Complainant', 'Accused']);
                }

                $q2->where(function ($q3) use ($search) {
                    $q3->where('person_name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('pen_number', 'like', "%{$search}%");
                });
            });
        });
    }

    public function scopeFilterDates(Builder $query, ?string $dateFrom, ?string $dateTo, ?string $status, string $tab = 'all'): Builder
    {
        if (!$dateFrom && !$dateTo) return $query;

        $parsedFrom = null;
        if ($dateFrom) {
            try {
                $parsedFrom = \Carbon\Carbon::parse($dateFrom)->format('Y-m-d');
            } catch (\Exception $e) {
                $parsedFrom = $dateFrom;
            }
        }

        $parsedTo = null;
        if ($dateTo) {
            try {
                $parsedTo = \Carbon\Carbon::parse($dateTo)->format('Y-m-d');
            } catch (\Exception $e) {
                $parsedTo = $dateTo;
            }
        }

        $finalDecisionStatuses = ['VC', 'VE', 'PE', 'SC', 'CV', 'Closed', 'Sent to Govt', 'ICell'];

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
            return $query->whereHas($relation, function ($q) use ($parsedFrom, $parsedTo, $column) {
                if ($parsedFrom) $q->whereDate($column, '>=', $parsedFrom);
                if ($parsedTo) $q->whereDate($column, '<=', $parsedTo);
            });
        }

        if ($parsedFrom) $query->whereDate($column, '>=', $parsedFrom);
        if ($parsedTo) $query->whereDate($column, '<=', $parsedTo);

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

    public function compliance(): HasOne
    {
        return $this->hasOne(Compliance::class, 'petition_id', 'petition_id');
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

