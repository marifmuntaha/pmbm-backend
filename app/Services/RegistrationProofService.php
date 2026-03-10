<?php

namespace App\Services;

use App\Models\Student\StudentProgram;
use App\Models\Student\StudentPersonal;
use App\Models\Student\StudentParent;
use App\Models\Student\StudentAddress;
use App\Models\Student\StudentFile;
use App\Models\Institution;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class RegistrationProofService
{
    /**
     * Generate registration proof for a student
     */
    public function generateRegistrationProof(StudentProgram $studentProgram): StudentProgram
    {
        // Generate registration number if not exists
        if (!$studentProgram->registration_number) {
            $registrationNumber = $this->generateRegistrationNumber($studentProgram);
            $registrationToken = Str::random(64);

            $studentProgram->update([
                'registration_number' => $registrationNumber,
                'registration_token' => $registrationToken,
                'registration_generated_at' => now(),
            ]);

            $studentProgram->refresh();
        }

        return $studentProgram;
    }

    /**
     * Generate unique registration number
     * Format: PMB-{institutionId}.{sequence}
     */
    private function generateRegistrationNumber(StudentProgram $studentProgram): string
    {
        $institution = $studentProgram->institution;

        // Get the last registration number for this institution and year
        $lastRegistration = StudentProgram::where('institutionId', $studentProgram->institutionId)
            ->where('yearId', $studentProgram->yearId)
            ->whereNotNull('registration_number')
            ->orderBy('registration_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastRegistration && $lastRegistration->registration_number) {
            // Extract sequence from last registration number
            $parts = explode('.', $lastRegistration->registration_number);
            if (count($parts) === 2) {
                $sequence = intval($parts[1]) + 1;
            }
        }

        return sprintf('PMB-%d.%04d', $studentProgram->institutionId, $sequence);
    }

    /**
     * Get all data needed for registration proof PDF
     */
    public function getRegistrationProofData(StudentProgram $studentProgram, ?string $frontendUrl = null): array
    {
        // Load all relationships
        $studentProgram->load([
            'personal',
            'parent',
            'address',
            'program',
            'boarding',
            'institution'
        ]);

        // Get student files
        $files = StudentFile::where('userId', $studentProgram->userId)->first();


        // Generate verification URL - use frontend URL from request or fallback to env
        if (!$frontendUrl) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        }

        // Clean the frontend URL - remove query strings and fragments
        $parsedUrl = parse_url($frontendUrl);
        $cleanUrl = ($parsedUrl['scheme'] ?? 'http') . '://' . ($parsedUrl['host'] ?? 'localhost');
        if (isset($parsedUrl['port'])) {
            $cleanUrl .= ':' . $parsedUrl['port'];
        }

        $verifyUrl = $cleanUrl . '/verify/' . $studentProgram->registration_token;

        \Log::info('Generating QR Code', [
            'original_frontend_url' => $frontendUrl,
            'cleaned_frontend_url' => $cleanUrl,
            'verify_url' => $verifyUrl,
            'token' => $studentProgram->registration_token
        ]);

        // Generate QR Code
        $qrCode = $this->generateQRCode($verifyUrl);

        // Get institution logo as base64
        $logoBase64 = null;
        if ($studentProgram->institution && $studentProgram->institution->logo) {
            $logoUrl = $studentProgram->institution->logo;
            $parsedUrl = parse_url($logoUrl);
            if (isset($parsedUrl['path'])) {
                $path = str_replace('/storage/', '', $parsedUrl['path']);
                $fullPath = storage_path('app/public/' . $path);
                if (file_exists($fullPath)) {
                    $imageData = file_get_contents($fullPath);
                    $mimeType = mime_content_type($fullPath);
                    $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            }
        }

        // Get student photo as base64
        $photoBase64 = null;
        if ($files && $files->filePhoto) {
            $photoUrl = $files->filePhoto;
            $parsedUrl = parse_url($photoUrl);
            if (isset($parsedUrl['path'])) {
                $path = str_replace('/storage/', '', $parsedUrl['path']);
                $fullPath = storage_path('app/public/' . $path);
                if (file_exists($fullPath)) {
                    $imageData = file_get_contents($fullPath);
                    $mimeType = mime_content_type($fullPath);
                    $photoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            }
        }

        return [
            'registration_number' => $studentProgram->registration_number,
            'institution' => $studentProgram->institution,
            'logo_base64' => $logoBase64,
            'student' => [
                'name' => $studentProgram->personal->name ?? '-',
                'nisn' => $studentProgram->personal->nisn ?? '-',
                'nik' => $studentProgram->personal->nik ?? '-',
                'birthPlace' => $studentProgram->personal->birthPlace ?? '-',
                'birthDate' => $studentProgram->personal->birthDate ?? '-',
                'gender' => $studentProgram->personal->gender == 1 ? 'Laki-laki' : 'Perempuan',
                'guardName' => $studentProgram->parent->guardName ?? '-',
                'phone' => $studentProgram->parent->guardPhone ?? '-',
            ],
            'program' => [
                'name' => $studentProgram->program->name ?? '-',
                'boarding' => $studentProgram->boarding->name ?? '-',
            ],
            'photo_base64' => $photoBase64,
            'qr_code' => $qrCode,
            'verify_url' => $verifyUrl,
            'generated_at' => $studentProgram->registration_generated_at,
        ];
    }

    /**
     * Generate QR Code as base64 image
     */
    private function generateQRCode(string $data): string
    {
        try {
            $options = new QROptions([
                'version' => QRCode::VERSION_AUTO, // Auto-detect best version for data length
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L, // Low error correction for more data capacity
                'scale' => 8, // Slightly smaller scale for better compatibility
                'imageBase64' => false,
            ]);

            $qrcode = new QRCode($options);

            // Generate QR code as binary PNG
            $qrImageData = $qrcode->render($data);

            // Convert to base64 data URI
            $base64 = base64_encode($qrImageData);

            \Log::info('QR Code generated successfully', [
                'data_length' => strlen($data),
                'base64_length' => strlen($base64)
            ]);

            return 'data:image/png;base64,' . $base64;

        } catch (\Exception $e) {
            \Log::error('QR Code generation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
                'data_length' => strlen($data)
            ]);

            // Return a simple placeholder QR code with just the token
            try {
                // Try with just a shorter URL
                $shortUrl = substr($data, 0, 100);
                $options = new QROptions([
                    'version' => QRCode::VERSION_AUTO,
                    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel' => QRCode::ECC_L,
                    'scale' => 8,
                    'imageBase64' => false,
                ]);

                $qrcode = new QRCode($options);
                $qrImageData = $qrcode->render($shortUrl);
                $base64 = base64_encode($qrImageData);
                return 'data:image/png;base64,' . $base64;
            } catch (\Exception $e2) {
                return 'FALLBACK';
            }
        }
    }

    /**
     * Generate PDF file using DomPDF
     */
    public function generatePdfFile(array $data): string
    {
        $html = view('pdf.registration-proof', $data)->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save to temporary file
        $studentName = isset($data['student']['name']) && $data['student']['name'] !== '-' ? \Illuminate\Support\Str::slug($data['student']['name']) : uniqid();
        $filename = 'bukti-pendaftaran-' . $studentName . '.pdf';
        $tempPath = storage_path('app/temp/' . $filename);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        file_put_contents($tempPath, $dompdf->output());

        return $tempPath;
    }

    /**
     * Generate signed PDF file
     */
    public function generateSignedPdfFile(array $data, \App\Models\Institution $institution): string
    {
        $pdfPath = $this->generatePdfFile($data);

        try {
            $certificateService = new CertificateService();
            $tcpdfService = new TcpdfService();

            $certificatePath = $certificateService->getCertificateForInstitution($institution, 'headmaster');
            $certificatePassword = $certificateService->getCertificatePassword();

            $registrationNumber = $data['registration_number'] ?? uniqid();
            $signedFilename = 'bukti-pendaftaran-' . $registrationNumber . '.pdf';
            $signedPath = storage_path('app/temp/' . $signedFilename);

            $tcpdfService->signExistingPdfToFile(
                $pdfPath,
                $signedPath,
                $certificatePath,
                $certificatePassword,
                [
                    'author' => 'Kepala Madrasah',
                    'title' => 'Bukti Pendaftaran Murid Baru',
                    'name' => $institution->head ?? 'Kepala Lembaga',
                    'reason' => 'Bukti Pendaftaran Murid Baru'
                ]
            );

            // Cleanup unsigned PDF
            if (file_exists($pdfPath)) {
                @unlink($pdfPath);
            }

            return $signedPath;

        } catch (\Exception $e) {
            \Log::error('Failed to sign registration proof', ['error' => $e->getMessage()]);
            // Return unsigned path as fallback
            return $pdfPath;
        }
    }
}
