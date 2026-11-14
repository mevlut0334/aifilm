<?php

namespace App\Http\Controllers\Admin\Studio;

use App\Http\Controllers\Controller;
use App\Models\Studio\StudioRequest;
use App\Models\Studio\StudioRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudioController extends Controller
{
    public function index(): View
    {
        $requests = StudioRequest::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.studio.index', compact('requests'));
    }

    public function show(string $uuid): View
    {
        $studioRequest = StudioRequest::with([
            'user',
            'revisions' => fn ($query) => $query->latest(),
        ])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('admin.studio.show', compact('studioRequest'));
    }

    public function updateVideo(Request $request, string $uuid): RedirectResponse
    {
        $validated = $request->validate([
            'video_url' => ['required', 'url', 'max:2048'],
        ]);

        $studioRequest = StudioRequest::where('uuid', $uuid)->firstOrFail();

        $studioRequest->update([
            'video_url' => $validated['video_url'],
            'video_updated_at' => now(),
            'status' => 'completed',
        ]);

        return redirect()
            ->route('admin.studio.show', $studioRequest->uuid)
            ->with('success', 'Video bağlantısı başarıyla güncellendi.');
    }

    public function completeRevision(Request $request, StudioRevision $revision): RedirectResponse
    {
        $validated = $request->validate([
            'video_url' => ['required', 'url', 'max:2048'],
        ]);

        DB::transaction(function () use ($revision, $validated) {
            $studioRequest = StudioRequest::findOrFail(
                $revision->studio_request_id
            );

            $studioRequest->update([
                'video_url' => $validated['video_url'],
                'video_updated_at' => now(),
                'status' => 'completed',
            ]);

            $revision->update([
                'status' => 'completed',
            ]);
        });

        return redirect()
            ->route('admin.studio.show', $revision->studioRequest->uuid)
            ->with('success', 'Revize tamamlandı ve video güncellendi.');
    }

    /**
     * Karakterin yüklediği özel ses dosyasını admin için indirir.
     */
    public function downloadCustomVoice(string $uuid, int $characterIndex)
    {
        $studioRequest = StudioRequest::where('uuid', $uuid)
            ->firstOrFail();

        $characters = $studioRequest->characters ?? [];

        $path = $characters[$characterIndex]['custom_voice_path'] ?? null;

        abort_unless(is_string($path) && $path !== '', 404);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $fileName = 'karakter-' . ($characterIndex + 1) . '-ses.' . $extension;

        return response()->download($disk->path($path), $fileName);
    }
}
