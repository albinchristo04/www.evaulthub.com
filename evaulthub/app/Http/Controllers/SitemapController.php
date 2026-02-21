<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $matches = MatchModel::query()
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->get(['slug', 'league', 'updated_at']);

        $leagueSlugs = $matches
            ->pluck('league')
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($league) => [Str::slug((string) $league) => (string) $league])
            ->all();

        return response()
            ->view('sitemap.index', [
                'matches' => $matches,
                'leagueSlugs' => $leagueSlugs,
            ])
            ->header('Content-Type', 'application/xml');
    }
}
