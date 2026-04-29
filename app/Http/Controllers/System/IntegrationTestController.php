<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Invoice;
use App\Models\Payment\Gateway;
use App\Services\CertificateService;
use App\Services\LogService;
use App\Services\Payment\MidtransService;
use App\Services\Payment\PaymentFactory;
use App\Services\PdfService;
use App\Services\TcpdfService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IntegrationTestController extends Controller
{
    protected $whatsapp;
    protected $pdf;
    protected $certificate;
    protected $tcpdf;

    public function __construct(
        WhatsAppService $whatsapp, 
        PdfService $pdf,
        CertificateService $certificate,
        TcpdfService $tcpdf
    ) {
        $this->whatsapp = $whatsapp;
        $this->pdf = $pdf;
        $this->certificate = $certificate;
        $this->tcpdf = $tcpdf;
    }

    public function testWhatsAppMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $message = "Halo! Ini adalah pesan testing dari sistem PPDB. Kode unik Anda: " . Str::upper(Str::random(6));
        $result = $this->whatsapp->sendMessage($request->phone, $message);

        if ($result) {
            LogService::log("Test WhatsApp Message dikirim ke {$request->phone}", 'info', ['phone' => $request->phone]);
            return response()->json([
                'status' => 'success',
                'statusMessage' => 'Pesan WhatsApp berhasil dikirim.',
                'result' => $result
            ]);
        }

        return response()->json([
            'status' => 'error',
            'statusMessage' => 'Gagal mengirim pesan WhatsApp.'
        ], 500);
    }

    public function testWhatsAppPdf(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $randomId = Str::random(10);
        $html = "<h1>Testing PDF PPDB</h1><p>ID Transaksi: {$randomId}</p><p>Waktu: " . now() . "</p><p>Ini adalah file PDF testing yang dihasilkan secara otomatis.</p>";
        $filename = "test-document-{$randomId}.pdf";
        $path = "temp/{$filename}";

        try {
            // Ensure temp directory exists
            Storage::makeDirectory('temp');

            $this->pdf->generateAndSave($html, $path);
            $fullPath = Storage::path($path);

            if (!file_exists($fullPath)) {
                throw new \Exception("File PDF tidak ditemukan di storage: {$fullPath}");
            }

            $result = $this->whatsapp->sendFile($request->phone, $fullPath, "Berikut adalah file PDF testing Anda.");

            // Cleanup
            Storage::delete($path);

            if ($result) {
                LogService::log("Test WhatsApp PDF dikirim ke {$request->phone}", 'info', ['phone' => $request->phone]);
                return response()->json([
                    'status' => 'success',
                    'statusMessage' => 'File PDF WhatsApp berhasil dikirim.',
                    'result' => $result
                ]);
            }

            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Gagal mengirim file PDF WhatsApp.'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testPdfSignature(Request $request)
    {
        $institution = Institution::first();
        if (!$institution) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Belum ada data lembaga untuk testing sertifikat.'
            ], 404);
        }

        $randomId = Str::random(10);
        $html = "<h1>Testing PDF Signature PPDB</h1><p>ID Transaksi: {$randomId}</p><p>Lembaga: {$institution->name}</p><p>Waktu: " . now() . "</p>";
        $pdfFilename = "test-to-sign-{$randomId}.pdf";
        $pdfPath = "temp/{$pdfFilename}";

        try {
            Storage::makeDirectory('temp');
            
            // 1. Generate base PDF
            $this->pdf->generateAndSave($html, $pdfPath);
            $fullPdfPath = Storage::path($pdfPath);

            // 2. Get/Generate Certificate
            $certPath = $this->certificate->getCertificateForInstitution($institution);
            $certPassword = $this->certificate->getCertificatePassword();

            // 3. Sign PDF
            $signedFilename = "signed-test-{$randomId}.pdf";
            $response = $this->tcpdf->signExistingPdf(
                $fullPdfPath,
                $signedFilename,
                $certPath,
                $certPassword,
                [
                    'author' => 'Bendahara Lembaga',
                    'title' => 'Testing Signed PDF',
                    'name' => 'Testing Admin',
                    'reason' => 'Testing Digital Signature'
                ]
            );

            // Cleanup
            Storage::delete($pdfPath);

            LogService::log("Test PDF Signature berhasil dilakukan untuk lembaga: {$institution->name}", 'info', ['institution' => $institution->name]);

            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Gagal mengetes tanda tangan PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testMidtrans(Request $request)
    {
        $gateway = Gateway::where('provider', 'midtrans')->where('is_active', true)->first();

        if (!$gateway) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Gateway Midtrans aktif tidak ditemukan.'
            ], 404);
        }

        try {
            $midtrans = new MidtransService($gateway);

            // Jika invoice_reference diberikan, gunakan agar order_id bisa di-trace saat test callback.
            // Format: {reference}-TEST-{timestamp} sehingga callback parser dapat memotong suffix dengan benar.
            $invoiceReference = $request->input('invoice_reference');
            if ($invoiceReference) {
                $invoice = Invoice::whereReference($invoiceReference)->first();
                if (!$invoice) {
                    return response()->json([
                        'status' => 'error',
                        'statusMessage' => "Invoice dengan reference '{$invoiceReference}' tidak ditemukan."
                    ], 404);
                }
                $grossAmount = (int)$invoice->amount + 3500;
                $orderId = $invoiceReference . '-TEST-' . time();
            } else {
                // Fallback: order_id dummy, TIDAK bisa dipakai untuk test callback
                $grossAmount = 10000;
                $orderId = 'TEST-DUMMY-' . time();
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => 'Testing',
                    'email' => 'test@example.com',
                ],
            ];

            $result = $midtrans->createTransaction($params);

            LogService::log("Test Midtrans Generate Transaction berhasil: {$orderId}", 'info', ['order_id' => $orderId]);

            return response()->json([
                'status' => 'success',
                'statusMessage' => 'Transaksi Midtrans berhasil dibuat.',
                'result' => [
                    'order_id' => $orderId,
                    'snap_token' => $result['token'] ?? null,
                    'payment_url' => isset($result['token']) ? $midtrans->getRedirectUrl($result['token']) : null,
                    'invoice_reference' => $invoiceReference,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Gagal membuat transaksi Midtrans: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testMidtransCallback(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'status' => 'required|string|in:settlement,pending,expire,cancel',
        ]);

        try {
            $gateway = Gateway::where('provider', 'midtrans')->where('is_active', true)->first();
            if (!$gateway) {
                return response()->json(['status' => 'error', 'statusMessage' => 'Gateway Midtrans aktif tidak ditemukan.'], 404);
            }

            $statusCode = '200';
            if ($request->status === 'pending') $statusCode = '201';
            if ($request->status === 'expire' || $request->status === 'cancel') $statusCode = '407';

            // --- Cari invoice dari order_id ---
            // Format order_id: {reference}-{SUFFIX}-{timestamp}
            // Contoh: INV-PMB.40001-TEST-1714387654
            //         INV-PMB.40001-RESEND-1714387654
            //         INV-PMB.40001-SISA-1714387654
            // Strategi: potong 2 bagian terakhir (suffix + timestamp) jika ada 3+ bagian,
            //           atau potong 1 bagian terakhir jika hanya ada 2 bagian.
            $orderId = $request->order_id;
            $parts = explode('-', $orderId);
            $partCount = count($parts);

            if ($partCount < 2) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'Format Order ID tidak valid. Gunakan format: {invoice_reference}-{SUFFIX}-{timestamp}'
                ], 400);
            }

            // Potong suffix dan timestamp di akhir untuk mendapatkan invoice reference.
            // Suffix standar: TEST, RESEND, SISA — selalu diikuti timestamp (numerik).
            // Strategi aman: cek apakah part terakhir adalah numerik (timestamp),
            // dan part kedua-dari-akhir adalah suffix teks → potong keduanya.
            $lastPart = end($parts);
            $secondLastPart = $parts[$partCount - 2] ?? '';
            $knownSuffixes = ['TEST', 'RESEND', 'SISA'];

            if (is_numeric($lastPart) && in_array(strtoupper($secondLastPart), $knownSuffixes)) {
                // Format: {reference}-{SUFFIX}-{timestamp} → ambil semua kecuali 2 terakhir
                $referenceParts = array_slice($parts, 0, $partCount - 2);
            } elseif (is_numeric($lastPart)) {
                // Format: {reference}-{timestamp} → ambil semua kecuali 1 terakhir
                $referenceParts = array_slice($parts, 0, $partCount - 1);
            } else {
                // Tidak ada timestamp → pakai seluruh order_id sebagai reference
                $referenceParts = $parts;
            }

            $invoiceReference = implode('-', $referenceParts);

            if (empty($invoiceReference)) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'Gagal mengekstrak invoice reference dari Order ID: ' . $orderId
                ], 400);
            }

            $invoice = Invoice::whereReference($invoiceReference)->first();

            if (!$invoice) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => "Invoice tidak ditemukan dengan reference: {$invoiceReference} (diekstrak dari order_id: {$orderId})"
                ], 404);
            }

            // Sesuaikan gross_amount dengan jumlah invoice aktual
            $adminFee = 3500;
            $grossAmount = number_format((int)$invoice->amount + $adminFee, 2, '.', '');
            $signature = hash("sha512", $orderId . $statusCode . $grossAmount . $gateway->server_key);

            // Simulasi payload Midtrans notification
            $payload = [
                'order_id' => $orderId,
                'status_code' => $statusCode,
                'transaction_status' => $request->status,
                'payment_type' => 'credit_card',
                'transaction_id' => 'TRX-' . Str::random(10),
                'gross_amount' => $grossAmount,
                'fraud_status' => 'accept',
                'transaction_time' => now()->toDateTimeString(),
                'signature_key' => $signature,
            ];

            $service = PaymentFactory::createFromProvider('midtrans');
            $data = $service->handleCallback($payload);

            $transactionStatus = $data['transaction_status'];
            $fraudStatus = $data['fraud_status'];
            $isSuccess = ($transactionStatus == 'settlement' || ($transactionStatus == 'capture' && $fraudStatus == 'accept'));

            $statusLabel = $isSuccess ? 'Terbayar (Settlement)' : ucfirst($transactionStatus);

            LogService::log("Simulasi Callback Midtrans: {$orderId} → {$transactionStatus}", 'info', [
                'order_id' => $orderId,
                'invoice_reference' => $invoiceReference,
                'status' => $transactionStatus
            ]);

            return response()->json([
                'status' => 'success',
                'statusMessage' => "Simulasi callback berhasil. Invoice: {$invoiceReference}. Status: {$statusLabel}.",
                'result' => array_merge($data, [
                    'invoice_reference' => $invoiceReference,
                    'invoice_amount' => $invoice->amount,
                    'invoice_status' => $invoice->status,
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Gagal simulasi callback: ' . $e->getMessage()
            ], 500);
        }
    }
}
