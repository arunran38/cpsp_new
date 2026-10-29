<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';
    protected $primaryKey = 'audit_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'seat_id',
        'user_name',
        'user_pen',
        'action',
        'module',
        'description',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'properties',
        'session_duration',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: User who initiated the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relationship: Seat assigned at the time of action
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id', 'seat_id');
    }

    /**
     * Record an audit log entry explicitly
     */
    public static function record(
        string $action,
        string $module,
        string $description,
        ?array $properties = null,
        ?User $user = null,
        ?string $sessionDuration = null
    ): ?self {
        try {
            $user = $user ?? Auth::user();
            $seatId = null;

            if ($user && method_exists($user, 'currentSeatUser')) {
                $seatUser = $user->currentSeatUser();
                $seatId = $seatUser ? $seatUser->seat_id : null;
            }

            return self::create([
                'user_id'          => $user ? $user->user_id : null,
                'seat_id'          => $seatId,
                'user_name'        => $user ? $user->name : 'System/Guest',
                'user_pen'         => $user ? ($user->pen ?? null) : null,
                'action'           => strtoupper($action),
                'module'           => $module,
                'description'      => $description,
                'method'           => Request::method(),
                'url'              => Request::fullUrl(),
                'ip_address'       => Request::ip(),
                'user_agent'       => Request::userAgent(),
                'properties'       => $properties,
                'session_duration' => $sessionDuration,
                'created_at'       => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Audit log recording failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Scope for filtering audit logs
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when(!empty($filters['search']), function ($q) use ($filters) {
            $search = '%' . trim($filters['search']) . '%';
            $q->where(function ($sub) use ($search) {
                $sub->where('description', 'like', $search)
                    ->orWhere('user_name', 'like', $search)
                    ->orWhere('user_pen', 'like', $search)
                    ->orWhere('ip_address', 'like', $search)
                    ->orWhere('url', 'like', $search);
            });
        });

        $query->when(!empty($filters['action']), function ($q) use ($filters) {
            $q->where('action', strtoupper($filters['action']));
        });

        $query->when(!empty($filters['module']), function ($q) use ($filters) {
            $q->where('module', $filters['module']);
        });

        $query->when(!empty($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        });

        $query->when(!empty($filters['from_date']), function ($q) use ($filters) {
            $q->whereDate('created_at', '>=', $filters['from_date']);
        });

        $query->when(!empty($filters['to_date']), function ($q) use ($filters) {
            $q->whereDate('created_at', '<=', $filters['to_date']);
        });

        return $query;
    }
}

