<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Institution\ActivityController;
use App\Http\Controllers\Institution\PeriodController;
use App\Http\Controllers\Institution\ProgramController as InstitutionProgramController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\Invoice\DetailController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Master\BoardingController;
use App\Http\Controllers\Master\DiscountController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\RoomController;
use App\Http\Controllers\Master\RuleController;
use App\Http\Controllers\Master\YearController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Payment\GatewayController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Student\AchievementController;
use App\Http\Controllers\Student\AddressController;
use App\Http\Controllers\Student\FileController;
use App\Http\Controllers\Student\OriginController;
use App\Http\Controllers\Student\ParentController;
use App\Http\Controllers\Student\PersonalController;
use App\Http\Controllers\Student\ProgramController as StudentProgramController;
use App\Http\Controllers\Student\VerificationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TestimonyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\System\IntegrationTestController;
use App\Http\Controllers\System\LogController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(callback: function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('phone-verify', [AuthController::class, 'phoneVerify']);
        Route::post('get-phone-verify', [AuthController::class, 'getPhoneVerify']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');
    });

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::prefix('master')->group(function () {
            Route::apiResource('year', YearController::class);
            Route::apiResource('boarding', BoardingController::class);
            Route::apiResource('product', ProductController::class);
            Route::apiResource('discount', DiscountController::class);
            Route::apiResource('room', RoomController::class);
            Route::apiResource('rule', RuleController::class);
        });
        Route::prefix('institution')->group(function () {
            Route::apiResource('activity', ActivityController::class);
            Route::apiResource('program', InstitutionProgramController::class);
            Route::apiResource('period', PeriodController::class);
        });
        Route::prefix('student')->group(function () {
            Route::get('registration-proof', [StudentController::class, 'generateRegistrationProof']);
            Route::get('treasurer', [StudentController::class, 'treasurer']);
            Route::get('invoice', [StudentController::class, 'invoice']);
            Route::get('dashboard', [StudentController::class, 'dashboard']);
            Route::get('boarding', [StudentController::class, 'boarding']);
            Route::get('boarding-report', [StudentController::class, 'boardingReport']);
            Route::get('boarding-report/export', [StudentController::class, 'exportBoardingReport']);
            Route::apiResource('personal', PersonalController::class);
            Route::apiResource('parent', ParentController::class);
            Route::apiResource('address', AddressController::class);
            Route::apiResource('program', StudentProgramController::class);
            Route::apiResource('origin', OriginController::class);
            Route::apiResource('achievement', AchievementController::class);
            Route::apiResource('file', FileController::class);
            Route::apiResource('verification', VerificationController::class);
        });
        Route::apiResource('invoice/detail', DetailController::class);
        Route::post('invoice/{invoice}/send-whatsapp', [InvoiceController::class, 'sendWhatsapp']);
        Route::apiResource('invoice', InvoiceController::class);
        Route::prefix('payment')->group(function () {
            Route::get('active-gateway', [\App\Http\Controllers\Payment\ActiveGatewayController::class, 'index']);
            Route::post('cash', [PaymentController::class, 'cash']);
            Route::get('download-all-receipts', [PaymentController::class, 'downloadAllReceipts']);
            Route::get('{id}/generate-receipt', [PaymentController::class, 'generateReceipt']);
            Route::get('{id}/download-receipt', [PaymentController::class, 'downloadReceipt']);
            Route::apiResource('gateway', GatewayController::class)->only(['index', 'update']);
        });
        Route::apiResource('announcement', AnnouncementController::class);
        Route::apiResource('payment', PaymentController::class);
        Route::apiResource('user', UserController::class);
    });
    Route::apiResource('schedule', ScheduleController::class);
    Route::apiResource('student', StudentController::class);
    Route::apiResource('institution', InstitutionController::class);
    Route::apiResource('testimony', TestimonyController::class);
    Route::prefix('public')->group(function () {
        Route::get('landing', [PublicController::class, 'landing']);
        Route::get('year', [PublicController::class, 'year'] );
        Route::get('rules', [PublicController::class, 'rules'] );
        Route::get('schedule', [PublicController::class, 'schedule'] );
    });
    Route::prefix('report')->group(function () {
        Route::get('/invoice', [ReportController::class, 'invoice'])->middleware('auth:sanctum');
        Route::get('/invoice/export', [ReportController::class, 'exportInvoiceReport'])->middleware('auth:sanctum');
        Route::get('/payment', [ReportController::class, 'payment'])->middleware('auth:sanctum');
        Route::get('/payment/export', [ReportController::class, 'exportPaymentReport'])->middleware('auth:sanctum');
        Route::get('/applicants', [ReportController::class, 'applicantReport'])->middleware('auth:sanctum');
        Route::get('/applicants/export', [ReportController::class, 'exportApplicantReport'])->middleware('auth:sanctum');
        Route::get('/discounts', [ReportController::class, 'discountReport'])->middleware('auth:sanctum');
        Route::get('/discounts/export', [ReportController::class, 'exportDiscountReport'])->middleware('auth:sanctum');
        Route::get('/stats', [ReportController::class, 'stats'])->middleware('auth:sanctum');
        Route::get('/operator/stats', [ReportController::class, 'operatorStats'])->middleware('auth:sanctum');
        Route::get('/admin/stats', [ReportController::class, 'adminStats'])->middleware('auth:sanctum');
    });
    
    Route::prefix('system')->middleware(['auth:sanctum', 'isAdmin'])->group(function () {
        Route::get('logs', [LogController::class, 'index']);
        Route::delete('logs/clear', [LogController::class, 'clear']);
        Route::delete('logs/{id}', [LogController::class, 'destroy']);

        Route::prefix('test')->group(function () {
            Route::post('whatsapp/message', [IntegrationTestController::class, 'testWhatsAppMessage']);
            Route::post('whatsapp/pdf', [IntegrationTestController::class, 'testWhatsAppPdf']);
            Route::post('pdf/signature', [IntegrationTestController::class, 'testPdfSignature']);
            Route::post('midtrans', [IntegrationTestController::class, 'testMidtrans']);
            Route::post('midtrans/callback', [IntegrationTestController::class, 'testMidtransCallback']);
        });
    });

    // System Update Route (Sudah ada tapi mari kita pindahkan atau biarkan - user minta log)
    Route::post('/system/update', [\App\Http\Controllers\SystemController::class, 'update'])->middleware('auth:sanctum');

    Route::post('/callback/{provider}', [PaymentController::class, 'callback']);
    Route::get('/verify-receipt/{token}', [PaymentController::class, 'verifyReceipt']);
    Route::get('/verify-registration/{token}', [StudentController::class, 'verifyRegistration']);
    Route::post('/student/{userId}/send-whatsapp', [StudentController::class, 'sendWhatsAppRegistrationProof']);
    Route::get('/student/verify/{token}', [StudentController::class, 'verifyRegistrationProof']);
});
