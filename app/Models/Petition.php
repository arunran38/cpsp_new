<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Services\HashidService;

/**
 * App\Models\Petition
 *
 * @property int $petition_id
 * @property string $receipt_no
 * @property int|null $linked_petition_id
 * @property \App\Models\Petition|null $originalPetition
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Address> $addresses
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petition excludeTSectionIfNotAuthorized(?\App\Models\User $user = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petition accessibleInwardPetitions(\App\Models\User $user, ?string $scopeFilter = null)
 */
class Petition extends Model
{
    use SoftDeletes, HasFactory;

    // Status Constants
    public const STATUS_RECEIVED = 'Received';
    public const STATUS_FORWARDED = 'Forwarded';
    public const STATUS_VR_RECEIVED = 'VR_Received';
    public const STATUS_SENT_TO_GOVT = 'Sent_to_Govt';
    public const STATUS_SENT_TO_SPC = 'Sent_to_SPC';
    public const STATUS_INTERNAL_VIGILANCE = 'Internal_Vigilance';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_CLOSED_BY_GOVT = 'Closed_by_Govt';
    public const STATUS_DUPLICATE = 'Duplicate';

    // Mode of Petition Constants
    public const MODE_EMAIL = 'Email';
    public const MODE_WHATSAPP = 'Whatsapp';
    public const MODE_TOLLFREE = 'Tollfree';
    public const MODE_DIRECT = 'Direct';
    public const MODE_UNIT = 'Unit';
    public const MODE_TAPAL = 'Tapal';
    public const MODE_IAPS = 'iaps';
    public const MODE_GOVERNMENT = 'Government';
    public const MODE_OTHERS = 'others';

