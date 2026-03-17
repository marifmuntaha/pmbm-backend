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