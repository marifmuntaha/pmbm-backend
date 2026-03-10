<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Tcpdf\Fpdi;
use TCPDF;

class TcpdfService
{
    /**
     * Sign an existing PDF file
     *
     * @param string $pdfPath Path to the input PDF file
     * @param string $filename Output filename
     * @param string|null $certificatePath Path to .crt file
     * @param string|null $certificatePassword Password for .key file
     * @param array $metadata
     * @return Response
     * @throws Exception
     */
    public function signExistingPdf(string $pdfPath, string $filename, ?string $certificatePath = null, ?string $certificatePassword = null, array $metadata = []): Response
    {
        try {
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            Log::info('TCPDF+FPDI: Starting PDF signing', ['input' => $pdfPath, 'filename' => $filename]);

            if (!file_exists($pdfPath)) {
                throw new Exception('Input PDF file not found: ' . $pdfPath);
            }

            // Create new PDF document using FPDI
            $pdf = new Fpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator('PMB System');
            $pdf->SetAuthor($metadata['author'] ?? 'Bendahara');
            $pdf->SetTitle($metadata['title'] ?? 'Bukti Pembayaran');
            $pdf->SetSubject($metadata['subject'] ?? 'Receipt');

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Add digital signature
            if ($certificatePath && file_exists($certificatePath) && $certificatePassword) {
                Log::info('TCPDF: Adding digital signature', ['cert_path' => $certificatePath]);

                try {
                    $keyPath = str_replace('.crt', '.key', $certificatePath);

                    if (!file_exists($keyPath)) {
                        throw new Exception('Private key file not found');
                    }

                    $certContent = file_get_contents($certificatePath);
                    $keyContent = file_get_contents($keyPath);

                    $pdf->setSignature(
                        $certContent,
                        $keyContent,
                        $certificatePassword,
                        '',
                        1,
                        [
                            'Name' => $metadata['name'] ?? 'Bendahara',
                            'Location' => 'Jepara, Indonesia',
                            'Reason' => $metadata['reason'] ?? 'Bukti Pembayaran PMB',
                            'ContactInfo' => ''
                        ]
                    );
                    Log::info('TCPDF: Digital signature configured');
                } catch (Exception $e) {
                    Log::error('TCPDF: Failed to set signature', ['error' => $e->getMessage()]);
                }
            }

            // Import pages from the existing PDF
            $pageCount = $pdf->setSourceFile($pdfPath);
            Log::info('FPDI: Source PDF has pages', ['count' => $pageCount]);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);

                // Get the size of the imported page
                $size = $pdf->getTemplateSize($templateId);

                // Add a page with the same orientation and size
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

                // Use the template at 0,0 to prevent margin shift
                $pdf->useTemplate($templateId, 0, 0);

                // Add signature appearance on the first page?
                // Or maybe the last? Usually valid for the whole doc.
                // TCPDF adds visual signature widget to the page where setSignatureAppearance is called?
                // The doc says: "The signature appearance is a widget annotation... It must be added to a page."

                if ($pageNo == 1 && $certificatePath) {
                     // Set signature appearance (if signature was set)
                     // Position might need adjustment based on generated PDF layout
                    try {
                        if ($metadata['author'] == 'Kepala Madrasah') {
                            $pdf->setSignatureAppearance(
                                160,  // x position
                                129,  // y position
                                40,   // width
                                15    // height
                            );
                        } else {
                            $pdf->setSignatureAppearance(
                                109,  // x position
                                202,  // y position
                                60,   // width
                                15    // height
                            );
                        }
                    } catch (Exception $e) {
                        // ignore
                    }
                }
            }

            // Output PDF
            Log::info('TCPDF+FPDI: Generating PDF output');
            $pdfOutput = $pdf->Output('', 'S');

            return response($pdfOutput, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public',
                'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
                'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
            ]);

        } catch (Exception $e) {
            Log::error('TCPDF+FPDI: Signing error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
}
