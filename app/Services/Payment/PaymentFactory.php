<?php

namespace App\Services\Payment;

use App\Models\Payment\Gateway;
use Exception;

class PaymentFactory
{
    public static function create(): PaymentGatewayInterface
    {
        $gateway = Gateway::where('is_active', true)->first();

        if (!$gateway) {
            throw new Exception('No active payment gateway found.');
        }

        return self::createFromGateway($gateway);
    }

    public static function createFromProvider(string $provider): PaymentGatewayInterface
    {
        $gateway = Gateway::where('provider', $provider)->first();

        if (!$gateway) {
            throw new Exception("Payment gateway provider '{$provider}' not found.");
        }

        return self::createFromGateway($gateway);
    }

    protected static function createFromGateway(Gateway $gateway): PaymentGatewayInterface
    {
        switch ($gateway->provider) {
            case 'midtrans':
                return new MidtransService($gateway);
            default:
                throw new Exception("Unsupported payment provider: {$gateway->provider}");
        }
    }
}
