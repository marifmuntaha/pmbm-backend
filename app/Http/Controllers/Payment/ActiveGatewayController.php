<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment\Gateway;

class ActiveGatewayController extends Controller
{
    /**
     * Get the currently active payment gateway (minimal info for public/student use)
     */
    public function index()
    {
        try {
            $gateway = Gateway::where('is_active', true)->first();

            if (!$gateway) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'No active payment gateway found.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'result' => [
                    'provider' => $gateway->provider,
                    'mode' => (int) $gateway->mode,
                    'client_key' => $gateway->client_key,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }
}
