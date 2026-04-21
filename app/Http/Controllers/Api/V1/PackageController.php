<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\PackageRepository;
use App\Services\PaddleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PackageController extends Controller
{
    public function __construct(
        private PackageRepository $packageRepository,
        private PaddleService $paddleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Only web packages are managed in backend
        $packages = $this->packageRepository->getAll(true);

        // Include Paddle price details
        $packages = $packages->map(function ($package) {
            if ($package->paddle_price_id) {
                $priceDetails = Cache::remember(
                    "paddle_price_{$package->paddle_price_id}",
                    3600,
                    fn () => $this->paddleService->getPriceDetails($package->paddle_price_id)
                );
                $package->price_details = $priceDetails;
            }

            return $package;
        });

        return response()->json([
            'success' => true,
            'data' => $packages->map(function ($package) {
                return [
                    'id' => $package->id,
                    'title' => $package->getTitle(),
                    'description' => $package->getDescription(),
                    'token_amount' => $package->token_amount,
                    'paddle_price_id' => $package->paddle_price_id,
                    'price_details' => $package->price_details ?? null,
                ];
            }),
        ]);
    }
}
