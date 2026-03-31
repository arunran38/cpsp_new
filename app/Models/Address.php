<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'address_id';
    protected $guarded = [];

    public function petition()
    {
        return $this->belongsTo(Petition::class, 'petition_id');
    }
}
