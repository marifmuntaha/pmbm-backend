<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\UpdateGatewayRequest;
use App\Models\Payment\Gateway;
use Exception;

class GatewayController extends Controller
{
    public function index()
    {
        try {
            $gateways = new Gateway();
            return response()->json([
                'status' => 'success',
                'messageStatus' => '',
                'result' => $gateways->get()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'messageStatus' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateGatewayRequest $request, Gateway $gateway)
    {
        try {
            return $gateway->update(array_filter($request->all))
            ? response([
                'status' => 'success',
                'messageStatus' => '',
                'result' => $gateway
            ])
            : response([
                'status' => 'error',
                'messageStatus' => 'Gagal update Gateway pembayaran',
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'messageStatus' => $e->getMessage()
            ], 500);
        }
    }
}
