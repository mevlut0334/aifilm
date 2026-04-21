<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\PackageRepository;
use App\Services\PaddleService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        private PackageRepository $packageRepository,
        private PaddleService $paddleService
    ) {}

    public function index(): View
    {
        $packages = $this->packageRepository->getAll(true);

        // Enrich packages with Paddle price details (cached for 60 minutes)
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

        return view('web.packages.index', [
            'packages' => $packages,
        ]);
    }
}
