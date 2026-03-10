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
            $orderId = 'TEST-' . time();
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => 10000,
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
                    'redirect_url' => $result['redirect_url'] ?? null,
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

            $statusCode = '200'; // Default for testing
            if ($request->status === 'pending') $statusCode = '201';
            if ($request->status === 'expire' || $request->status === 'cancel') $statusCode = '407';

            $grossAmount = '10000.00';
            $signature = hash("sha512", $request->order_id . $statusCode . $grossAmount . $gateway->server_key);

            // Simulate Midtrans notification payload
            $payload = [
                'order_id' => $request->order_id,
                'status_code' => $statusCode,
                'transaction_status' => $request->status,
                'payment_type' => 'credit_card',
                'transaction_id' => 'TRX-' . Str::random(10),
                'gross_amount' => $grossAmount,
                'fraud_status' => 'accept',
                'transaction_time' => now()->toDateTimeString(),
                'signature_key' => $signature,
            ];

            // Use the same logic as PaymentController@callback
            $provider = 'midtrans';
            $service = PaymentFactory::createFromProvider($provider);
            $data = $service->handleCallback($payload);

            $orderId = $data['order_id'] ?? '';
            $orderIdParts = explode('-', $orderId);

            if (count($orderIdParts) < 2) {
                return response()->json(['status' => 'error', 'statusMessage' => 'Format Order ID tidak valid.'], 400);
            }

            $invoiceReference = $orderIdParts[0] . '-' . $orderIdParts[1];
            $invoice = Invoice::whereReference($invoiceReference)->first();

            if (!$invoice) {
                return response()->json(['status' => 'error', 'statusMessage' => "Invoice tidak ditemukan: {$invoiceReference}"], 404);
            }

            // Internal call to emulate the processing
            $transactionStatus = $data['transaction_status'];
            $fraudStatus = $data['fraud_status'];

            $isSuccess = ($transactionStatus == 'settlement' || ($transactionStatus == 'capture' && $fraudStatus == 'accept'));

            if ($isSuccess) {
                LogService::transaction("Simulasi Callback Berhasil (Settlement): {$request->order_id}", ['order_id' => $request->order_id]);
                return response()->json([
                    'status' => 'success',
                    'statusMessage' => 'Simulasi callback berhasil. Status: Terbayar.',
                    'result' => $data
                ]);
            }

            LogService::log("Simulasi Callback Dilakukan: {$request->order_id} dengan status {$transactionStatus}", 'info', ['order_id' => $request->order_id, 'status' => $transactionStatus]);
            return response()->json([
                'status' => 'success',
                'statusMessage' => "Simulasi callback berhasil. Status: {$transactionStatus}.",
                'result' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Gagal simulasi callback: ' . $e->getMessage()
            ], 500);
        }
    }
}
