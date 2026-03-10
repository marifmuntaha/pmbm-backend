<?php

namespace App\Services;

use App\Models\System\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class LogService
{
    public static function log(string $message, string $level = 'info', array $context = [])
    {
        try {
            Log::create([
                'userId' => Auth::id(),
                'level' => $level,
                'message' => $message,
                'context' => $context,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (Exception $e) {
            // Silently fail to prevent infinite loops or breaking user actions if logging fails
        }
    }

    public static function transaction(string $message, array $context = [])
    {
        self::log($message, 'transaction', $context);
    }

    public static function error(string $message, array $context = [])
    {
        self::log($message, 'error', $context);
    }

    public static function warning(string $message, array $context = [])
    {
        self::log($message, 'warning', $context);
    }
}
