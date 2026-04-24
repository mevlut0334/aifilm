<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\PackageRepository;
use App\Services\PaddleService;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        private PackageRepository $packageRepository,
        private PaddleService $paddleService
    ) {}

    public function index(): View
    {
        $packages = $this->packageRepository->getAll(true)->where('is_active', true);

        // Paddle price ID'lerini topla
        $priceIds = $packages
            ->whereNotNull('paddle_price_id')
            ->pluck('paddle_price_id')
            ->filter()
            ->values()
            ->toArray();

        // Paddle'dan fiyatları çek
        $paddlePrices = [];
        if (! empty($priceIds)) {
            try {
                $paddlePrices = $this->paddleService->fetchPaddlePrices($priceIds);
            } catch (\Exception $e) {
                Log::warning('Paddle fiyatları çekilemedi: '.$e->getMessage());
            }
        }

        // Her pakete paddle_price ve paddle_currency ekle
        $packages->each(function ($package) use ($paddlePrices) {
            $pid = $package->paddle_price_id;

            // Güvenli array erişimi
            if (isset($paddlePrices[$pid]) && is_array($paddlePrices[$pid])) {
                $package->paddle_price = $paddlePrices[$pid]['amount'] ?? null;
                $package->paddle_currency = $paddlePrices[$pid]['currency'] ?? 'USD';
            } else {
                $package->paddle_price = null;
                $package->paddle_currency = 'USD';
            }
        });

        return view('web.packages.index', [
            'packages' => $packages,
        ]);
    }
}
