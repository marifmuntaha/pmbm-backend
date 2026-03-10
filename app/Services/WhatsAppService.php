<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $url;
    protected string $user;
    protected string $pass;
    protected string $key;

    public function __construct()
    {
        $this->url = config('whatsapp.url');
        $this->user = config('whatsapp.user');
        $this->pass = config('whatsapp.pass');
        $this->key = config('whatsapp.key', ''); // Ensure key is safe
    }

    /**
     * Standardize phone number formatting.
     *
     * @param string $phone
     * @return string
     */
    private function formatPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (!str_ends_with($cleaned, '@s.whatsapp.net')) {
            $cleaned .= '@s.whatsapp.net';
        }
        return $cleaned;
    }

    public function sendTyping(string $phone, string $action) : void
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withBasicAuth($this->user, $this->pass)
                ->post($this->url . "/send/chat-presence", [
                    'phone' => $this->formatPhone($phone),
                    'action' => $action,
                ]);
            if ($response->successful()) {
                $response->json();
                return;
            }
            Log::error('WhatsApp Service Error: ' . $response->body());
            return;
        } catch (Exception $e) {
            Log::error('WhatsApp Service Exception: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Send a text message via WhatsApp.
     *
     * @param string $phone Phone number (with country code, e.g., 628123456789)
     * @param string $message The message content
     * @return array|null
     */
    public function sendMessage(string $phone, string $message): ?array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withBasicAuth($this->user, $this->pass)
                ->post("$this->url/send/message", [
                    'phone' => $this->formatPhone($phone),
                    'message' => $message,
                    "is_forwarded" => false,
                    "duration" => 3600
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            $body = $response->json();
            Log::error('WhatsApp Service Error: ' . $response->body());
            return [
                'success' => false,
                'error' => $body['code'] ?? 'UNKNOWN_ERROR',
                'message' => $body['message'] ?? 'Unknown error'
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp Service Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send an image via WhatsApp.
     *
     * @param string $phone
     * @param string $imageUrl
     * @param string $caption
     * @return array|null
     */
    public function sendImage(string $phone, string $imageUrl, string $caption = ''): ?array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->key,
            ])->withBasicAuth($this->user, $this->pass)
                ->post("$this->url/send/image", [
                'phone' => $this->formatPhone($phone),
                'image' => $imageUrl,
                'caption' => $caption,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            $body = $response->json();
            Log::error('WhatsApp Service (Image) Error: ' . $response->body());
            return [
                'success' => false,
                'error' => $body['code'] ?? 'UNKNOWN_ERROR',
                'message' => $body['message'] ?? 'Unknown error'
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp Service (Image) Exception: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Send a document/file via WhatsApp.
     *
     * @param string $phone
     * @param string $filePath
     * @param string $caption
     * @return array|null
     */
    public function sendFile(string $phone, string $filePath, string $caption = ''): ?array
    {
        try {
            $response = Http::withBasicAuth($this->user, $this->pass)
                ->attach(
                    'file',
                    file_get_contents($filePath),
                    basename($filePath)
                )
                ->post("$this->url/send/file", [
                    'phone' => $this->formatPhone($phone),
                    'caption' => $caption,
                    'is_forwarded' => 'false',
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            $body = $response->json();
            Log::error('WhatsApp Service (File) Error: ' . $response->body());
            return [
                'success' => false,
                'error' => $body['code'] ?? 'UNKNOWN_ERROR',
                'message' => $body['message'] ?? 'Unknown error'
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp Service (File) Exception: ' . $e->getMessage());
            return null;
        }
    }
}
