<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $primaryKey = 'address_id';
    protected $guarded = [];

    public function petition()
    {
        return $this->belongsTo(Petition::class, 'petition_id');
    }
}
