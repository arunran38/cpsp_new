<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Upload extends Model
{
    protected $primaryKey = 'upload_id';
    protected $guarded = [];

    public function petition()
    {
        return $this->belongsTo(Petition::class, 'petition_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
