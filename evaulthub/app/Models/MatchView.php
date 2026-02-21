<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchView extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'viewed_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'match_id',
        'server_id',
        'match_title',
        'viewed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }
}
