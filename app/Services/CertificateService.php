<?php

namespace App\Services;

use App\Models\Institution;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    private const CERT_PASSWORD = 'Masadepan100%';
    private const CERT_VALIDITY_DAYS = 365; // 1 year

    /**
     * Get or generate certificate for institution
     * @param Institution $institution
     * @param string $type 'headmaster' or 'treasurer'
     * @return string|null
     */
    public function getCertificateForInstitution(Institution $institution, string $type = 'treasurer'): ?string
    {
        // Check if certificate exists and is valid
        if ($this->hasCertificate($institution, $type) && !$this->isCertificateExpired($institution)) {
            Log::info('Using existing certificate', [
                'institution_id' => $institution->id,
                'type' => $type
            ]);

            $certPath = $type === 'headmaster'
                ? $institution->headmaster_certificate_path
                : $institution->certificate_path;

            return Storage::path($certPath);
        }

        // Generate new certificate
        Log::info('Generating new certificate', [
            'institution_id' => $institution->id,
            'type' => $type
        ]);
        return $this->generateCertificate($institution, $type);
    }

    /**
     * Check if institution has certificate
     */
    private function hasCertificate(Institution $institution, string $type = 'treasurer'): bool
    {
        $certPath = $type === 'headmaster'
            ? $institution->headmaster_certificate_path
            : $institution->certificate_path;

        if (!$certPath) {
            return false;
        }

        return Storage::exists($certPath);
    }

    /**
     * Check if certificate is expired
     */
    private function isCertificateExpired(Institution $institution): bool
    {
        if (!$institution->certificate_expires_at) {
            return true;
        }

        return now()->greaterThan($institution->certificate_expires_at);
    }

    /**
     * Generate self-signed certificate for institution
     */
    private function generateCertificate(Institution $institution, string $type = 'treasurer'): ?string
    {
        try {
            // Get certificate holder information based on type
            $holderInfo = $type === 'headmaster'
                ? $this->getHeadmasterInfo($institution)
                : $this->getTreasurerInfo($institution);

            // Certificate distinguished name
            $dn = [
                "countryName" => "ID",
                "stateOrProvinceName" => "Jawa Tengah",
                "localityName" => "Jepara",
                "organizationName" => $institution->surname,
                "organizationalUnitName" => $holderInfo['unit'],
                "commonName" => $holderInfo['name'],
                "emailAddress" => $holderInfo['email']
            ];

            // Generate private key
            $privkey = openssl_pkey_new([
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ]);

            // Generate certificate signing request
            $csr = openssl_csr_new($dn, $privkey, ['digest_alg' => 'sha256']);

            // Generate self-signed certificate (valid for 1 year)
            $x509 = openssl_csr_sign($csr, null, $privkey, self::CERT_VALIDITY_DAYS, ['digest_alg' => 'sha256']);

            // Export certificate to PEM format
            $certPem = '';
            openssl_x509_export($x509, $certPem);

            // Export private key to PEM format
            $keyPem = '';
            openssl_pkey_export($privkey, $keyPem, self::CERT_PASSWORD);

            // Save certificate and key to storage
            $certDir = 'certificates';
            $certBasename = 'institution_' . $institution->id . '_' . $type;

            // Create directory if not exists
            if (!Storage::exists($certDir)) {
                Storage::makeDirectory($certDir);
            }

            // Save certificate file (.crt)
            $certPath = $certDir . '/' . $certBasename . '.crt';
            Storage::put($certPath, $certPem);

            // Save private key file (.key)
            $keyPath = $certDir . '/' . $certBasename . '.key';
            Storage::put($keyPath, $keyPem);

            // Get absolute paths
            $certAbsolutePath = Storage::path($certPath);
            $keyAbsolutePath = Storage::path($keyPath);

            Log::info('Certificate files saved', [
                'type' => $type,
                'cert_path' => $certAbsolutePath,
                'key_path' => $keyAbsolutePath,
                'cert_exists' => file_exists($certAbsolutePath),
                'key_exists' => file_exists($keyAbsolutePath)
            ]);

            // Update institution record based on type
            $expiresAt = now()->addDays(self::CERT_VALIDITY_DAYS);

            if ($type === 'headmaster') {
                $institution->headmaster_certificate_path = $certPath;
            } else {
                $institution->certificate_path = $certPath;
            }

            $institution->certificate_generated_at = now();
            $institution->certificate_expires_at = $expiresAt;
            $institution->save();

            Log::info('Certificate generated successfully', [
                'institution_id' => $institution->id,
                'type' => $type,
                'cert_path' => $certPath,
                'expires_at' => $expiresAt
            ]);

            // Return certificate path (key path will be derived in TcpdfService)
            return $certAbsolutePath;

        } catch (Exception $e) {
            Log::error('Certificate generation failed', [
                'institution_id' => $institution->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get treasurer information for institution
     */
    private function getTreasurerInfo(Institution $institution): array
    {
        // Use institution-based treasurer information
        // Format: Bendahara [Institution Name]
        $treasurerName = 'Bendahara ' . $institution->name;
        $treasurerEmail = $institution->email;

        return [
            'name' => $treasurerName,
            'email' => $treasurerEmail,
            'unit' => 'Bendahara'
        ];
    }

    /**
     * Get headmaster information for institution
     */
    private function getHeadmasterInfo(Institution $institution): array
    {
        // Use institution headmaster if available, otherwise use default
        $headmasterName = $institution->headmaster ?? ('Kepala ' . $institution->name);
        $headmasterEmail = $institution->email;

        return [
            'name' => $headmasterName,
            'email' => $headmasterEmail,
            'unit' => 'Kepala Madrasah'
        ];
    }

    /**
     * Get certificate password
     */
    public function getCertificatePassword(): string
    {
        return self::CERT_PASSWORD;
    }
}
