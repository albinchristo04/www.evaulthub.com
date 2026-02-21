<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use App\Models\Server;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $servers = Server::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $matchesByServer = [];
        $leaguesByServer = [];

        foreach ($servers as $server) {
            $matches = MatchModel::query()
                ->with('server')
                ->withCount('streams')
                ->where('server_id', $server->id)
                ->whereNull('deleted_at')
                ->orderByDesc('match_datetime')
                ->limit(24)
                ->get();

            $matchesByServer[$server->id] = $matches;
            $leaguesByServer[$server->id] = $matches
                ->pluck('league')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();
        }

        return view('home.index', [
            'servers' => $servers,
            'matchesByServer' => $matchesByServer,
            'leaguesByServer' => $leaguesByServer,
            'seo' => [
                'title' => 'Watch Live Football Streams Online Free HD | EVaultHub',
                'description' => "Stream live football, soccer, Liga MX, Premier League, Champions League, LaLiga, Serie A free in HD. Watch today's live matches on EVaultHub.",
                'keywords' => 'live football stream free, watch soccer online free HD, live sports streaming, watch football match online, free live soccer stream',
                'canonical' => route('home'),
                'og_type' => 'website',
            ],
            'h1' => 'Watch Live Football Streams Online Free HD',
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy', [
            'seo' => [
                'title' => 'Privacy Policy | EVaultHub',
                'description' => 'Read EVaultHub privacy policy covering Google AdSense cookies, analytics tracking, data collection and third-party streaming embeds.',
                'keywords' => 'EVaultHub privacy policy, AdSense cookies policy, live stream website privacy',
                'canonical' => route('privacy'),
                'og_type' => 'article',
            ],
            'h1' => 'Privacy Policy',
        ]);
    }

    public function dmca(): View
    {
        return view('pages.dmca', [
            'seo' => [
                'title' => 'DMCA Notice | EVaultHub',
                'description' => 'Submit DMCA takedown requests to EVaultHub. We aggregate external links and do not host copyrighted media content.',
                'keywords' => 'DMCA notice, copyright takedown, EVaultHub DMCA',
                'canonical' => route('dmca'),
                'og_type' => 'article',
            ],
            'h1' => 'DMCA Notice',
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'seo' => [
                'title' => 'Contact EVaultHub | Live Sports Streaming Support',
                'description' => 'Contact EVaultHub for support, feedback, and business inquiries related to free live sports streaming links and updates.',
                'keywords' => 'contact EVaultHub, sports streaming support, live stream contact',
                'canonical' => route('contact'),
                'og_type' => 'website',
            ],
            'h1' => 'Contact EVaultHub',
        ]);
    }
}
