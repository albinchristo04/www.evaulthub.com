<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchStream extends Model
{
    protected $fillable = [
        'match_id',
        'channel_name',
        'iframe_url',
        'stream_type',
        'sort_order',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchModel::class, 'match_id');
    }
}
