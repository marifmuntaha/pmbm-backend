<?php


use App\Http\Controllers\PaymentController;
use App\Models\Payment;
use App\Models\User\Otp;
use App\Services\PaymentReceiptService;
use App\Services\WhatsAppService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('testing/waha', function () {
    $user = \App\Models\User::find(1);
    $user->notify(new \App\Notifications\NewUserNotification());
});
Route::get('testing/date', function () {
    return \Illuminate\Support\Carbon::now()->format('Y-m-d H:i:s');
});
Route::get('testing/otp', function () {
    $otp = Otp::whereEmail('wikramawardhana@gmail.com')->whereDate('expires_at', '>=', Carbon::now())->first();
    return $otp;
});

Route::get('testing/storage', function () {
    return url(Storage::url('images/x6pmb0S0NzfjCn65E2hSlRWB8QEkHrcA0SrO7yrV.png'));
});


Route::get('testing/program', function () {
    $program = \App\Models\Master\Product::find(3);
    dd($program->boarding);
});

Route::get('testing/payment', [PaymentController::class, 'testing']);
Route::get('testing/whatsapp', function () {
    $whatsapp = new WhatsappService();
    $whatsapp->sendMessage('6285870059849', 'Ini adalah notifikasi WhatsApp');
});
