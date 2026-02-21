<?php

namespace App\Services;

use App\Models\MatchModel;
use App\Models\Server;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ImportService
{
    /**
     * @param array<int, array<string, mixed>> $matches
     * @return array{imported: int, skipped: int}
     */
    public function importMatches(Server $server, array $matches): array
    {
        $imported = 0;
        $skipped = 0;

        foreach ($matches as $matchData) {
            $title = trim((string) ($matchData['title'] ?? ''));
            $date = (string) ($matchData['date_diary'] ?? '');
            if ($title === '' || $date === '') {
                $skipped++;
                continue;
            }

            $fingerprint = $this->buildFingerprint($title, $date);

            $exists = MatchModel::query()->where('fingerprint', $fingerprint)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            $slugBase = Str::slug($title.' '.$date);
            $slug = $this->makeUniqueSlug($slugBase);
            $status = $this->deriveStatus($matchData['match_datetime'] ?? null);

            $match = MatchModel::query()->create([
                'title' => $title,
                'league' => $matchData['league'] ?? null,
                'team_home' => $matchData['team_home'] ?? null,
                'team_away' => $matchData['team_away'] ?? null,
                'match_datetime' => $matchData['match_datetime'] ?? null,
                'country' => $matchData['country'] ?? null,
                'server_id' => $server->id,
                'slug' => $slug,
                'fingerprint' => $fingerprint,
                'status' => $status,
                'is_featured' => false,
            ]);

            $streams = $matchData['streams'] ?? [];
            if (is_array($streams)) {
                foreach ($streams as $index => $stream) {
                    if (!is_array($stream)) {
                        continue;
                    }

                    $url = trim((string) ($stream['iframe_url'] ?? ''));
                    if ($url === '') {
                        continue;
                    }

                    $match->streams()->create([
                        'channel_name' => $stream['channel_name'] ?? 'Channel '.($index + 1),
                        'iframe_url' => $url,
                        'stream_type' => $stream['stream_type'] ?? (Str::contains(strtolower($url), '.m3u8') ? 'm3u8' : 'iframe'),
                        'sort_order' => (int) ($stream['sort_order'] ?? $index),
                    ]);
                }
            }

            $imported++;
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
        ];
    }

    public function buildFingerprint(string $title, string $date): string
    {
        return md5(strtolower(trim($title)).$date);
    }

    /**
     * @param mixed $dateTime
     */
    protected function deriveStatus($dateTime): string
    {
        if (!$dateTime) {
            return 'upcoming';
        }

        $now = now();
        $start = $dateTime instanceof Carbon ? $dateTime : Carbon::parse((string) $dateTime);
        $end = $start->copy()->addHours(2);

        if ($now->between($start, $end)) {
            return 'live';
        }

        if ($now->lt($start)) {
            return 'upcoming';
        }

        return 'finished';
    }

    protected function makeUniqueSlug(string $base): string
    {
        $slug = $base !== '' ? $base : 'match-'.Str::random(6);
        $counter = 2;

        while (MatchModel::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
