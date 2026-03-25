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

    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 5;

    public function forwardings()
    {
        return $this->hasMany(PetitionForwarding::class, 'petition_id', 'petition_id')->orderBy('created_at', 'desc');
    }

    public function latestForwarding()
    {
        return $this->hasOne(PetitionForwarding::class, 'petition_id', 'petition_id')->latestOfMany('petition_forwarding_id');
    }

    public function decision()
    {
        return $this->hasOne(Decision::class, 'petition_id', 'petition_id');
    }
   public static function countForwardedPetitions()
   {
    return self::where('status','=','Forwarded')->count();
   }
   public static function countPendingPetitions()
   {
    return self::where('status','=','Received')->count();
   }
   public static function countVrPetitions()
   {
    return self::where('status','=','VR_Received')->count();
   }
   public static function countDecisionPetitions()
   {
    return self::where('status','=','Sent_to_Govt')->count();
   }
   public static function countClosedPetitions()
   {
    return self::where('status','=','Closed_by_Govt')->count();
   }
}
