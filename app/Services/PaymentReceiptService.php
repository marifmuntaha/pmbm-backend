<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use App\Models\Student\StudentProgram;
use Carbon\Carbon;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentReceiptService
{
    /**
     * Generate receipt for a payment
     */
    public function generateReceipt(Payment $payment, User $user): Payment
    {
        // Check if receipt already exists
        if ($payment->receipt_number) {
            return $payment;
        }

        // Generate unique receipt number and token
        $receiptNumber = $this->generateReceiptNumber($payment);
        $receiptToken = Str::uuid()->toString();

        // Update payment with receipt info
        $payment->update([
            'receipt_number' => $receiptNumber,
            'receipt_token' => $receiptToken,
            'receipt_generated_at' => now(),
            'receipt_generated_by' => $user->id,
        ]);

        return $payment->fresh();
    }

    /**
     * Generate unique receipt number
     * Format: RCP-{INST_ID}-{YEAR}-{NUMBER}
     */
    private function generateReceiptNumber(Payment $payment): string
    {
        $year = date('Y');
        $institutionId = str_pad($payment->institutionId, 3, '0', STR_PAD_LEFT);

        // Get last receipt number for this institution and year
        $lastReceipt = Payment::where('institutionId', $payment->institutionId)
            ->whereNotNull('receipt_number')
            ->where('receipt_number', 'like', "RCP-$institutionId-$year-%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt) {
            // Extract number from last receipt and increment
            $parts = explode('-', $lastReceipt->receipt_number);
            $lastNumber = (int) end($parts);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $number = str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        return "RCP-$institutionId-$year-$number";
    }

    /**
     * Generate QR Code and save to temporary file
     * Returns the file path
     */
    public function generateQRCode(string $url): string
    {
        try {
            $options = new QROptions([
                'version'      => 5,
                'outputType'   => QROutputInterface::GDIMAGE_PNG,
                'eccLevel'     => EccLevel::L,
                'scale'        => 3,
                'imageBase64'  => false, // Don't use base64
            ]);

            $qrcode = new QRCode($options);

            // Generate unique filename
            $filename = 'qr_' . md5($url . time()) . '.png';
            $filepath = storage_path('app/temp/' . $filename);

            // Create temp directory if not exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            // Save QR code to file
            $qrcode->render($url, $filepath);

            Log::info('QR code saved to file', ['path' => $filepath, 'url_length' => strlen($url)]);
            return $filepath;

        } catch (Exception $e) {
            Log::warning('QR Code generation failed', ['error' => $e->getMessage(), 'url' => $url]);

            // Return empty string to indicate failure
            return '';
        }
    }

    /**
     * Get receipt data for PDF generation
     */
    public function getReceiptData(Payment $payment, ?string $frontendUrl = null): array
    {
        $payment->load(['user', 'personal', 'institution', 'invoice']);

        // Use frontend URL if provided, fallback to APP_URL (though usually backend)
        // For production, this should ideally be FRONTEND_URL
        if (!$frontendUrl) {
            $frontendUrl = env('FRONTEND_URL', env('APP_URL', 'http://localhost:3000'));
        }

        // Clean the frontend URL
        $parsedUrl = parse_url($frontendUrl);
        $cleanUrl = ($parsedUrl['scheme'] ?? 'http') . '://' . ($parsedUrl['host'] ?? 'localhost');
        if (isset($parsedUrl['port'])) {
            $cleanUrl .= ':' . $parsedUrl['port'];
        }

        $verifyUrl = $cleanUrl . "/verify-receipt/$payment->receipt_token";
        $qrCodePath = $this->generateQRCode($verifyUrl);

        // Get treasurer name (who generated the receipt)
        if ($payment->receipt_generated_by) {
            User::find($payment->receipt_generated_by);
        }

        // Determine signature name
        $signatureName = 'Bendahara';

        // Find institution treasurer (Role 3)
        if ($payment->institutionId) {
            $treasurerUser = User::where('institutionId', $payment->institutionId)
                ->where('role', 3) // Assuming role 3 is treasurer/staff
                ->first();

            if ($treasurerUser) {
                $signatureName = $treasurerUser->name;
            }
        }

        // Get institution logo path (local file only)
        $institutionLogoPath = null;
        if ($payment->institution && $payment->institution->logo) {
            $logoUrl = $payment->institution->logo;

            // Try to find local path
            $logoPath = null;

            if (filter_var($logoUrl, FILTER_VALIDATE_URL)) {
                // If it's a URL, try to convert to local path if it matches our storage
                $path = parse_url($logoUrl, PHP_URL_PATH);
                if ($path && Str::startsWith($path, '/storage')) {
                    $localPath = public_path(substr($path, 1)); // remove leading slash
                    if (file_exists($localPath)) {
                        $logoPath = $localPath;
                        Log::info('Converted remote logo URL to local path', ['url' => $logoUrl, 'path' => $localPath]);
                    }
                }
            } else {
                // It's a relative path, assume it's in public/storage
                $localPath = public_path('storage/' . $logoUrl);
                if (file_exists($localPath)) {
                    $logoPath = $localPath;
                }
            }

            if ($logoPath && file_exists($logoPath)) {
                $institutionLogoPath = $logoPath;
                Log::info('Institution logo path set', ['path' => $logoPath]);
            } else {
                Log::warning('Logo file not found', ['url' => $logoUrl]);

                // Fallback: Use the URL as is if allowed (but risk of timeout/hanging)
                // For now, we leave it null if local file not found to avoid Dompdf hanging
            }
        }

        // Get Accurate Student Program Info
        $studentProgram = StudentProgram::with(['program', 'boarding'])
            ->where('userId', $payment->userId)
            ->first();

        // Format Program Name
        $programName = $payment->invoice->description ?? 'Pendaftaran PMB';
        if ($studentProgram && $studentProgram->program) {
            $programName = $studentProgram->program->name;
            if ($studentProgram->boarding) {
                $programName .= ' (' . $studentProgram->boarding->name . ')';
            }
        }

        return [
            // Original objects
            'payment' => $payment,
            'institution' => $payment->institution,
            'student' => $payment->personal,
            'user' => $payment->user,
            'invoice' => $payment->invoice,
            'student_program' => $studentProgram,

            // Formatted fields for template
            'institution_name' => $payment->institution->name ?? 'N/A',
            'institution_logo_path' => $institutionLogoPath,
            'student_name' => $payment->personal->name ?? 'N/A',
            'registration_number' => $studentProgram->registration_number ?? ($payment->personal->nisn ?? 'N/A'),
            'program_name' => $programName,
            'invoice_reference' => $payment->invoice->reference ?? 'N/A',
            'receipt_number' => $payment->receipt_number,
            'receipt_date' => $payment->receipt_generated_at,
            'transaction_date' => Carbon::parse($payment->transaction_time)->format('d/m/Y H:i'),
            'payment_date' => Carbon::parse($payment->transaction_time)->format('d F Y, H:i') . ' WIB',
            'transaction_id' => $payment->transaction_id,
            'payment_method' => $payment->method == 1 ? 'Tunai' : 'Online',
            'amount' => 'Rp ' . number_format($payment->amount, 0, ',', '.'),
            'amount_formatted' => number_format($payment->amount, 0, ',', '.'),
            'print_date' => Carbon::now()->format('d/m/Y H:i:s'),
            'qr_code_path' => $qrCodePath,
            'verify_url' => $verifyUrl,
            'signature_name' => $signatureName,
            'signature_location' => 'Jepara',
            'signature_date' => Carbon::now()->format('d F Y'),
            'lock_icon_path' => public_path('assets/images/lock-verified.png'),
        ];
    }

    /**
     * Generate PDF HTML from receipt data
     * @throws Throwable
     */
    public function generatePDFHtml(array $data): string
    {
        // Use simplified template to avoid Dompdf hanging
        return view('pdf.receipt-simple', $data)->render();
    }

    /**
     * Generate PDF HTML for multiple receipts
     * @throws Throwable
     */
    public function generateMultiplePDFHtml(array $paymentsData): string
    {
        return view('pdf.receipt-multiple', ['payments' => $paymentsData])->render();
    }
    /**
     * Generate PDF file from data and save to temp storage
     */
    public function generatePdfFile(array $data): string
    {
        try {
            $html = $this->generatePDFHtml($data);
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        // Use PdfService to generate and save
        $pdfService = new PdfService();
        $studentName = isset($data['student_name']) && $data['student_name'] !== 'N/A' ? \Illuminate\Support\Str::slug($data['student_name']) : \Illuminate\Support\Str::random(10);
        $filename = 'bukti-pembayaran-' . $studentName . '.pdf';
        $path = 'temp/' . $filename;

        // Ensure temp directory exists via Storage
        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        $pdfService->generateAndSave($html, $path, [
             'format' => 'A4',
             'orientation' => 'portrait'
        ]);

        return Storage::path($path);
    }

    /**
     * Generate signed PDF file
     */
    public function generateSignedPdfFile(array $data): string
    {
        $pdfPath = $this->generatePdfFile($data);

        try {
            $payment = $data['payment'];
            $certificateService = new CertificateService();
            $tcpdfService = new TcpdfService();

            $certificatePath = $certificateService->getCertificateForInstitution($payment->institution);
            $certificatePassword = $certificateService->getCertificatePassword();

            $studentName = isset($data['student_name']) && $data['student_name'] !== 'N/A' ? \Illuminate\Support\Str::slug($data['student_name']) : \Illuminate\Support\Str::random(10);
            $signedFilename = 'receipt-' . ($payment->receipt_number ?? $studentName) . '.pdf';
            $signedPath = storage_path('app/temp/' . $signedFilename);

            $tcpdfService->signExistingPdfToFile(
                $pdfPath,
                $signedPath,
                $certificatePath,
                $certificatePassword,
                [
                    'author' => 'Bendahara Lembaga',
                    'title' => 'bukti pembayaran PMBM',
                    'name' => $data['signature_name'] ?? 'Bendahara',
                    'reason' => 'bukti pembayaran PMBM'
                ]
            );

            // Cleanup unsigned PDF
            if (file_exists($pdfPath)) {
                @unlink($pdfPath);
            }

            return $signedPath;

        } catch (Exception $e) {
            \Log::error('Failed to sign payment receipt', ['error' => $e->getMessage()]);
            // Return unsigned path as fallback if signing fails
            return $pdfPath;
        }
    }
}
