<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public int $backoff = 30;

    protected string $phone;
    protected string $message;
    protected ?string $imageUrl;
    protected string $caption;

    protected ?string $filePath;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phone, string $message, ?string $imageUrl = null, string $caption = '', ?string $filePath = null)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
        $this->caption = $caption;
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle(WhatsAppService $whatsapp): void
    {
        // 1. Send typing start
        $whatsapp->sendTyping($this->phone, 'start');

        // 2. Random delay between 1-10 seconds
        $delay = rand(1, 10);
        Log::info("WhatsApp typing delay: {$delay}s for {$this->phone}");
        sleep($delay);

        // 3. Send typing stop
        $whatsapp->sendTyping($this->phone, 'stop');

        Log::info("Job SendWhatsAppMessage variables [phone: {$this->phone}] [hasFile: " . ($this->filePath ? 'YES' : 'NO') . "] [filePath: {$this->filePath}] [exists: " . ($this->filePath && file_exists($this->filePath) ? 'YES' : 'NO') . "]");

        // 4. Send the actual content
        if ($this->filePath) {
            $result = $whatsapp->sendFile($this->phone, $this->filePath, $this->caption ?: $this->message);
        } elseif ($this->imageUrl) {
            $result = $whatsapp->sendImage($this->phone, $this->imageUrl, $this->caption);
        } else {
            $result = $whatsapp->sendMessage($this->phone, $this->message);
        }

        if (!$result || !$result['success']) {
            $errorCode = $result['error'] ?? 'UNKNOWN_ERROR';
            $errorMessage = $result['message'] ?? 'Unknown error';

            // If the error is permanent (like INVALID_JID), do not retry
            if ($errorCode === 'INVALID_JID' || str_contains($errorMessage, 'is not on whatsapp')) {
                Log::warning("Permanent failure sending WhatsApp to $this->phone: $errorMessage. Job will not be retried.");
                return;
            }

            throw new Exception("Gagal mengirim pesan WhatsApp ke $this->phone. Error: $errorCode. Mencoba lagi...");
        }

        Log::info("WhatsApp message sent successfully to $this->phone");
    }
}
