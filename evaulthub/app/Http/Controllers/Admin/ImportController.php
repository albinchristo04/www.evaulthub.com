<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\Server;
use App\Services\ImportService;
use App\Services\MatchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(
        protected MatchService $matchService,
        protected ImportService $importService
    ) {
    }

    public function index(): View
    {
        return view('admin.import.index', [
            'servers' => Server::query()->where('is_active', true)->orderBy('id')->get(),
        ]);
    }

    public function fetch(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'server_id' => ['required', 'integer', 'exists:servers,id'],
        ]);

        $server = Server::query()->findOrFail((int) $validated['server_id']);
        $matches = $this->matchService->fetchAndParse($server);

        if ($matches === []) {
            return redirect()
                ->route('admin.import')
                ->with('error', 'No matches fetched from selected server.');
        }

        $fingerprints = collect($matches)
            ->map(fn (array $item) => $this->importService->buildFingerprint((string) ($item['title'] ?? ''), (string) ($item['date_diary'] ?? '')))
            ->filter()
            ->values();

        $existingFingerprints = MatchModel::query()
            ->whereIn('fingerprint', $fingerprints)
            ->pluck('fingerprint')
            ->flip();

        $previewItems = collect($matches)->values()->map(function (array $match, int $index) use ($existingFingerprints) {
            $fingerprint = $this->importService->buildFingerprint((string) ($match['title'] ?? ''), (string) ($match['date_diary'] ?? ''));

            return [
                'index' => $index,
                'title' => $match['title'] ?? '',
                'league' => $match['league'] ?? null,
                'match_datetime' => $match['match_datetime'] ?? null,
                'streams_count' => is_array($match['streams'] ?? null) ? count($match['streams']) : 0,
                'fingerprint' => $fingerprint,
                'is_existing' => $existingFingerprints->has($fingerprint),
                'payload' => $match,
            ];
        })->all();

        $request->session()->put('admin_import_preview', [
            'server_id' => $server->id,
            'items' => $previewItems,
        ]);

        return view('admin.import.preview', [
            'server' => $server,
            'previewItems' => $previewItems,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_id' => ['required', 'integer', 'exists:servers,id'],
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer'],
        ]);

        $sessionPayload = $request->session()->get('admin_import_preview', []);
        if (($sessionPayload['server_id'] ?? null) !== (int) $validated['server_id']) {
            return redirect()
                ->route('admin.import')
                ->with('error', 'Import session expired. Please fetch matches again.');
        }

        $server = Server::query()->findOrFail((int) $validated['server_id']);
        $itemMap = collect($sessionPayload['items'] ?? [])->keyBy('index');

        $selectedItems = collect($validated['selected'])
            ->map(fn ($index) => $itemMap->get((int) $index))
            ->filter()
            ->map(fn (array $item) => $item['payload'])
            ->values()
            ->all();

        if ($selectedItems === []) {
            return redirect()
                ->route('admin.import')
                ->with('error', 'No valid matches selected for import.');
        }

        $result = $this->importService->importMatches($server, $selectedItems);

        return redirect()
            ->route('admin.import')
            ->with('success', "{$result['imported']} matches imported, {$result['skipped']} skipped (already exist).");
    }
}
