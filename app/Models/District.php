<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'district_id';
    protected $guarded = [];
    protected $appends = ['display_name'];

    /**
     * Default global ordering by database table order (district_id).
     */
    protected static function booted(): void
    {
        static::addGlobalScope('orderById', function ($builder) {
            $builder->orderBy($builder->getModel()->getTable() . '.district_id', 'asc');
        });
    }

    /**
     * Standard display label with district code: e.g. "Thiruvananthapuram (TVM)".
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->district_name . ($this->district_code ? ' (' . $this->district_code . ')' : '');
    }

    /**
     * Check if the district represents statewide jurisdiction.
     */
    public function isStatewide(): bool
    {
        return in_array(strtolower((string)$this->district_name), ['statewide', 'state wide']) 
            || strtoupper((string)$this->district_code) === 'STW';
    }

    /**
     * Scope query to exclude statewide options (used for physical addresses).
     */
    public function scopeForAddress($query)
    {
        return $query->whereNotIn('district_code', ['STW', 'stw'])
            ->whereRaw("LOWER(district_name) NOT IN ('statewide', 'state wide')");
    }

    /**
     * Get all districts formatted as an associative array [district_id => "District Name (CODE)"]
     * in the exact database order.
     */
    public static function getOptionsArray(): array
    {
        return static::get()->pluck('display_name', 'district_id')->toArray();
    }

    /**
     * Get address district options (14 Kerala districts + Other State, excluding Statewide).
     */
    public static function getAddressOptionsArray(): array
    {
        return static::forAddress()->get()->pluck('display_name', 'district_id')->toArray();
    }
}
