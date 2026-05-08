<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\MobilePackageRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePackageApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private MobilePackageRepository $mobilePackageRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'nullable|string|in:ios,android',
        ]);

        $platform = $request->input('platform');

        $packages = match ($platform) {
            'ios'     => $this->mobilePackageRepository->getActiveForIos(),
            'android' => $this->mobilePackageRepository->getActiveForAndroid(),
            default   => $this->mobilePackageRepository->getAll(activeOnly: true),
        };

        $data = $packages->map(fn ($package) => [
            'id'                 => $package->id,
            'title'              => $package->getTitle(),
            'description'        => $package->getDescription(),
            'token_amount'       => $package->token_amount,
            'ios_product_id'     => $package->ios_product_id,
            'android_product_id' => $package->android_product_id,
            'order'              => $package->order,
        ]);

        return $this->successResponse(data: $data);
    }
}
