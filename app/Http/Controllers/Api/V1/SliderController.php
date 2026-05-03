<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SliderResource;
use App\Models\Slider;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SliderController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $sliders = Slider::active()->ordered()->get();

        return $this->successResponse(
            SliderResource::collection($sliders)
        );
    }
}