    public const MODES = [
        self::MODE_EMAIL => 'Email',
        self::MODE_WHATSAPP => 'Whatsapp',
        self::MODE_TOLLFREE => 'Tollfree',
        self::MODE_DIRECT => 'Direct',
        self::MODE_UNIT => 'Unit',
        self::MODE_TAPAL => 'Tapal',
        self::MODE_IAPS => 'iAPS',
        self::MODE_GOVERNMENT => 'Government',
        self::MODE_OTHERS => 'Others',
    ];

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
        'created_by_seat_id',
        'unit_id',
        'concerned_district_id',
        'petition_number_at_unit',
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
     * Relationship: Originating Creation Seat (e.g. Inward Seat)
     */
    public function createdBySeat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'created_by_seat_id', 'seat_id');
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
     * Return this petition's addresses and, for linked duplicates, its original's
     * addresses without repeating identical records copied by older link flows.
     *
     * Fix 3: If this is a linked duplicate, show ONLY the original petition's
     * addresses — prevents double-entry when complainant is re-entered on the duplicate.
     */
    public function displayAddresses(): EloquentCollection
    {
        // Fix 3: Linked duplicates always display the original petition's addresses only.
        if ($this->linked_petition_id && $this->originalPetition) {
            return $this->originalPetition->uniqueOwnAddresses();
        }

        return $this->uniqueOwnAddresses();
    }

    public function uniqueOwnAddresses(): EloquentCollection
    {
        return $this->uniqueAddressRecords($this->addresses);
    }

    private function uniqueAddressRecords(EloquentCollection $addresses): EloquentCollection
    {
        $identityFields = [
            'person_type',
            'entity_type',
            'person_name',
            'contact_person',
            'department_id',
            'designation_id',
            'phone',
            'email',
            'pen_number',
            'Aadhar_number',
            'address_type',
            'is_primary',
            'full_address',
            'district_id',
            'pincode',
        ];

        // Fix 1: Normalize string fields (person_name, contact_person) to lowercase
        // before comparison so "ABHIJITH" and "Abhijith" are treated as the same person.
        $caseInsensitiveFields = ['person_name', 'contact_person'];

        return $addresses->unique(function (Address $address) use ($identityFields, $caseInsensitiveFields): string {
            $identity = [];
            foreach ($identityFields as $field) {
                $value = $address->getAttribute($field);
                if (in_array($field, $caseInsensitiveFields, true) && is_string($value)) {
                    $value = mb_strtolower(trim($value));
                }
                $identity[] = $value;
            }

            return json_encode($identity, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        })->values();
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
        return $this->hasOne(Decision::class, 'petition_id', 'petition_id')->latestOfMany('decision_id');
    }

    /**
     * Relationship: Unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    public function concernedDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'concerned_district_id', 'district_id');
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
        if ($this->created_by_seat_id) {
            return in_array($this->created_by_seat_id, Seat::getInwardSeatIds());
        }

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
     * Get user-friendly display status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === self::STATUS_RECEIVED) {
            return 'Processed';
        }
        if ($this->status === 'Sent_to_Govt') {
            return 'Sent to Govt';
        }
        if ($this->status === 'Sent_to_SPC') {
            return 'Sent to SPC';
        }
        if ($this->status === 'VR_Received') {
            return 'VR Received';
        }
        return (string) $this->status;
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

        // 2. Duplicate / Linked (Evaluated before unprocessed check to ensure processed linked status)
        if ($this->linked_petition_id || $this->status === self::STATUS_DUPLICATE) {
            $origReceipt = $this->originalPetition?->receipt_no;
            return [
                'label' => 'Duplicate',
                'description' => $origReceipt ? 'Linked to ' . $origReceipt : 'Linked Duplicate',
                'badge_class' => 'bg-slate-50 text-slate-700 border border-slate-200',
                'pill_class' => 'bg-slate-100 text-slate-700 border border-slate-200',
                'dot_class' => 'bg-slate-400',
                'color' => 'slate',
            ];
        }

        // 3. Transferred from Inward, awaiting CPSP processing
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
                'badge_class' => 'bg-purple-100 text-purple-900 border border-purple-300',
                'pill_class' => 'bg-purple-100 text-purple-900 border border-purple-300',
                'dot_class' => 'bg-purple-600',
                'color' => 'purple',
            ];
        }

        // 7. Finalized / Decision Taken
        if (in_array($this->status, [self::STATUS_CLOSED, self::STATUS_SENT_TO_GOVT, self::STATUS_SENT_TO_SPC, self::STATUS_INTERNAL_VIGILANCE, self::STATUS_CLOSED_BY_GOVT]) || $this->decision) {
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

    /**
     * Get IDs of users who have created inward petitions or hold Inward seats/roles.
     */
    public static function getInwardCreatorUserIds(): array
    {
        $inwardSeatIds = Seat::getInwardSeatIds();

        $userIdsFromPetitions = self::whereIn('created_by_seat_id', $inwardSeatIds)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();

        $userIdsFromSeats = SeatUser::whereIn('seat_id', $inwardSeatIds)
            ->pluck('user_id')
            ->toArray();

        $userIdsFromRoles = User::role(['inward', 'Inward'])->pluck('user_id')->toArray();

        $allIds = array_values(array_unique(array_merge($userIdsFromPetitions, $userIdsFromSeats, $userIdsFromRoles)));

        return empty($allIds) ? [-1] : $allIds;
    }

    /**
     * Scope: Strictly petitions registered/originated through the Inward section.
     */
    public function scopeOriginatedFromInward(Builder $query): Builder
    {
        $inwardSeatIds = Seat::getInwardSeatIds();

        return $query->where(function ($q) use ($inwardSeatIds) {
            $q->whereIn('petitions.created_by_seat_id', $inwardSeatIds)
              ->orWhereHas('user.roles', fn($r) => $r->whereIn('name', ['inward', 'Inward']))
              ->orWhereHas('user.seatUsers', function ($su) use ($inwardSeatIds) {
                  $su->where('is_active', true)->whereIn('seat_id', $inwardSeatIds);
              })
              ->orWhere(function ($fallbackQ) use ($inwardSeatIds) {
                  $fallbackQ->whereNull('petitions.created_by_seat_id')
                            ->where(function ($sub) use ($inwardSeatIds) {
                                $sub->where('nature_of_petition', 'General Inward')
                                    ->orWhere('is_cpsp_processed', false)
                                    ->orWhereIn('cpsp_opened_by_seat_id', $inwardSeatIds);
                            });
              });
        });
    }

    /**
     * Scope: Filter petitions for the View Inward Petitions module based on user role and permissions.
     * Enforces strictly that only petitions created or registered by Inward seats or Inward workflow are returned.
     */
    public function scopeAccessibleInwardPetitions(Builder $query, User $user, ?string $scopeFilter = null): Builder
    {
        // Strictly exclude T-Section confidential petitions if user does not have clearance
        $query->excludeTSectionIfNotAuthorized($user);

        // MANDATORY BASE: Only petitions that originated from an Inward Seat or Inward registration
        $query->originatedFromInward();

        $currentSeatUser = $user->currentSeatUser();
        $currentSeat = $currentSeatUser?->seat;

        // 1. Admin / Super Admin / Developer: View all inward petitions
        $isAdminOrSuperAdmin = $user->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']) ||
            ($currentSeat && $currentSeat->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin'])) ||
            $user->canAccess('access admin dashboard');

        if ($isAdminOrSuperAdmin) {
            return $query;
        }

        // 2. Inward Role / Current Inward Seat: View inward petitions
        $isInwardUser = $user->hasRole(['inward', 'Inward']) || 
            ($currentSeat && $currentSeat->isInward());

        if ($isInwardUser) {
            if (($scopeFilter === 'all' && $user->canAccess('view all petitions')) || $user->canAccess('view file transfer') || $user->canAccess('view inward registration')) {
                return $query;
            }
            return $query->where(function ($q) use ($user, $currentSeat) {
                if ($currentSeat) {
                    $q->where('petitions.created_by_seat_id', $currentSeat->seat_id)
                      ->orWhere('petitions.user_id', $user->user_id);
                } else {
                    $q->where('petitions.user_id', $user->user_id);
                }
            });
        }

        // 3. CPSP Role: View inward petitions transferred to their assigned seat (or all inward if granted global view)
        $isCpspUser = $user->hasRole(['cpsp', 'CPSP']) || 
            ($currentSeat && (
                $currentSeat->hasRole(['cpsp', 'CPSP']) || 
                str_contains(strtolower($currentSeat->seat_name), 'cpsp')
            ));

        if ($isCpspUser) {
            if ($user->canAccess('view all petitions') && !session('is_impersonating_seat')) {
                return $query;
            }

            // CPSP user restricted to assigned seat / own creations
            return $query->where(function ($q) use ($user, $currentSeat) {
                if ($currentSeat) {
                    $q->where('petitions.seat_id', $currentSeat->seat_id)
                      ->orWhere('petitions.user_id', $user->user_id);
                } else {
                    $q->where('petitions.user_id', $user->user_id);
                }
            });
        }

        // 4. T-Section Role / Seat: View inward petitions assigned strictly to their section/seat
        $isTSectionUser = $user->isTSectionRole() || ($currentSeat && $currentSeat->isTSection());
        if ($isTSectionUser) {
            return $query->where(function ($q) use ($user, $currentSeat) {
                if ($currentSeat) {
                    $q->where('petitions.seat_id', $currentSeat->seat_id)
                      ->orWhere('petitions.user_id', $user->user_id);
                } else {
                    $q->where('petitions.user_id', $user->user_id);
                }
            });
        }

        // 5. Other Authorized Roles
        if ($user->canAccess('view all petitions') && !session('is_impersonating_seat')) {
            return $query;
        }

        return $query->where(function ($q) use ($user, $currentSeat) {
            if ($currentSeat) {
                $q->where('petitions.seat_id', $currentSeat->seat_id)
                  ->orWhere('petitions.user_id', $user->user_id);
            } else {
                $q->where('petitions.user_id', $user->user_id);
            }
        });
    }

    public function scopeFilterByTab(Builder $query, string $tab): Builder
    {
        return match ($tab) {
            'inward' => $query->where('is_cpsp_processed', false)
                              ->where('is_returned_to_inward', false)
                              ->whereNull('linked_petition_id')
                              ->where('status', '!=', self::STATUS_DUPLICATE),
            'received' => $query->where('status', self::STATUS_RECEIVED)->where('is_cpsp_processed', true),
            'forwarded' => $query->where('status', self::STATUS_FORWARDED),
            'vrs' => $query->where('status', self::STATUS_VR_RECEIVED),
            'decisions' => $query->whereIn('status', [self::STATUS_SENT_TO_GOVT, self::STATUS_SENT_TO_SPC, self::STATUS_INTERNAL_VIGILANCE, self::STATUS_CLOSED]),
            'all' => $query->where('is_cpsp_processed', true),
            default => $query->where('is_cpsp_processed', true),
        };
    }

    public function scopeFilterByStatus(Builder $query, ?string $status, string $tab = 'all'): Builder
    {
        if (empty($status)) return $query;

        $finalDecisionStatuses = ['VC', 'VE', 'PE', 'SC', 'CV', 'IV', 'Internal Vigilance', 'ICell', 'Closed', 'Sent to Govt', 'Sent to SPC'];

        if ($status === self::STATUS_FORWARDED) {
            return $query->where('status', self::STATUS_FORWARDED);
        } 
        
        if ($status === self::STATUS_VR_RECEIVED) {
            return $query->where('status', self::STATUS_VR_RECEIVED);
        } 
        
        if ($status === 'VR_Received_at_cpsp_date') {
            return $query->whereHas('forwardings', fn($q) => $q->whereNotNull('vr_received_at_cpsp_date'));
        } 
        
        if ($status === self::STATUS_RECEIVED || $status === 'Processed') {
            return $query->where('status', self::STATUS_RECEIVED)
                         ->where('is_cpsp_processed', true);
        }

        if ($status === 'All_Final_Decisions') {
            return $query->whereHas('decision');
        }

        if ($status === 'Pending') {
            return $query->doesntHave('decision');
        }

        if ($status === 'IV' || $status === 'Internal Vigilance') {
            return $query->whereHas('decision', fn($q) => $q->whereIn('final_decision', ['IV', 'Internal Vigilance']));
        }

        if (in_array($status, $finalDecisionStatuses)) {
            return $query->whereHas('decision', fn($q) => $q->where('final_decision', $status));
        }

        return $query->where('status', $status);
    }

    public function scopeFilterByRecommendationType(Builder $query, ?string $recommendationType, ?string $status = null): Builder
    {
        if (empty($recommendationType) || in_array(strtolower($recommendationType), ['all', 'all recommendation types'])) {
            return $query;
        }

        if ($status && $status !== 'Sent to Govt') {
            return $query;
        }

        return $query->whereHas('decision', function ($q) use ($recommendationType) {
            $q->where('govt_recommendation_type', $recommendationType);
        });
    }

    public function scopeSearch(Builder $query, ?string $search, ?string $searchType = null): Builder
    {
        if (empty($search)) return $query;

        return $query->where(function ($q) use ($search, $searchType) {
            if (in_array($searchType, ['unit_petition_no', 'petition_number_at_unit'])) {
                $q->where('petition_number_at_unit', 'like', "%{$search}%");
                return;
            }

            // Search receipt_no, file_no, or petition_number_at_unit if searchType is empty/all or explicitly petition/receipt/file no
            $matchPetitionNo = empty($searchType) || in_array($searchType, ['petition_no', 'receipt_no', 'file_no']);
            if ($matchPetitionNo) {
                $q->where(function ($qNo) use ($search) {
                    $qNo->where('receipt_no', 'like', "%{$search}%")
                        ->orWhere('file_no', 'like', "%{$search}%")
                        ->orWhere('petition_number_at_unit', 'like', "%{$search}%");
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

        $finalDecisionStatuses = ['VC', 'VE', 'PE', 'SC', 'CV', 'IV', 'Internal Vigilance', 'Closed', 'Sent to Govt', 'Sent to SPC', 'ICell'];

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

        // Combined OR filter: include petitions received within the date range (or created within range if received date is null) OR processed within the date range
        return $query->where(function ($q) use ($parsedFrom, $parsedTo) {
            $q->where(function ($sub) use ($parsedFrom, $parsedTo) {
                if ($parsedFrom) $sub->whereDate('date_of_petition_received', '>=', $parsedFrom);
                if ($parsedTo) $sub->whereDate('date_of_petition_received', '<=', $parsedTo);
            })->orWhere(function ($sub) use ($parsedFrom, $parsedTo) {
                $sub->whereNull('date_of_petition_received');
                if ($parsedFrom) $sub->whereDate('created_at', '>=', $parsedFrom);
                if ($parsedTo) $sub->whereDate('created_at', '<=', $parsedTo);
            })->orWhere(function ($sub) use ($parsedFrom, $parsedTo) {
                if ($parsedFrom) $sub->whereDate('processed_at', '>=', $parsedFrom);
                if ($parsedTo) $sub->whereDate('processed_at', '<=', $parsedTo);
            });
        });
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

    /**
     * Check if this petition belongs to a T-Section (Confidential) seat.
     */
    public function isTSection(): bool
    {
        if (!$this->seat_id) {
            return false;
        }
        $tSeatIds = Seat::getTSectionSeatIds();
        return in_array($this->seat_id, $tSeatIds);
    }

    /**
     * Check if this petition is linked across sections (CPSP <-> T-Section).
     */
    public function isCrossSectionLinked(): bool
    {
        if (!$this->linked_petition_id) {
            return false;
        }
        $original = $this->originalPetition;
        if (!$original) {
            return false;
        }
        return $this->isTSection() !== $original->isTSection();
    }

    /**
     * Check whether a user is allowed to view sensitive internal workflow details
     * (VR reports, forwarding history, final decisions, remarks) of this petition.
     */
    public function canViewWorkflowDetails(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->canAccess('access admin dashboard')) {
            return true;
        }

        if ($this->isTSection()) {
            return $user->canAccessTSection() || $user->canAccessTSectionPending();
        }

        $currentSeat = $user->currentSeatUser()?->seat;
        $isTSectionUser = ($currentSeat && $currentSeat->isTSection()) || $user->isTSectionRole() || $user->canAccessTSection();

        // T-Section user viewing CPSP petition in read-only mode cannot view sensitive workflow details
        if ($isTSectionUser && !$user->canAccessCpspSection()) {
            return false;
        }

        return true;
    }

    /**
     * Unconditionally scope a query to exclude all T-Section confidential petitions.
     * Prevents data leaks in non-confidential modules (Department-Wise Analysis, Inward Statistics).
     */
    public function scopeExcludeTSection(Builder $query): Builder
    {
        $tSeatIds = Seat::getTSectionSeatIds();
        if (!empty($tSeatIds)) {
            $query->where(function ($q) use ($tSeatIds) {
                $q->whereNotIn('petitions.seat_id', $tSeatIds)
                  ->orWhereNull('petitions.seat_id');
            });
        }

        $query->whereDoesntHave('seat', function ($sq) {
            $sq->where(function ($tq) {
                $tq->where('seat_name', 'like', '%T Section%')
                   ->orWhere('seat_name', 'like', '%T-Section%')
                   ->orWhere('seat_name', 'like', 'T-%')
                   ->orWhere('seat_name', 'like', 'T1%')
                   ->orWhere('seat_name', 'like', 'T2%')
                   ->orWhere('seat_name', 'like', 'T3%')
                   ->orWhereHas('roles', fn($rq) => $rq->whereIn('name', ['T Section', 't section', 'T-Section', 't-section']));
            });
        });

        return $query;
    }

    /**
     * Scope to exclude T-Section petitions for users without secret clearance.
     */
    public function scopeExcludeTSectionIfNotAuthorized(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();
        if ($user && ($user->canAccessTSection() || $user->canAccessTSectionPending() || $user->canAccessTSectionInReports())) {
            return $query;
        }

        return $this->scopeExcludeTSection($query);
    }

    /**
     * Resolve the current assigned seat and the assigned user handling this petition.
     *
     * @return array{seat_name: string|null, user_name: string|null}
     */
    public function getCurrentSeatAndUser(): array
    {
        $seat = null;
        if ($this->is_returned_to_inward) {
            $seat = $this->createdBySeat ?? $this->seat;
        } else {
            $seat = $this->seat ?? $this->processedBySeat ?? $this->createdBySeat;
        }

        $seatName = $seat?->seat_name ?? ($this->is_returned_to_inward ? 'Inward' : null);

        // 1. Try active assignment on the current assigned seat
        $userName = $seat?->activeAssignment?->user?->name
            ?? $seat?->activeAssignments()->first()?->user?->name;

        // 2. Fallbacks if seat has no active occupant assigned
        if (!$userName) {
            $userName = $this->processedByUser?->name 
                ?? $this->updatedByUser?->name 
                ?? $this->cpspOpenedByUser?->name 
                ?? $this->user?->name;
        }

        return [
            'seat_name' => $seatName,
            'user_name' => $userName,
        ];
    }

    /**
     * Format an informative error message when a duplicate Receipt Number is detected.
     */
    public static function formatDuplicateReceiptMessage(self $existing): string
    {
        $info = $existing->getCurrentSeatAndUser();
        $seatName = $info['seat_name'] ?? 'Unknown Seat';
        $userName = $info['user_name'];

        if ($userName && $seatName) {
            return "This Receipt Number already exists in {$seatName} ({$userName}).";
        }

        if ($seatName && $seatName !== 'Unknown Seat') {
            return "This Receipt Number already exists in {$seatName}.";
        }

        return "This Receipt Number already exists.";
    }

    /**
     * Reusable validation rule closure for receipt number uniqueness with informative seat/user message.
     */
    public static function receiptUniquenessRule(?int $ignorePetitionId = null): \Closure
    {
        return function ($attribute, $value, $fail) use ($ignorePetitionId) {
            if (!$value || !trim((string)$value)) {
                return;
            }

            $query = self::with([
                'seat.activeAssignment.user',
                'seat.activeAssignments.user',
                'processedBySeat',
                'processedByUser',
                'createdBySeat',
                'user',
            ])->where('receipt_no', trim((string)$value));

            if ($ignorePetitionId) {
                $query->where('petition_id', '!=', $ignorePetitionId);
            }

            $existing = $query->first();
            if ($existing) {
                $fail(self::formatDuplicateReceiptMessage($existing));
            }
        };
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
