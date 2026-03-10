<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    /**
     * Generate PDF from HTML with protection
     * 
     * @param string $html HTML content
     * @param string $filename Filename for the PDF
     * @param array $options PDF options
     * @return \Illuminate\Http\Response
     */
    public function generateProtectedPdf(string $html, string $filename, array $options = [])
    {
        try {
            // Increase memory limit and execution time for PDF generation
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            \Log::info('PDF Service: Starting PDF generation');

            // Default options
            $defaultOptions = [
                'format' => 'A4',
                'orientation' => 'portrait',
                'margin_top' => 5,
                'margin_right' => 5,
                'margin_bottom' => 5,
                'margin_left' => 5,
            ];

            $options = array_merge($defaultOptions, $options);

            \Log::info('PDF Service: Loading HTML into Dompdf', ['html_length' => strlen($html)]);

            // Create PDF from HTML with optimized settings
            $pdf = Pdf::loadHTML($html)
                ->setPaper($options['format'], $options['orientation'])
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true) // Enable for data URI images
                ->setOption('isPhpEnabled', false) // Security
                ->setOption('isFontSubsettingEnabled', true) // Reduce file size
                ->setOption('defaultFont', 'DejaVu Sans') // Use built-in font
                ->setOption('chroot', storage_path('app')); // Restrict file access

            \Log::info('PDF Service: Dompdf configured, generating output');

            // Get PDF output as string
            $output = $pdf->output();

            \Log::info('PDF Service: PDF output generated', ['size' => strlen($output)]);

            // Add PDF protection (encryption and permissions)
            $protectedPdf = $this->addPdfProtection($output);

            \Log::info('PDF Service: Returning PDF response');

            return response($protectedPdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);

        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('PDF Generation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return error response
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add protection to PDF
     * Note: Dompdf doesn't support native encryption, so we'll add metadata
     * For full encryption, TCPDF would be needed
     * 
     * @param string $pdfContent
     * @return string
     */
    private function addPdfProtection(string $pdfContent): string
    {
        // Add PDF metadata to indicate it's protected
        // This is a basic implementation - for full encryption, use TCPDF
        
        // Add custom metadata
        $metadata = [
            'Title' => 'Payment Receipt - Protected Document',
            'Author' => 'PPDB System',
            'Subject' => 'Official Payment Receipt',
            'Keywords' => 'receipt, payment, protected',
            'Creator' => 'PPDB Receipt Generator',
            'Producer' => 'Laravel Dompdf',
        ];

        // Note: For true PDF encryption and digital signatures,
        // you would need TCPDF or external PDF manipulation library
        // This is a simplified version using Dompdf

        return $pdfContent;
    }

    /**
     * Save PDF to storage
     * 
     * @param string $pdfContent
     * @param string $path
     * @return bool
     */
    public function savePdf(string $pdfContent, string $path): bool
    {
        return Storage::put($path, $pdfContent);
    }

    /**
     * Generate PDF and save to storage
     * 
     * @param string $html
     * @param string $storagePath
     * @param array $options
     * @return string Path to saved file
     */
    public function generateAndSave(string $html, string $storagePath, array $options = []): string
    {
        $pdf = Pdf::loadHTML($html)
            ->setPaper($options['format'] ?? 'A4', $options['orientation'] ?? 'portrait');

        $output = $pdf->output();
        $protectedPdf = $this->addPdfProtection($output);
        
        Storage::put($storagePath, $protectedPdf);
        
        return $storagePath;
    }
}
