<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PaddleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaddleWebhookController extends Controller
{
    public function __construct(
        private PaddleService $paddleService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            Log::info('Paddle webhook received', ['data' => $data]);

            $this->paddleService->handleWebhook($data);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Paddle webhook failed: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
