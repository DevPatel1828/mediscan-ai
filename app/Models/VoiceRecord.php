<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceRecord extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'text_content',
        'voice_file_path',
        'language_code'
    ];

    /**
     * Relationship: User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
