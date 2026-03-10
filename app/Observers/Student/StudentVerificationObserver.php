<?php

namespace App\Observers\Student;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Student\StudentVerification;
use App\Models\Student\StudentProgram;
use App\Models\User;
use App\Services\RegistrationProofService;
use App\Services\CertificateService;
use App\Services\TcpdfService;
use Illuminate\Support\Facades\Log;

class StudentVerificationObserver
{
    /**
     * Handle the StudentVerification "created" event.
     */
    public function created(StudentVerification $studentVerification): void
    {
        $user = User::find($studentVerification->userId);
        if ($user) {
            $registrationProofPath = null;
            $caption = '*PMBM YAYASAN DARUL HIKMAH*'. PHP_EOL . PHP_EOL;
            $caption .= 'Selamat, berkas pendaftaran Anda telah lengkap.'. PHP_EOL;
            $caption .= 'admin akan memferifikasi pendaftaran anda, setelah itu kami akan mengirimkan pemberitahuan & tagihan untuk pembayaran.'. PHP_EOL . PHP_EOL;
            $caption .= 'Terlampir adalah bukti pendaftaran Anda.'. PHP_EOL . PHP_EOL;
            $caption .= 'Terima kasih.'. PHP_EOL;

            // 1. Get Student Program Data
            $studentProgram = StudentProgram::with([
                'personal', 'parent', 'address', 'program', 'boarding', 'institution', 'file'
            ])->where('userId', $user->id)->first();

            try {
                if ($studentProgram) {
                    $registrationService = new RegistrationProofService();
                    $certificateService = new CertificateService();
                    $tcpdfService = new TcpdfService();

                    // Generate registration number if not exists
                    if (!$studentProgram->registration_number) {
                        $studentProgram = $registrationService->generateRegistrationProof($studentProgram);
                    }

                    $data = $registrationService->getRegistrationProofData($studentProgram, env('FRONTEND_URL', 'http://localhost:3000'));
                    $tempPdfPath = $registrationService->generatePdfFile($data);
                    
                    $registrationProofPath = $tempPdfPath;
                }
            } catch (\Exception $e) {
                Log::error('Gagal generate PDF bukti pendaftaran saat verifikasi selesai: ' . $e->getMessage());
            }

            // 2. Send WhatsApp to Student
            if ($user->phone) {
                // Send via Job (File + Caption, or just Caption if PDF failed)
                SendWhatsAppMessage::dispatch(
                    $user->phone, 
                    $caption, 
                    null, 
                    $caption, 
                    $registrationProofPath
                );
            }

            // 3. Send WhatsApp to Treasurer (Role 3)
            if ($studentProgram && $studentProgram->institutionId) {
                $treasure = User::where('role', 3)
                    ->where('institutionId', $studentProgram->institutionId)
                    ->first();

                if ($treasure && $treasure->phone) {
                    $message = '*PMBM YAYASAN DARUL HIKMAH*' . PHP_EOL . PHP_EOL;
                    $message .= 'Pendaftaran siswa baru atas nama ' . $user->name . ' telah diverifikasi.' . PHP_EOL;
                    $message .= 'Silahkan melakukan generate tagihan.' . PHP_EOL;
                    $message .= 'Terima kasih.' . PHP_EOL;

                    SendWhatsAppMessage::dispatch($treasure->phone, $message);
                }
            }
        }
    }

    /**
     * Handle the StudentVerification "updated" event.
     */
    public function updated(StudentVerification $studentVerification): void
    {
      //
    }

    /**
     * Handle the StudentVerification "deleted" event.
     */
    public function deleted(StudentVerification $studentVerification): void
    {
        //
    }

    /**
     * Handle the StudentVerification "restored" event.
     */
    public function restored(StudentVerification $studentVerification): void
    {
        //
    }

    /**
     * Handle the StudentVerification "force deleted" event.
     */
    public function forceDeleted(StudentVerification $studentVerification): void
    {
        //
    }
}
