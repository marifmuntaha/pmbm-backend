<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use App\Models\Payment\Gateway;
use Exception;

class MidtransService implements PaymentGatewayInterface
{
    protected $serverKey;
    protected $isProduction;

    public function __construct(Gateway $gateway)
    {
        $this->serverKey = $gateway->server_key;
        $this->isProduction = (int) $gateway->mode === 2;
    }

    public function getRedirectUrl(string $token): string
    {
        return $this->isProduction
            ? "https://app.midtrans.com/snap/v3/redirection/{$token}"
            : "https://app.sandbox.midtrans.com/snap/v3/redirection/{$token}";
    }

    public function createTransaction(array $params): array
    {
        $url = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $response = Http::withBasicAuth($this->serverKey, '')
            ->post($url, $params);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Midtrans Error: ' . $response->body());
    }

    public function handleCallback(array $requestData): array
    {
        $signatureKey = $requestData['signature_key'] ?? '';
        $orderId = $requestData['order_id'] ?? '';
        $statusCode = $requestData['status_code'] ?? '';
        $grossAmount = $requestData['gross_amount'] ?? '';

        $hashed = hash("sha512", $orderId . $statusCode . $grossAmount . $this->serverKey);

        if ($hashed !== $signatureKey) {
             throw new Exception('Invalid signature');
        }

        return [
            'transaction_status' => $requestData['transaction_status'],
            'fraud_status' => $requestData['fraud_status'] ?? null,
            'order_id' => $orderId,
            'transaction_id' => $requestData['transaction_id'] ?? null,
            'transaction_time' => $requestData['transaction_time'] ?? null,
            'amount' => $grossAmount,
        ];
    }
}
