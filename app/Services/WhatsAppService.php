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
    protected string $deviceId;

    public function __construct()
    {
        $this->url = config('whatsapp.url');
        $this->user = config('whatsapp.user');
        $this->pass = config('whatsapp.pass');
        $this->deviceId = config('whatsapp.device_id', '');
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

    private function getHeaders(string $device): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($device != "") {
            $headers['X-Device-Id'] = $device;
        }

        return $headers;
    }

    public function deviceAdd(string $device)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($device))
                ->withBasicAuth($this->user, $this->pass)
                ->post("$this->url/devices", [
                    'device_id' => $device,
                ]);
            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('WhatsApp Service Error: ' . $response->body());
                return $response->body();
            }

        } catch (Exception $e) {
            Log::error('WhatsApp Service Exception: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function deviceInfo(string $device)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($device))
                ->withBasicAuth($this->user, $this->pass)
                ->get("$this->url/devices/{$device}/status");
            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('WhatsApp Service Error: ' . $response->body());
                return $response->body();
            }

        } catch (Exception $e) {
            Log::error('WhatsApp Service Exception: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function deviceRemove(string $device)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($device))
                ->withBasicAuth($this->user, $this->pass)
                ->delete("$this->url/devices/{$device}");
            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('WhatsApp Service Error: ' . $response->body());
                return $response->body();
            }

        } catch (Exception $e) {
            Log::error('WhatsApp Service Exception: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function deviceLogin(string $device)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($device))
                ->withBasicAuth($this->user, $this->pass)
                ->get("$this->url/app/login");
            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('WhatsApp Service Error: ' . $response->body());
                return $response->body();
            }

        } catch (Exception $e) {
            Log::error('WhatsApp Service Exception: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function deviceLoginWithCode(string $device, string $code)
    {

    }

    public function sendTyping(string $phone, string $action): void
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withBasicAuth($this->user, $this->pass)
                ->post("$this->url/send/chat-presence", [
                    'phone' => $this->formatPhone($phone),
                    'action' => $action,
                ]);
            if ($response->successful()) {
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
            $response = Http::withHeaders($this->getHeaders())
                ->withBasicAuth($this->user, $this->pass)
                ->post("$this->url/send/message", [
                    'phone' => $this->formatPhone($phone),
                    'message' => $message,
                    'is_forwarded' => false,
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
            $response = Http::withHeaders($this->getMultipartHeaders())
                ->withBasicAuth($this->user, $this->pass)
                ->asMultipart()
                ->post("$this->url/send/image", [
                    ['name' => 'phone', 'contents' => $this->formatPhone($phone)],
                    ['name' => 'caption', 'contents' => $caption],
                    ['name' => 'image_url', 'contents' => $imageUrl],
                    ['name' => 'view_once', 'contents' => 'false'],
                    ['name' => 'compress', 'contents' => 'false'],
                    ['name' => 'is_forwarded', 'contents' => 'false'],
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
            $response = Http::withHeaders($this->getMultipartHeaders())
                ->withBasicAuth($this->user, $this->pass)
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
