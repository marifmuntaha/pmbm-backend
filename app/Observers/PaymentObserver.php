<?php

namespace App\Observers;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Account\Transaction;
use App\Models\Institution;
use App\Models\Institution\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\LogService;
use App\Services\Payment\PaymentFactory;
use App\Services\PaymentReceiptService;
use Exception;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        try {
            $payment->load(['institution']);
            $user = User::with('personal')->find($payment->userId);
            if ($user && $user->phone) {
                $paymentReceiptService = new PaymentReceiptService();
                $payment = $paymentReceiptService->generateReceipt($payment, $user);
                $receiptData = $paymentReceiptService->getReceiptData($payment);
                $receiptPath = $paymentReceiptService->generateSignedPdfFile($receiptData);
                $institution = Institution::find($payment->institutionId);
                $institutionName = $institution ? $institution->surname : 'Yayasan Darul Hikmah Menganti';
                $message = "*PMBM YAYASAN DARUL HIKMAH*" . PHP_EOL . PHP_EOL;
                $message .= "Assalamualaikum wr wb." . PHP_EOL;
                $message .= "ini adalah pesan otomatis dari sistem" . PHP_EOL . PHP_EOL;
                $message .= "Halo, {$user->personal->name}." . PHP_EOL;
                $message .= "Pembayaran Anda sebesar *Rp. " . number_format($payment->amount) . "* telah kami terima." . PHP_EOL . PHP_EOL;
                $message .= "Metode: " . ($payment->method == 1 ? 'Cash / Tunai' : 'Online Transfer') . PHP_EOL;
                $message .= "ID Transaksi: {$payment->transaction_id}" . PHP_EOL;
                $message .= "Waktu: {$payment->transaction_time}" . PHP_EOL . PHP_EOL;
                $message .= "Terlampir adalah bukti pembayaran Anda." . PHP_EOL . PHP_EOL;
                $message .= "Terima kasih telah melakukan pembayaran. Silakan cek status pendaftaran Anda di aplikasi." . PHP_EOL . PHP_EOL;

                $message .= "Wassalamualaikum wr wb" . PHP_EOL . PHP_EOL;
                $message .= "-Panitia PMBM {$institutionName}-" . PHP_EOL;

                SendWhatsAppMessage::dispatch($user->phone, $message, null, $message, $receiptPath);
                LogService::transaction("Pembayaran diterima: Rp. " . number_format($payment->amount) . " dari {$user->personal->name}", [
                    'paymentId' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                ]);
            }

            $invoice = Invoice::find($payment->invoiceId);
            if ($invoice && $user && $user->phone) {
                $sisaTagihan = $invoice->amount - $payment->amount;

                if ($sisaTagihan > 0) {
                    try {
                        $service = PaymentFactory::create();
                        $adminFee = 3500;
                        $totalAmount = (int)$sisaTagihan + $adminFee;

                        $params = [
                            'transaction_details' => [
                                'order_id' => $invoice->reference . '-SISA-' . time(),
                                'gross_amount' => $totalAmount,
                            ],
                            'customer_details' => [
                                'first_name' => $user->personal->name ?? '',
                                'email' => $user->email,
                                'phone' => $user->phone,
                            ],
                            'item_details' => [
                                [
                                    'id' => 'PMBM-' . $invoice->yearId . '-' . $invoice->institutionId,
                                    'price' => (int)$sisaTagihan,
                                    'quantity' => 1,
                                    'name' => 'Sisa Tagihan PMBM Yayasan Darul Hikmah',
                                ],
                                [
                                    'id' => 'ADMIN-FEE',
                                    'price' => $adminFee,
                                    'quantity' => 1,
                                    'name' => 'Biaya Admin',
                                ]
                            ],
                        ];

                        $result = $service->createTransaction($params);
                        $paymentLink = $result['redirect_url'] ?? '';

                        if (!empty($result['token'])) {
                            Invoice::where('id', $invoice->id)->update(['link' => $result['token']]);
                        }
                    } catch (Exception $e) {
                        LogService::error('Gagal generate Midtrans link untuk sisa tagihan: ' . $e->getMessage(), [
                            'invoiceId' => $invoice->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    $institution = Institution::find($payment->institutionId);
                    $institutionName = $institution ? $institution->surname : 'Yayasan Darul Hikmah Menganti';
                    $reminder = "*PMBM YAYASAN DARUL HIKMAH*" . PHP_EOL . PHP_EOL;
                    $reminder .= "Assalamualaikum wr wb." . PHP_EOL;
                    $reminder .= "ini adalah pesan otomatis dari sistem" . PHP_EOL . PHP_EOL;
                    $reminder .= "Pembayaran Anda telah kami catat, namun tagihan Anda belum lunas." . PHP_EOL;
                    $reminder .= "Sisa tagihan yang harus dibayarkan adalah sebesar *Rp. " . number_format($sisaTagihan, 0, ',', '.') . "*." . PHP_EOL . PHP_EOL;

                    if ($paymentLink) {
                        $reminder .= "Silakan kunjungi tautan berikut untuk melakukan pembayaran sisa tagihan:" . PHP_EOL;
                        $reminder .= $paymentLink . PHP_EOL . PHP_EOL;
                    } else {
                        $reminder .= "Silakan masuk ke aplikasi untuk melanjutkan pembayaran sisa tagihan." . PHP_EOL . PHP_EOL;
                    }
                    $reminder .= "Terima kasih." . PHP_EOL . PHP_EOL;
                    $reminder .= "Wassalamualaikum wr wb" . PHP_EOL . PHP_EOL;
                    $reminder .= "-Panitia PMBM {$institutionName}-" . PHP_EOL;

                    SendWhatsAppMessage::dispatch($user->phone, $reminder);
                }
            }

            $account = Account::whereInstitutionid($payment->institutionId)
                ->whereMethod($payment->method)->first();
            $methodLabel = $payment->method === 1 ? 'Tunai' : 'Online';
            Transaction::create([
                'yearId' => $payment->yearId,
                'institutionId' => $payment->institutionId,
                'accountId' => $account->id,
                'paymentId' => $payment->id,
                'name' => "Pembayaran {$methodLabel} a.n {$payment->personal->name} ({$payment->transaction_id})",
                'credit' => 0,
                'debit' => $payment->amount,
            ]);
            if ($payment->method === 2) {
                Transaction::create([
                    'yearId' => $payment->yearId,
                    'institutionId' => $payment->institutionId,
                    'accountId' => $account->id,
                    'paymentId' => $payment->id,
                    'name' => "Biaya Transaksi via Midtrans ({$payment->transaction_id})",
                    'credit' => 4500,
                    'debit' => 0,
                ]);
            }

        } catch (Exception $e) {
            LogService::error('PaymentObserver Error: ' . $e->getMessage(), [
                'paymentId' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

