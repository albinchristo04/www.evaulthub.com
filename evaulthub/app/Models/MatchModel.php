<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchModel extends Model
{
    use SoftDeletes;

    protected $table = 'matches';

    protected $fillable = [
        'title',
        'league',
        'team_home',
        'team_away',
        'match_datetime',
        'country',
        'server_id',
        'slug',
        'fingerprint',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'match_datetime' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    public function streams(): HasMany
    {
        return $this->hasMany(MatchStream::class, 'match_id')->orderBy('sort_order');
    }

    public function scopePublicVisible(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    public function computeStatus(?Carbon $now = null): string
    {
        if (!$this->match_datetime) {
            return 'upcoming';
        }

        $now = $now ?? now();
        $start = $this->match_datetime->copy();
        $end = $start->copy()->addHours(2);

        if ($now->between($start, $end)) {
            return 'live';
        }

        if ($now->lt($start)) {
            return 'upcoming';
        }

        return 'finished';
    }

    public function getComputedStatusAttribute(): string
    {
        return $this->computeStatus();
    }

    public function getCountryFlagAttribute(): string
    {
        $map = [
            'Argentina' => '🇦🇷',
            'Brazil' => '🇧🇷',
            'Chile' => '🇨🇱',
            'Colombia' => '🇨🇴',
            'Ecuador' => '🇪🇨',
            'England' => '🇬🇧',
            'France' => '🇫🇷',
            'Germany' => '🇩🇪',
            'Italy' => '🇮🇹',
            'Mexico' => '🇲🇽',
            'México' => '🇲🇽',
            'Peru' => '🇵🇪',
            'Portugal' => '🇵🇹',
            'Spain' => '🇪🇸',
            'United States' => '🇺🇸',
            'USA' => '🇺🇸',
            'Uruguay' => '🇺🇾',
            'Venezuela' => '🇻🇪',
        ];

        return $map[$this->country ?? ''] ?? '🌍';
    }
}
