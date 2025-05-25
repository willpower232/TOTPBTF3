<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;

class SessionsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
        ];
    }

    public function setLightMode(): JsonResponse
    {
        $user = user();

        $user->light_mode = request()->boolean('light_mode');
        $user->save();

        return response()->json([
            'current_state' => $user->light_mode,
        ], JsonResponse::HTTP_OK);
    }
}
