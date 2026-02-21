<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\Server;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MatchAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = MatchModel::query()
            ->withTrashed()
            ->with('server')
            ->withCount('streams')
            ->orderByDesc('match_datetime');

        if ($request->filled('server_id')) {
            $query->where('server_id', (int) $request->integer('server_id'));
        }

        if ($request->filled('league')) {
            $query->where('league', $request->string('league')->toString());
        }

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where('title', 'like', "%{$term}%");
        }

        if ($request->filled('date')) {
            $query->whereDate('match_datetime', $request->string('date')->toString());
        }

        return view('admin.matches.index', [
            'matches' => $query->paginate(20)->withQueryString(),
            'servers' => Server::query()->orderBy('id')->get(),
            'leagues' => MatchModel::query()->whereNotNull('league')->distinct()->orderBy('league')->pluck('league'),
            'filters' => $request->only(['server_id', 'league', 'search', 'date']),
        ]);
    }

    public function create(): View
    {
        return view('admin.matches.create', [
            'servers' => Server::query()->where('is_active', true)->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedMatchPayload($request);
        $dateTime = Carbon::createFromFormat('Y-m-d H:i', $payload['match_date'].' '.$payload['match_time']);
        $fingerprint = md5(strtolower(trim($payload['title'])).$dateTime->format('Y-m-d'));
        $slug = $this->uniqueSlug(Str::slug($payload['title'].' '.$dateTime->format('Y-m-d')));

        $match = MatchModel::query()->create([
            'title' => $payload['title'],
            'league' => $payload['league'],
            'team_home' => $payload['team_home'],
            'team_away' => $payload['team_away'],
            'match_datetime' => $dateTime,
            'country' => $payload['country'],
            'server_id' => (int) $payload['server_id'],
            'slug' => $slug,
            'fingerprint' => $this->uniqueFingerprint($fingerprint),
            'status' => $this->statusFromDateTime($dateTime),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        $this->syncStreams($match, $payload['streams'] ?? []);

        return redirect()
            ->route('admin.matches')
            ->with('success', 'Match created successfully.');
    }

    public function edit(int $id): View
    {
        $match = MatchModel::query()
            ->withTrashed()
            ->with('streams')
            ->findOrFail($id);

        return view('admin.matches.edit', [
            'match' => $match,
            'servers' => Server::query()->where('is_active', true)->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $match = MatchModel::query()->withTrashed()->with('streams')->findOrFail($id);
        $payload = $this->validatedMatchPayload($request);
        $dateTime = Carbon::createFromFormat('Y-m-d H:i', $payload['match_date'].' '.$payload['match_time']);
        $fingerprint = md5(strtolower(trim($payload['title'])).$dateTime->format('Y-m-d'));

        $slugBase = Str::slug($payload['title'].' '.$dateTime->format('Y-m-d'));
        $slug = $this->uniqueSlug($slugBase, $match->id);
        $fingerprint = $this->uniqueFingerprint($fingerprint, $match->id);

        $match->update([
            'title' => $payload['title'],
            'league' => $payload['league'],
            'team_home' => $payload['team_home'],
            'team_away' => $payload['team_away'],
            'match_datetime' => $dateTime,
            'country' => $payload['country'],
            'server_id' => (int) $payload['server_id'],
            'slug' => $slug,
            'fingerprint' => $fingerprint,
            'status' => $this->statusFromDateTime($dateTime),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        $match->streams()->delete();
        $this->syncStreams($match, $payload['streams'] ?? []);

        return redirect()
            ->route('admin.matches')
            ->with('success', 'Match updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $match = MatchModel::query()->findOrFail($id);
        $match->delete();

        return redirect()
            ->route('admin.matches')
            ->with('success', 'Match deleted (soft delete) successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $match = MatchModel::query()->withTrashed()->findOrFail($id);
        $match->restore();

        return redirect()
            ->route('admin.matches')
            ->with('success', 'Match restored successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedMatchPayload(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'league' => ['nullable', 'string', 'max:255'],
            'team_home' => ['nullable', 'string', 'max:255'],
            'team_away' => ['nullable', 'string', 'max:255'],
            'match_date' => ['required', 'date'],
            'match_time' => ['required', 'date_format:H:i'],
            'country' => ['nullable', 'string', 'max:100'],
            'server_id' => ['required', 'integer', 'exists:servers,id'],
            'streams' => ['required', 'array', 'min:1'],
            'streams.*.channel_name' => ['nullable', 'string', 'max:255'],
            'streams.*.iframe_url' => ['required', 'url', 'max:2000'],
            'streams.*.stream_type' => ['required', 'in:iframe,m3u8'],
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $streams
     */
    protected function syncStreams(MatchModel $match, array $streams): void
    {
        foreach ($streams as $index => $stream) {
            $url = trim((string) ($stream['iframe_url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $match->streams()->create([
                'channel_name' => $stream['channel_name'] ?? 'Channel '.($index + 1),
                'iframe_url' => $url,
                'stream_type' => $stream['stream_type'] ?? (Str::contains(strtolower($url), '.m3u8') ? 'm3u8' : 'iframe'),
                'sort_order' => $index,
            ]);
        }
    }

    protected function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : 'match-'.Str::random(8);
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return MatchModel::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    protected function uniqueFingerprint(string $fingerprint, ?int $ignoreId = null): string
    {
        $candidate = $fingerprint;
        $counter = 1;

        while (
            MatchModel::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('fingerprint', $candidate)
                ->exists()
        ) {
            $candidate = md5($fingerprint.$counter);
            $counter++;
        }

        return $candidate;
    }

    protected function statusFromDateTime(Carbon $start): string
    {
        if (now()->between($start, $start->copy()->addHours(2))) {
            return 'live';
        }

        return now()->lt($start) ? 'upcoming' : 'finished';
    }
}
