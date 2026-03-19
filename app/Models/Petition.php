<?php

namespace App\Models;
use App\Http\Controllers\PetitionController;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Address;
use App\Models\Upload;

class Petition extends Model
{
    protected $primaryKey = 'petition_id';

   protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'petition_id');
    }

    public function uploads()
    {
        return $this->hasMany(Upload::class, 'petition_id');
    }
}
