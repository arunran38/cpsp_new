<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetitionForwarding extends Model
{
    protected $primaryKey = 'petition_forwarding_id';

    protected $fillable = [
        'petition_id',
        'from_seat_id',
        'to_unit_id',
        'director_remarks',
        'forwarded_date',
        'vr_ref_no',
        'vr_date',
        'vr_remarks',
        'vr_received_at_cpsp_date',
    ];

    public function petition()
    {
        return $this->belongsTo(Petition::class, 'petition_id', 'petition_id');
    }

    public function fromSeat()
    {
        return $this->belongsTo(Seat::class, 'from_seat_id', 'seat_id');
    }

    public function toUnit()
    {
        return $this->belongsTo(Unit::class, 'to_unit_id', 'unit_id');
    }
}
