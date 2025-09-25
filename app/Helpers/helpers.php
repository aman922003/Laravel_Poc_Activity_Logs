<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

if (!function_exists('handleTransaction')) {
    function handleTransaction(callable $callback) {
        try {
            DB::beginTransaction();

            $result = $callback();

            DB::commit();

            return response()->json($result, $result['status'] ?? 200);

        } catch (\Throwable $t) {
            DB::rollBack();

            Log::error("Exception: " . $t->getMessage(), [
                'trace' => $t->getTraceAsString()
            ]);

            return response()->json([
                'error'   => 'Something went wrong',
                'message' => $t->getMessage()
            ], 500);
        }
    }
}
