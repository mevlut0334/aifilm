<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GenerationRequest;
use App\Services\GenerationRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GenerationRequestController extends Controller
{
    public function __construct(
        private GenerationRequestService $generationRequestService
    ) {}

    public function index(): View
    {
        // custom_image tipindeki talepler custom-images sayfasında gösterildiği için burada hariç tutuyoruz
        $requests = GenerationRequest::with(['user', 'template'])
            ->whereNotIn('type', ['custom_image'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.generation-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function show(string $uuid): View
    {
        $request = $this->generationRequestService->getRequestByUuid($uuid);
        abort_if(! $request, 404);

        return view('admin.generation-requests.show', [
            'request' => $request,
        ]);
    }

    public function updateProgress(Request $request, string $uuid): RedirectResponse
    {
        $generationRequest = $this->generationRequestService->getRequestByUuid($uuid);
        abort_if(! $generationRequest, 404);

        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $this->generationRequestService->updateRequest($generationRequest, [
            'progress' => $validated['progress'],
        ]);

        return back()->with('success', 'Progress güncellendi.');
    }

    public function updateStatus(Request $request, string $uuid): RedirectResponse
    {
        $generationRequest = $this->generationRequestService->getRequestByUuid($uuid);
        abort_if(! $generationRequest, 404);

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,failed',
            'output_url' => 'nullable|url',
            'failure_reason' => 'nullable|string',
        ]);

        if ($validated['status'] === 'completed' && ! empty($validated['output_url'])) {
            $this->generationRequestService->markAsCompleted(
                $generationRequest,
                $validated['output_url']
            );
        } elseif ($validated['status'] === 'failed' && ! empty($validated['failure_reason'])) {
            $this->generationRequestService->markAsFailed(
                $generationRequest,
                $validated['failure_reason']
            );
        } elseif ($validated['status'] === 'processing') {
            $this->generationRequestService->markAsProcessing($generationRequest);
        } else {
            $this->generationRequestService->updateRequest($generationRequest, [
                'status' => $validated['status'],
            ]);
        }

        return back()->with('success', 'Durum güncellendi.');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $generationRequest = $this->generationRequestService->getRequestByUuid($uuid);
        abort_if(! $generationRequest, 404);

        try {
            $this->generationRequestService->deleteRequest($generationRequest);

            return redirect()->route('admin.generation-requests.index')->with('success', 'Talep başarıyla silindi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
