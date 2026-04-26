<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $appKey    = $request->header('X-App-Key');
        $secretKey = $request->header('X-Secret-Key');

        $validAppKey    = config('app.api_app_key');
        $validSecretKey = config('app.api_secret_key');

        if (
            empty($appKey) ||
            empty($secretKey) ||
            !hash_equals($validAppKey, $appKey) ||
            !hash_equals($validSecretKey, $secretKey)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data'    => null,
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
