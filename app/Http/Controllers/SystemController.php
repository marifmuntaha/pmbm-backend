<?php

namespace App\Http\Controllers;

use App\Services\LogService;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class SystemController extends Controller
{
    /**
     * Mengeksekusi Auto Update Script.
     * Endpoint ini harus dilindungi middleware (hanya untuk Admin).
     */
    public function update(Request $request)
    {
        // Pengecekan keamanan tambahan (jika Role belum diblacklist di middleware)
        if ($request->user()->role !== 1 && $request->user()->role !== '1') {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Anda tidak memiliki akses untuk aksi ini.',
            ], 403);
        }

        try {
            // Path absolut untuk skrip bash setelah di-mount
            // Prioritaskan base_path (level di atas backend) atau path kontainer
            $scriptPath = base_path('../update.sh');
            if (!file_exists($scriptPath)) {
                $scriptPath = '/var/www/project/update.sh';
            }

            if (!file_exists($scriptPath)) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'File script update (update.sh) tidak ditemukan di root aplikasi.',
                ], 404);
            }

            LogService::log("System update initiated by " . $request->user()->name, 'info');

            // Eksekusi bash dengan Process (membutuhkan symfony/process)
            $process = Process::fromShellCommandline("sh {$scriptPath}");
            $process->setTimeout(600); // 10 menit batas waktu
            $process->run();

            if (!$process->isSuccessful()) {
                $errorMsg = $process->getErrorOutput() ?: $process->getOutput();
                LogService::error('System update failed', ['error' => $errorMsg]);
                throw new Exception($errorMsg);
            }

            $output = $process->getOutput();
            LogService::log('System update completed successfully', 'info', ['output' => substr($output, -1000)]);

            return response()->json([
                'status' => 'success',
                'statusMessage' => 'Sistem berhasil diperbarui.',
                'result' => [
                    'log' => $output
                ]
            ]);

        } catch (Exception $exception) {
            \Illuminate\Support\Facades\Log::error("System Auto-Update Error: " . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Gagal memperbarui sistem: ' . $exception->getMessage(),
            ], 500);
        }
    }
}
