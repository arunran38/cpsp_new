<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Upload;


#[Fillable(['name', 'email', 'password'])]
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
    ];

    public function profilePhoto()
    {
        return $this->belongsTo(Upload::class, 'photo', 'upload_id');
    }

    public function seatUsers()
    {
        return $this->hasMany(SeatUser::class, 'user_id', 'user_id');
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    


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
}
