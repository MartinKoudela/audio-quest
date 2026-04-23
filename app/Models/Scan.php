<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scan extends Model
{
    protected $fillable = [
        'user_id',
        'job_id',
        'status',
        'mood',
        'vibe',
        'genre',
        'tempo',
        'era',
        'language',
        'occasion',
        'similar_artists',
        'similar_tracks',
        'instruments',
        'popularity',
        'description',
        'ai_prompt',
        'result',
        'result_source',
    ];

    protected $casts = [
        'similar_artists' => 'array',
        'similar_tracks'  => 'array',
        'result'          => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}