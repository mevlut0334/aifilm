<?php

namespace App\Http\Controllers\Web\Studio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Studio\StoreStudioRequest;
use App\Models\Studio\StudioRequest as StudioRequestModel;
use App\Models\Studio\StudioRevision;
use App\Services\TelegramNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class StudioController extends Controller
{
    /**
     * Müşterinin Studio taleplerini listeler.
     */
    public function index(): View
    {
        $requests = StudioRequestModel::where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('studio.index', compact('requests'));
    }

    /**
     * Yeni talep formunu açar.
     */
    public function create(): View
    {
        $voices = config('studio_voices', []);

        return view('studio.create', compact('voices'));
    }

    /**
     * Yeni Studio talebini kaydeder.
     */
    public function store(StoreStudioRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $characters = [];
        $environments = [];

        // Hata durumunda silinecek dosyaları takip eder.
        $storedFiles = [];

        try {
            $studioRequest = DB::transaction(function () use (
                $request,
                $validated,
                &$characters,
                &$environments,
                &$storedFiles
            ) {
                // Karakter bilgileri ve dosyaları
                foreach ($request->input('characters', []) as $index => $character) {
                    $characterData = [
                        'name' => $character['name'] ?? null,
                        'voice_key' => $character['voice_key'] ?? null,
                        'image_path' => null,
                        'custom_voice_path' => null,
                    ];

                    if ($request->hasFile("characters.{$index}.image")) {
                        $path = $request
                            ->file("characters.{$index}.image")
                            ->store('studio/characters', 'public');

                        $characterData['image_path'] = $path;

                        $storedFiles[] = [
                            'disk' => 'public',
                            'path' => $path,
                        ];
                    }

                    if ($request->hasFile("characters.{$index}.custom_voice")) {
                        $path = $request
                            ->file("characters.{$index}.custom_voice")
                            ->store('studio/custom-voices', 'local');

                        $characterData['custom_voice_path'] = $path;

                        $storedFiles[] = [
                            'disk' => 'local',
                            'path' => $path,
                        ];
                    }

                    $characters[] = $characterData;
                }

                // Ortam bilgileri ve görselleri
                foreach ($request->input('environments', []) as $index => $environment) {
                    $environmentData = [
                        'name' => $environment['name'] ?? null,
                        'image_path' => null,
                    ];

                    if ($request->hasFile("environments.{$index}.image")) {
                        $path = $request
                            ->file("environments.{$index}.image")
                            ->store('studio/environments', 'public');

                        $environmentData['image_path'] = $path;

                        $storedFiles[] = [
                            'disk' => 'public',
                            'path' => $path,
                        ];
                    }

                    $environments[] = $environmentData;
                }

                return StudioRequestModel::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => auth()->id(),
                    'scenario' => $validated['scenario'] ?? null,
                    'format' => $validated['format'],
                    'characters' => $characters,
                    'environments' => $environments,
                    'initial_revision_video_url' =>
                        $validated['initial_revision_video_url'] ?? null,
                    'initial_revision_description' =>
                        $validated['initial_revision_description'] ?? null,
                    'status' => 'processing',
                ]);
            });

            // Yeni talep başarıyla kaydedildiğinde Telegram bildirimi gönder.
            app(TelegramNotificationService::class)->send(
                '🎬 Yeni video talebi oluşturuldu.'
            );

            return redirect()
                ->route('studio.show', $studioRequest->uuid)
                ->with('success', 'Studio video talebin başarıyla oluşturuldu.');

        } catch (Throwable $exception) {
            // Veritabanı işlemi başarısızsa yüklenen dosyaları da temizle.
            foreach ($storedFiles as $file) {
                Storage::disk($file['disk'])->delete($file['path']);
            }

            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Talep kaydedilirken bir hata oluştu. Lütfen tekrar dene.');
        }
    }

    /**
     * Müşterinin kendi talep detayını gösterir.
     */
    public function show(string $uuid): View
    {
        $studioRequest = StudioRequestModel::with([
            'revisions' => fn ($query) => $query->latest(),
        ])
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('studio.show', compact('studioRequest'));
    }

    /**
     * Teslim edilmiş video için yeni revize talebi oluşturur.
     */
    public function storeRevision(Request $request, string $uuid): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:10000'],
            'reference_video_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $studioRequest = StudioRequestModel::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (empty($studioRequest->video_url)) {
            return back()->with(
                'error',
                'Henüz teslim edilmiş bir video olmadığı için revize talebi oluşturamazsın.'
            );
        }

        DB::transaction(function () use ($studioRequest, $validated) {
            StudioRevision::create([
                'studio_request_id' => $studioRequest->id,
                'description' => $validated['description'],
                'reference_video_url' =>
                    $validated['reference_video_url'] ?? null,
                'status' => 'processing',
            ]);

            $studioRequest->update([
                'status' => 'processing',
            ]);
        });

        // Revize başarıyla kaydedildiğinde Telegram bildirimi gönder.
        app(TelegramNotificationService::class)->send(
            '🔄 Yeni revize talebi oluşturuldu.'
        );

        return redirect()
            ->route('studio.show', $studioRequest->uuid)
            ->with('success', 'Revize talebin başarıyla oluşturuldu.');
    }
}
