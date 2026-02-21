<?php

namespace App\Services;

use App\Models\Server;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MatchService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchAndParse(Server $server): array
    {
        $cacheKey = sprintf('server_%d_json', $server->id);

        $payload = Cache::remember($cacheKey, 300, function () use ($server) {
            return Http::timeout(20)->get($server->json_url)->json();
        });

        if (!is_array($payload)) {
            return [];
        }

        return $server->id === 1
            ? $this->parseServerOnePayload($payload)
            : $this->parseServerTwoPayload($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, mixed>>
     */
    protected function parseServerOnePayload(array $payload): array
    {
        $rows = Arr::get($payload, 'data', []);

        if (!is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->map(function (array $item): ?array {
                $attributes = Arr::get($item, 'attributes', []);
                $title = trim((string) Arr::get($attributes, 'diary_description', ''));
                $date = (string) Arr::get($attributes, 'date_diary', '');
                $time = (string) Arr::get($attributes, 'diary_hour', '00:00:00');

                if ($title === '' || $date === '') {
                    return null;
                }

                $teams = $this->extractTeamsFromTitle($title);
                $league = $this->extractLeagueFromTitle($title);
                $country = (string) Arr::get($attributes, 'country.data.attributes.name', '');
                $streams = collect(Arr::get($attributes, 'embeds.data', []))
                    ->map(function (array $embed, int $index): ?array {
                        $embedAttributes = Arr::get($embed, 'attributes', []);
                        $url = trim((string) Arr::get($embedAttributes, 'decoded_iframe_url', ''));

                        if ($url === '') {
                            return null;
                        }

                        return [
                            'channel_name' => trim((string) Arr::get($embedAttributes, 'embed_name', 'Channel '.($index + 1))),
                            'iframe_url' => $url,
                            'stream_type' => Str::endsWith(strtolower($url), '.m3u8') ? 'm3u8' : 'iframe',
                            'sort_order' => $index,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($streams === []) {
                    return null;
                }

                return [
                    'title' => preg_replace('/\s+/', ' ', $title) ?? $title,
                    'league' => $league,
                    'team_home' => $teams['team_home'],
                    'team_away' => $teams['team_away'],
                    'date_diary' => $date,
                    'time_diary' => $time,
                    'match_datetime' => $this->combineDateTime($date, $time),
                    'country' => $country,
                    'streams' => $streams,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, mixed>>
     */
    protected function parseServerTwoPayload(array $payload): array
    {
        $categories = Arr::get($payload, 'events.streams', []);

        if (!is_array($categories)) {
            return [];
        }

        $matches = [];

        foreach ($categories as $category) {
            if (!is_array($category)) {
                continue;
            }

            $league = trim((string) ($category['category'] ?? ''));
            $streams = Arr::get($category, 'streams', []);

            if (!is_array($streams)) {
                continue;
            }

            foreach ($streams as $streamItem) {
                if (!is_array($streamItem)) {
                    continue;
                }

                $title = trim((string) ($streamItem['name'] ?? $streamItem['uri_name'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $timestamp = (int) ($streamItem['starts_at'] ?? 0);
                $matchDate = $timestamp > 0 ? Carbon::createFromTimestamp($timestamp, config('app.timezone')) : null;
                $date = $matchDate?->format('Y-m-d') ?? now()->format('Y-m-d');
                $time = $matchDate?->format('H:i:s') ?? '00:00:00';
                $country = trim((string) ($streamItem['locale'] ?? 'International'));
                $teams = $this->extractTeamsFromTitle($title);

                $normalizedStreams = [];
                $primaryUrl = trim((string) ($streamItem['iframe'] ?? ''));

                if ($primaryUrl !== '') {
                    $normalizedStreams[] = [
                        'channel_name' => trim((string) ($streamItem['tag'] ?? 'Channel 1')),
                        'iframe_url' => $primaryUrl,
                        'stream_type' => $this->detectStreamType($primaryUrl),
                        'sort_order' => 0,
                    ];
                }

                $substreams = Arr::get($streamItem, 'substreams', []);
                if (is_array($substreams)) {
                    foreach ($substreams as $index => $substream) {
                        if (!is_array($substream)) {
                            continue;
                        }

                        $candidateUrls = $this->extractCandidateUrls($substream);
                        foreach ($candidateUrls as $candidateUrl) {
                            $normalizedStreams[] = [
                                'channel_name' => trim((string) ($substream['name'] ?? $substream['label'] ?? 'Channel '.($index + 2))),
                                'iframe_url' => $candidateUrl,
                                'stream_type' => $this->detectStreamType($candidateUrl),
                                'sort_order' => count($normalizedStreams),
                            ];
                        }
                    }
                }

                $normalizedStreams = collect($normalizedStreams)
                    ->unique('iframe_url')
                    ->values()
                    ->all();

                if ($normalizedStreams === []) {
                    continue;
                }

                $matches[] = [
                    'title' => $title,
                    'league' => $league !== '' ? $league : ($streamItem['category_name'] ?? 'Sports'),
                    'team_home' => $teams['team_home'],
                    'team_away' => $teams['team_away'],
                    'date_diary' => $date,
                    'time_diary' => $time,
                    'match_datetime' => $this->combineDateTime($date, $time),
                    'country' => $country,
                    'streams' => $normalizedStreams,
                ];
            }
        }

        return $matches;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, string>
     */
    protected function extractCandidateUrls(array $payload): array
    {
        $urls = [];
        $keys = ['iframe', 'url', 'hls', 'hls_url', 'm3u8', 'm3u8_url', 'src', 'link'];

        foreach ($keys as $key) {
            $value = $payload[$key] ?? null;
            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            $urls[] = trim($value);
        }

        return $urls;
    }

    protected function detectStreamType(string $url): string
    {
        return Str::contains(strtolower($url), '.m3u8') ? 'm3u8' : 'iframe';
    }

    protected function combineDateTime(string $date, string $time): ?Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', trim($date).' '.trim($time), config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    protected function extractLeagueFromTitle(string $title): ?string
    {
        if (!str_contains($title, ':')) {
            return null;
        }

        $parts = explode(':', $title, 2);
        $league = trim($parts[0]);

        return $league !== '' ? $league : null;
    }

    /**
     * @return array{team_home: string|null, team_away: string|null}
     */
    protected function extractTeamsFromTitle(string $title): array
    {
        $subject = trim(preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $title)) ?? $title);

        if (str_contains($subject, ':')) {
            [, $subject] = explode(':', $subject, 2);
            $subject = trim($subject);
        }

        $patterns = [
            '/(.+)\s+vs\.?\s+(.+)/i',
            '/(.+)\s+v\s+(.+)/i',
            '/(.+)\s+-\s+(.+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $subject, $matches) === 1) {
                return [
                    'team_home' => trim($matches[1]) ?: null,
                    'team_away' => trim($matches[2]) ?: null,
                ];
            }
        }

        return [
            'team_home' => null,
            'team_away' => null,
        ];
    }
}
