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
            $data = $request->validated();

            // Handle is_active boolean conversion
            if (isset($data['is_active'])) {
                $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
                if ($data['is_active']) {
                    Gateway::where('id', '!=', $gateway->id)->update(['is_active' => false]);
                }
            }

            // Handle mode conversion (string to integer)
            if (isset($data['mode'])) {
                if ($data['mode'] === 'production') {
                    $data['mode'] = 2;
                } elseif ($data['mode'] === 'sandbox') {
                    $data['mode'] = 1;
                }
            }

            return $gateway->update($data)
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
