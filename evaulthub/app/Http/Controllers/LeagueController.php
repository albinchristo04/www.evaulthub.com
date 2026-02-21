<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class LeagueController extends Controller
{
    public function show(string $league): View
    {
        $leagueName = MatchModel::query()
            ->whereNotNull('league')
            ->get(['league'])
            ->pluck('league')
            ->unique()
            ->first(fn ($item) => Str::slug((string) $item) === $league);

        abort_if(!$leagueName, 404);

        $matches = MatchModel::query()
            ->with(['server'])
            ->withCount('streams')
            ->where('league', $leagueName)
            ->whereNull('deleted_at')
            ->orderByDesc('match_datetime')
            ->paginate(24);

        return view('league.show', [
            'leagueName' => $leagueName,
            'matches' => $matches,
            'h1' => 'Watch '.$leagueName.' Live Streams Free Online',
            'seo' => [
                'title' => 'Watch '.$leagueName.' Live Streams Free Online | EVaultHub',
                'description' => 'Watch '.$leagueName.' live streams free online in HD. Stream every match live with updated links and multiple channels on EVaultHub.',
                'keywords' => strtolower($leagueName).' live stream free, watch '.$leagueName.' online, hd football streaming',
                'canonical' => route('league.show', ['league' => $league]),
                'og_type' => 'website',
            ],
        ]);
    }
}
