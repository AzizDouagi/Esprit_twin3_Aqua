<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('SELECT 1');

            return response()->json(['status' => 'ok', 'database' => 'ok']);
        } catch (Throwable $e) {
            Log::warning('health.database_failed', ['exception' => $e::class]);

            return response()->json(['status' => 'unavailable', 'database' => 'unavailable'], 503);
        }
    }
}
