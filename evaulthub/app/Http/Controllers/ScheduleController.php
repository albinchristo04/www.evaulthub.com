<?php

namespace App\Http\Controllers;

use App\Models\MatchModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $query = MatchModel::query()
            ->with('server')
            ->withCount('streams')
            ->whereNull('deleted_at')
            ->orderBy('match_datetime');

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where('title', 'like', "%{$term}%");
        }

        $matches = $query->paginate(24)->withQueryString();

        $grouped = $matches->getCollection()->groupBy(fn (MatchModel $match) => $match->league ?: 'Other Matches');

        return view('schedule.index', [
            'matches' => $matches,
            'groupedMatches' => $grouped,
            'h1' => "Today's Live Football Schedule & Free Streams",
            'seo' => [
                'title' => "Today's Live Football Schedule & Free Streams | EVaultHub",
                'description' => "See today's live football schedule with free stream links across leagues and servers. Watch matches online in HD on EVaultHub.",
                'keywords' => "today football schedule, free live football stream, watch soccer online hd",
                'canonical' => route('schedule'),
                'og_type' => 'website',
            ],
        ]);
    }
}
