<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Upload extends Model
{
    use SoftDeletes, \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $primaryKey = 'upload_id';
    protected $guarded = [];

    const CATEGORY_PROFILE_PHOTO = 'Profile Photo';
    const CATEGORY_PETITION_DOCUMENT = 'Petition Document';
    const CATEGORY_VERIFICATION_REPORT = 'Verification Report';
    const CATEGORY_FINAL_ORDER = 'Final Order';
    const CATEGORY_OTHERS = 'Others';

    public function petition()
    {
        return $this->belongsTo(Petition::class, 'petition_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
