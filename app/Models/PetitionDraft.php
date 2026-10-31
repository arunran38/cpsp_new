<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetitionDraft extends Model
{
    use HasFactory;

    protected $table = 'petition_drafts';

    protected $fillable = [
        'user_id',
        'type',
        'petition_id',
        'step',
        'form_data',
    ];

    protected $casts = [
        'step' => 'integer',
        'form_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function petition(): BelongsTo
    {
        return $this->belongsTo(Petition::class, 'petition_id', 'petition_id');
    }
}

