<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\Server;
use Illuminate\Http\Request;

class MatchApiController extends Controller
{
    public function matches(Request $request)
    {
        $validated = $request->validate([
            'server' => ['nullable', 'integer', 'exists:servers,id'],
            'date' => ['nullable', 'date'],
        ]);

        $query = MatchModel::query()
            ->with('server')
            ->withCount('streams')
            ->whereNull('deleted_at')
            ->orderBy('match_datetime');

        if (!empty($validated['server'])) {
            $query->where('server_id', (int) $validated['server']);
        }

        if (!empty($validated['date'])) {
            $query->whereDate('match_datetime', $validated['date']);
        }

        $matches = $query->limit(100)->get();
        $serverName = !empty($validated['server'])
            ? Server::query()->where('id', $validated['server'])->value('name')
            : 'All Servers';

        return response()->json([
            'success' => true,
            'server' => $serverName,
            'matches' => $matches->map(function (MatchModel $match) {
                return [
                    'id' => $match->id,
                    'title' => $match->title,
                    'league' => $match->league,
                    'team_home' => $match->team_home,
                    'team_away' => $match->team_away,
                    'match_datetime' => optional($match->match_datetime)->toIso8601String(),
                    'country' => $match->country,
                    'status' => $match->computed_status,
                    'slug' => $match->slug,
                    'streams_count' => $match->streams_count,
                    'url' => route('watch', ['slug' => $match->slug]),
                ];
            })->values(),
        ]);
    }

    public function show(int $id)
    {
        $match = MatchModel::query()
            ->with(['server', 'streams'])
            ->whereNull('deleted_at')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'match' => [
                'id' => $match->id,
                'title' => $match->title,
                'league' => $match->league,
                'team_home' => $match->team_home,
                'team_away' => $match->team_away,
                'match_datetime' => optional($match->match_datetime)->toIso8601String(),
                'country' => $match->country,
                'server_id' => $match->server_id,
                'slug' => $match->slug,
                'fingerprint' => $match->fingerprint,
                'status' => $match->computed_status,
                'is_featured' => $match->is_featured,
                'created_at' => optional($match->created_at)->toIso8601String(),
                'updated_at' => optional($match->updated_at)->toIso8601String(),
            ],
            'streams' => $match->streams->map(fn ($stream) => [
                'channel_name' => $stream->channel_name,
                'iframe_url' => $stream->iframe_url,
                'stream_type' => $stream->stream_type,
            ])->values(),
        ]);
    }
}
