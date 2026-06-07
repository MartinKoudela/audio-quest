<?php

namespace App\Models;

use App\Enums\Popularity;
use App\Enums\ScanStatus;
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
        'themes',
        'royalty_free_only',
        'ai_prompt',
        'result',
        'result_source',
    ];

    protected $casts = [
        'status'          => ScanStatus::class,
        'popularity'      => Popularity::class,
        'similar_artists'   => 'array',
        'similar_tracks'    => 'array',
        'themes'            => 'array',
        'royalty_free_only' => 'boolean',
        'result'            => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}