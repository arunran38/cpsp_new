<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $primaryKey = 'unit_id';

    protected $fillable = [
        'unit_name',
        'unit_code',
        'district',
    ];

    protected $casts = [
        'district' => 'array',
    ];

    public function seats()
    {
        return $this->belongsToMany(Seat::class, 'seat_unit', 'unit_id', 'seat_id');
    }
}
