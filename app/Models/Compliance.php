<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compliance extends Model
{
    protected $primaryKey = 'compliance_id';

    protected $fillable = [
        'decision_id',
        'petition_id',
        'action_number',
        'action_date',
        'unit_id',
        'remarks',
        'created_by_user_id',
    ];

    public function decision()
    {
        return $this->belongsTo(Decision::class, 'decision_id');
    }

    public function petition()
    {
        return $this->belongsTo(Petition::class, 'petition_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
