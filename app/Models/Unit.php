<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Unit extends Model
{
    use HasFactory;

    protected $primaryKey = 'unit_id';

    protected $fillable = [
        'unit_name',
        'unit_code',
    ];

    /**
     * Relationship: Associated Seats (Many-to-Many)
     */
    public function seats(): BelongsToMany
    {
        return $this->belongsToMany(Seat::class, 'seat_unit', 'unit_id', 'seat_id');
    }
}
