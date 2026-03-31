<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Decision extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'decision_id';

    protected $fillable = [
        'petition_id',
        'decided_by_seat_id',
        'decision_remarks',
        'final_remarks',
        'decision_date',
    ];

    public function petition()
    {
        return $this->belongsTo(Petition::class, 'petition_id', 'petition_id');
    }

    public function decidedBySeat()
    {
        return $this->belongsTo(Seat::class, 'decided_by_seat_id', 'seat_id');
    }
}
