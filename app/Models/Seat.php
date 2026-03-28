<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    protected $primaryKey = 'seat_id';

    protected $fillable = [
        'seat_name',
        'is_active',
    ];

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'seat_unit', 'seat_id', 'unit_id');
    }

    public function seatUsers()
    {
        return $this->hasMany(SeatUser::class, 'seat_id', 'seat_id');
    }

    public function activeAssignment()
    {
        return $this->hasOne(SeatUser::class, 'seat_id', 'seat_id')
            ->where('is_active', true);
    }

    public function occupant()
    {
        return $this->activeAssignment()->withDefault();
    }

    public static function countVacant()
    {
        return self::where('is_active', true)
            ->whereDoesntHave('seatUsers', function ($query) {
                $query->where('is_active', true);
            })->count();
    }

    public static function getVacant()
    {
        return self::where('is_active', true)
            ->whereDoesntHave('seatUsers', function ($query) {
                $query->where('is_active', true);
            })->get();
    }
}
