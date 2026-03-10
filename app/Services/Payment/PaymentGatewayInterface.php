<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a transaction.
     *
     * @param array $params
     * @return array
     */
    public function createTransaction(array $params): array;

    /**
     * Handle the callback notification.
     *
     * @param array $requestData
     * @return array
     */
    public function handleCallback(array $requestData): array;

    /**
     * Get the redirect URL from the given token.
     *
     * @param string $token
     * @return string
     */
    public function getRedirectUrl(string $token): string;
}
