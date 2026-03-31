<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Upload;


#[Fillable(['name', 'email', 'password', 'mobile_number', 'role', 'designation', 'other_designation', 'pen', 'photo', 'status'])]
#[Hidden(['password', 'remember_token'])]
  
class User extends Authenticatable
{
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number',
        'role',
        'designation',
        'other_designation',
        'pen',
        'photo',
        'status',
    ];

    public function profilePhoto()
    {
        return $this->belongsTo(Upload::class, 'photo', 'upload_id');
    }

    public function seatUsers()
    {
        return $this->hasMany(SeatUser::class, 'user_id', 'user_id');
    }

    /**
     * Get the currently active SeatUser based on session state, 
     * or fallback to the primary active seat.
     */
    public function currentSeatUser()
    {
        $currentSeatId = session('current_seat_id');

        if ($currentSeatId) {
            $seatUser = $this->seatUsers()->where('seat_id', $currentSeatId)->where('is_active', true)->first();
            if ($seatUser) {
                return $seatUser;
            }
        }

        $defaultSeatUser = $this->seatUsers()->where('is_active', true)->first();
        if ($defaultSeatUser) {
            session(['current_seat_id' => $defaultSeatUser->seat_id]);
            return $defaultSeatUser;
        }

        return null;
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;
    


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public static function countUser(){
        return self::where('role', 'user')->count();
    }   
}
