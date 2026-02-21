<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use App\Models\MatchView;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MatchController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $match = MatchModel::query()
            ->with(['streams', 'server'])
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $this->recordView($request, $match);

        $relatedMatches = MatchModel::query()
            ->withCount('streams')
            ->where('server_id', $match->server_id)
            ->where('id', '!=', $match->id)
            ->whereNull('deleted_at')
            ->orderByDesc('match_datetime')
            ->limit(8)
            ->get();

        $url = route('watch', $match->slug);
        $title = 'Watch '.$match->title.' Live Stream Free HD | EVaultHub';
        $description = 'Watch '.$match->title.' live stream free in HD. Stream '.$match->league.' online free on EVaultHub. No signup required. HD quality.';
        $keywords = sprintf(
            'watch %s vs %s live, %s live stream, %s free stream online HD',
            $match->team_home ?? 'home team',
            $match->team_away ?? 'away team',
            $match->league ?? 'football',
            $match->title
        );

        $sportsEventSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'SportsEvent',
            'name' => $match->title,
            'sport' => $match->league ?? 'Football',
            'startDate' => optional($match->match_datetime)->toIso8601String(),
            'location' => [
                '@type' => 'Place',
                'name' => $match->country ?? 'International',
            ],
            'eventStatus' => 'https://schema.org/EventScheduled',
            'url' => $url,
            'description' => $description,
        ];

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $match->league ?? 'League',
                    'item' => route('league.show', ['league' => Str::slug($match->league ?? 'league')]),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $match->title,
                    'item' => $url,
                ],
            ],
        ];

        return view('match.show', [
            'match' => $match,
            'relatedMatches' => $relatedMatches,
            'hasM3u8' => $match->streams->contains(fn ($stream) => $stream->stream_type === 'm3u8'),
            'sportsEventSchema' => $sportsEventSchema,
            'breadcrumbSchema' => $breadcrumbSchema,
            'newsKeywords' => implode(',', array_filter([
                $match->team_home,
                $match->team_away,
                $match->league,
                'live stream',
            ])),
            'h1' => 'Watch '.$match->title.' Live Stream Free HD',
            'seo' => [
                'title' => $title,
                'description' => $description,
                'keywords' => $keywords,
                'canonical' => $url,
                'og_type' => 'article',
            ],
        ]);
    }

    protected function recordView(Request $request, MatchModel $match): void
    {
        $ip = (string) $request->ip();

        $alreadyViewed = MatchView::query()
            ->where('match_id', $match->id)
            ->where('ip_address', $ip)
            ->where('viewed_at', '>', now()->subHour())
            ->exists();

        if ($alreadyViewed) {
            return;
        }

        MatchView::query()->create([
            'match_id' => $match->id,
            'server_id' => $match->server_id,
            'match_title' => $match->title,
            'viewed_at' => now(),
            'ip_address' => $ip,
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);
    }
}
