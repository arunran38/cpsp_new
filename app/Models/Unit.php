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
    ];

    public function seats()
    {
        return $this->belongsToMany(Seat::class, 'seat_unit', 'unit_id', 'seat_id');
    }
}
