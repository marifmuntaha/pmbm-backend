<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use App\Models\Announcement;
use App\Models\Invoice;
use App\Models\Student\StudentAddress;
use App\Models\Student\StudentFile;
use App\Models\Student\StudentOrigin;
use App\Models\Student\StudentParent;
use App\Models\Student\StudentPersonal;
use App\Models\Student\StudentProgram;
use App\Services\CertificateService;
use App\Services\RegistrationProofService;
use App\Services\TcpdfService;
use Exception;
use App\Jobs\SendWhatsAppMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BoardingStudentExport;

class StudentController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            $personal = StudentPersonal::whereUserid($request->userId)->first();
            $parent = StudentParent::whereUserid($request->userId)->first();
            $address = StudentAddress::whereUserid($request->userId)->first();
            $program = StudentProgram::whereUserid($request->userId)->first();
            $origin = StudentOrigin::whereUserid($request->userId)->first();
            $files = StudentFile::whereUserid($request->userId)->first();
            $announcements = [];
            if ($program?->yearId) {
                $announcements = Announcement::where('yearId', $program->yearId)
                    ->where('institutionId', $program->institutionId)
                    ->limit(5)
                    ->get();
            }

            $totalStudent = 0;
            if ($program?->yearId) {
                $totalStudent = StudentProgram::where('yearId', $program->yearId)
                    ->where('institutionId', $program->institutionId)
                    ->count();
            }
            $totalInvoice = Invoice::whereUserid($request->userId)
                ->where('status', '!=', 'PAID')
                ->first();
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => [
                    'announcements' => $announcements,
                    'totalStudent' => $totalStudent,
                    'totalInvoice' => $totalInvoice?->amount,
                    'personal' => ['updated_at' => $personal?->updated_at->diffForHumans()],
                    'parent' => ['updated_at' => $parent?->updated_at->diffForHumans()],
                    'address' => ['updated_at' => $address?->updated_at->diffForHumans()],
                    'program' => ['updated_at' => $program?->updated_at->diffForHumans()],
                    'origin' => ['updated_at' => $origin?->updated_at->diffForHumans()],
                    'files' => ['updated_at' => $files?->updated_at->diffForHumans()],
                ]
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function treasurer(Request $request)
    {
        try {
            $program = StudentProgram::with(['personal', 'parent', 'address', 'program', 'boarding', 'verification'])
                ->whereYearid($request->yearId)->whereInstitutionid($request->institutionId)
                ->when($request->gender, function ($query) use ($request) {
                    $query->whereHas('personal', function ($q) use ($request) {
                        $q->where('gender', $request->gender);
                    });
                })
                ->when($request->programId, function ($query) use ($request) {
                    $query->whereProgramid($request->programId);
                })
                ->when($request->boardingId, function ($query) use ($request) {
                    $query->whereBoardingid($request->boardingId);
                })
                ->when($request->status, function ($query) use ($request) {
                    $query->whereStatus($request->status);
                })
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();
            $result = collect($program)->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'userId' => $item['userId'] ?? $item['user_id'] ?? null,
                    'name' => $item['personal']['name'],
                    'birthPlace' => $item['personal']['birthPlace'],
                    'birthDate' => $item['personal']['birthDate'],
                    'guardName' => $item['parent']['guardName'] ?? '-',
                    'address' => $item['address']['street'],
                    'program' => $item['program']['name'] ?? '-',
                    'gender' => $item['personal']['gender'] ?? '0',
                    'boarding' => $item['boarding']['name'],
                    'verification' => $item['verification']['id'] ?? null,
                    'status' => $item['status'] ?? 1,
                    'number_register' => $item['registration_number'] ?? '',
                ];
            });
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => $result
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function boarding(Request $request)
    {
        try {
            $program = StudentProgram::with(['personal', 'parent', 'address', 'program', 'boarding', 'verification', 'institution', 'room'])
                ->whereYearid($request->yearId)
                ->whereNot('boardingId', 1)
                ->when($request->institutionId, function ($query) use ($request) {
                    $query->whereInstitutionid($request->institutionId);
                })
                ->when($request->boardingId, function ($query) use ($request) {
                    $query->whereBoardingid($request->boardingId);
                })
                ->when($request->gender, function ($query) use ($request) {
                    $query->whereHas('personal', function ($q) use ($request) {
                        $q->where('gender', $request->gender);
                    });
                })
                ->get()->toArray();
            $result = collect($program)->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'registration_number' => $item['registration_number'],
                    'name' => $item['personal']['name'],
                    'birthPlace' => $item['personal']['birthPlace'],
                    'birthDate' => $item['personal']['birthDate'],
                    'gender' => $item['personal']['gender'],
                    'guardName' => $item['parent']['guardName'],
                    'address' => $item['address']['street'],
                    'boarding' => $item['boarding']['name'],
                    'institution' => $item['institution']['surname'] ?? '-',
                    'room' => $item['room']['name'] ?? null,
                ];
            });
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => $result
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function invoice(Request $request)
    {
        try {
            $program = StudentProgram::with(['personal', 'parent', 'address', 'verification', 'invoice', 'period', 'program'])
                ->where(function (Builder $query) {
                    $query->where('status', "!=", 2)
                        ->orWhereNull('status');
                })
                ->whereYearid($request->yearId)->whereInstitutionid($request->institutionId)
                ->orderBy('id', 'desc')
                ->get()->toArray();

            $result = collect($program)->map(function ($item) {
                return [
                    'userId' => $item['personal']['userId'],
                    'name' => $item['personal']['name'],
                    'guardName' => $item['parent']['guardName'] ?? "-",
                    'address' => $item['address']['street'],
                    'gender' => $item['personal']['gender'],
                    'programId' => $item['programId'],
                    'boardingId' => $item['boardingId'],
                    'verification' => $item['verification'],
                    'period' => $item['period']['description'],
                    'invoice' => $item['invoice'] ?? null,
                    'program' => $item['program']['alias'] ?? null,
                ];
            });

            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => $result
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function sendWhatsAppRegistrationProof(Request $request, $userId)
    {
        try {
            // $userId is passed as a parameter from the route
            $studentProgram = StudentProgram::with([
                'personal',
                'parent',
                'address',
                'program',
                'boarding',
                'institution',
                'file'
            ])->where('userId', $userId)->first();

            if (!$studentProgram) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'Data pendaftaran tidak ditemukan.'
                ], 404);
            }

            if (!$studentProgram->personal || !$studentProgram->parent || !$studentProgram->program || !$studentProgram->institution) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'Data pendaftaran belum lengkap. Mohon lengkapi data pendaftaran dan lembaga terlebih dahulu.'
                ], 400);
            }

            $registrationService = new RegistrationProofService();

            if (!$studentProgram->registration_number) {
                $studentProgram = $registrationService->generateRegistrationProof($studentProgram);
            }

            $data = $registrationService->getRegistrationProofData($studentProgram, $request->query('frontend_url'));
            $signedPath = $registrationService->generateSignedPdfFile($data, $studentProgram->institution);

            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'Pengguna tidak ditemukan.'
                ], 404);
            }

            $phoneNumber = $user->phone;

            if (!$phoneNumber) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'Nomor telepon pengguna tidak ditemukan.'
                ], 400);
            }

            $caption = '*PMBM YAYASAN DARUL HIKMAH*'. PHP_EOL . PHP_EOL;
            $caption .= "ini adalah pesan otomatis dari sistem." . PHP_EOL . PHP_EOL;
            $caption .= 'Selamat, berkas pendaftaran Anda telah lengkap.'. PHP_EOL;
            $caption .= 'admin akan memverifikasi pendaftaran anda, setelah itu kami akan mengirimkan pemberitahuan & tagihan untuk pembayaran.'. PHP_EOL . PHP_EOL;
            $caption .= 'Terlampir adalah bukti pendaftaran Anda.'. PHP_EOL . PHP_EOL;
            $caption .= 'Terima kasih.'. PHP_EOL;
            SendWhatsAppMessage::dispatch($phoneNumber, $caption, null, 'Bukti pendaftaran Anda', $signedPath);

            return response()->json([
                'status' => 'success',
                'statusMessage' => 'Bukti pendaftaran berhasil dikirim ke WhatsApp Anda.'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Data pendaftaran tidak ditemukan.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    public function generateRegistrationProof(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $studentProgram = StudentProgram::with([
                'personal',
                'parent',
                'address',
                'program',
                'boarding',
                'institution',
                'file'
            ])->where('userId', $userId)->first();

            if (!$studentProgram) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'Data pendaftaran tidak ditemukan.'
                ], 404);
            }

            if (!$studentProgram->personal || !$studentProgram->parent || !$studentProgram->program) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'Data pendaftaran belum lengkap. Mohon lengkapi data terlebih dahulu.'
                ], 400);
            }

            $registrationService = new RegistrationProofService();

            if (!$studentProgram->registration_number) {
                $studentProgram = $registrationService->generateRegistrationProof($studentProgram);
            }

            $data = $registrationService->getRegistrationProofData($studentProgram, $request->query('frontend_url'));
            $signedPath = $registrationService->generateSignedPdfFile($data, $studentProgram->institution);

            $filename = 'bukti-pendaftaran-' . ($studentProgram->registration_number ?? $userId) . '.pdf';

            return response()->download($signedPath, $filename)->deleteFileAfterSend(true);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Data pendaftaran tidak ditemukan.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    public function verifyRegistrationProof($token)
    {
        try {
            \Log::info('Verification request received', ['token' => $token]);

            $studentProgram = StudentProgram::with([
                'personal',
                'institution',
                'program',
                'boarding'
            ])
                ->where('registration_token', $token)
                ->first();

            \Log::info('Student program lookup result', [
                'token' => $token,
                'found' => $studentProgram ? 'yes' : 'no',
                'student_id' => $studentProgram?->id
            ]);

            if (!$studentProgram) {
                return response([
                    'status' => 'error',
                    'statusMessage' => 'Token verifikasi tidak valid atau sudah kadaluarsa.'
                ], 404);
            }

            return response([
                'status' => 'success',
                'data' => [
                    'registration_number' => $studentProgram->registration_number,
                    'student_name' => $studentProgram->personal->name ?? 'N/A',
                    'birth_place' => $studentProgram->personal->birthPlace ?? 'N/A',
                    'birth_date' => $studentProgram->personal->birthDate ?? null,
                    'institution_name' => $studentProgram->institution->name ?? 'N/A',
                    'program_name' => $studentProgram->program->name ?? 'N/A',
                    'boarding_name' => $studentProgram->boarding->name ?? '-',
                    'registration_date' => $studentProgram->registration_generated_at instanceof \Carbon\Carbon
                        ? $studentProgram->registration_generated_at->format('Y-m-d')
                        : ($studentProgram->created_at instanceof \Carbon\Carbon
                            ? $studentProgram->created_at->format('Y-m-d')
                            : $studentProgram->created_at),
                    'signer_name' => $studentProgram->institution->head ?? 'Kepala Madrasah',
                    'status' => 'verified'
                ]
            ]);

        } catch (Exception $e) {
            \Log::error('Error verifying registration', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return response([
                'status' => 'error',
                'statusMessage' => 'Terjadi kesalahan saat memverifikasi token.'
            ], 500);
        }
    }

    public function verifyRegistration(string $token)
    {
        try {
            \Log::info('Verification request received', ['token' => $token]);

            // Find student program by registration token
            $studentProgram = StudentProgram::with([
                'personal',
                'program',
                'boarding',
                'institution'
            ])
            ->where('registration_token', $token)
            ->first();

            \Log::info('Student program lookup result', [
                'token' => $token,
                'found' => $studentProgram ? 'yes' : 'no',
                'student_id' => $studentProgram?->id
            ]);

            if (!$studentProgram) {
                return response()->json([
                    'status' => 'error',
                    'statusMessage' => 'Token verifikasi tidak valid atau sudah kadaluarsa.'
                ], 404);
            }

            // Return verification data
            return response()->json([
                'status' => 'success',
                'data' => [
                    'registration_number' => $studentProgram->registration_number,
                    'student_name' => $studentProgram->personal->fullname ?? 'N/A',
                    'birth_place' => $studentProgram->personal->birthplace ?? 'N/A',
                    'birth_date' => $studentProgram->personal->birthdate ?? null,
                    'institution_name' => $studentProgram->institution->name ?? 'N/A',
                    'program_name' => $studentProgram->program->name ?? 'N/A',
                    'boarding_name' => $studentProgram->boarding->name ?? '-',
                    'registration_date' => $studentProgram->registration_generated_at?->format('Y-m-d') ?? $studentProgram->created_at->format('Y-m-d'),
                    'signer_name' => $studentProgram->institution->head ?? 'Kepala Madrasah',
                    'status' => 'verified'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error verifying registration', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'statusMessage' => 'Terjadi kesalahan saat memverifikasi token.'
            ], 500);
        }
    }
    public function boardingReport(Request $request)
    {
        try {
            $program = StudentProgram::with(['personal', 'parent', 'address', 'program', 'boarding', 'verification', 'institution', 'room'])
                ->whereYearid($request->yearId)
                ->when($request->institutionId, function ($query) use ($request) {
                    $query->whereInstitutionid($request->institutionId);
                })
                ->when($request->boardingId, function ($query) use ($request) {
                    $query->whereBoardingid($request->boardingId);
                })
                ->when($request->gender, function ($query) use ($request) {
                    $query->whereHas('personal', function ($q) use ($request) {
                        $q->where('gender', $request->gender);
                    });
                })
                ->get()->toArray();

            $result = collect($program)->map(function ($item) {
                return [
                    'registration_number' => $item['registration_number'] ?? '-',
                    'name' => $item['personal']['name'],
                    'birthPlace' => $item['personal']['birthPlace'],
                    'birthDate' => $item['personal']['birthDate'],
                    'guardName' => $item['parent']['guardName'],
                    'address' => $item['address']['street'],
                    'boarding' => $item['boarding']['name'],
                    'room' => $item['room']['name'] ?? 'Belum diatur',
                ];
            });

            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => $result
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function exportBoardingReport(Request $request)
    {
        try {
            $program = StudentProgram::with(['personal', 'parent', 'address', 'program', 'boarding', 'verification', 'institution', 'room'])
                ->whereYearid($request->yearId)
                ->when($request->institutionId, function ($query) use ($request) {
                    $query->whereInstitutionid($request->institutionId);
                })
                ->when($request->boardingId, function ($query) use ($request) {
                    $query->whereBoardingid($request->boardingId);
                })
                ->when($request->gender, function ($query) use ($request) {
                    $query->whereHas('personal', function ($q) use ($request) {
                        $q->where('gender', $request->gender);
                    });
                })
                ->get();

            $filename = 'laporan-boarding-' . date('Y-m-d-His') . '.xlsx';
            return Excel::download(new BoardingStudentExport($program), $filename);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
}
