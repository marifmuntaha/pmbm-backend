<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\User;
use App\Jobs\SendWhatsAppMessage;
use App\Services\LogService;
use App\Services\Payment\PaymentFactory;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        $prefix = "INV-PMB.{$invoice->institutionId}";
        $lastInvoice = $invoice->whereYearid($invoice->yearId)
            ->whereInstitutionid($invoice->institutionId)
            ->where('reference', 'LIKE', "{$prefix}%")
            ->orderByRaw('LENGTH(reference) DESC')
            ->orderByDesc('reference')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            $lastSequence = (int) substr($lastInvoice->reference, strlen($prefix));
            $sequence = $lastSequence + 1;
        }
        $formattedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        $invoice->reference = "{$prefix}{$formattedSequence}";
    }
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $user = User::with('personal')->find($invoice->userId);
        if ($user && $user->phone) {
            $paymentLink = '';
            
            try {
                $service = PaymentFactory::create();
                $adminFee = 3500;
                $totalAmount = (int)$invoice->amount + $adminFee;

                $params = [
                    'transaction_details' => [
                        'order_id' => $invoice->reference . '-' . time(),
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
                            'price' => (int)$invoice->amount,
                            'quantity' => 1,
                            'name' => 'PMBM Yayasan Darul Hikmah Menganti',
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
                    // Simpan token ke dalam database tanpa memicu endless observer loop
                    Invoice::where('id', $invoice->id)->update(['link' => $result['token']]);
                }
            } catch (Exception $e) {
                LogService::error('Gagal generate Midtrans link saat buat invoice: ' . $e->getMessage(), [
                    'invoiceId' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
                // Tetap kirim WA meskipun gagal buat link
            }

            $message = "*PMBM YAYASAN DARUL HIKMAH*" . PHP_EOL . PHP_EOL;
            $message .= "Halo, {$user->personal->name}." . PHP_EOL;
            $message .= "Pendaftaran Anda telah terverifikasi." . PHP_EOL . PHP_EOL;
            $message .= "Tagihan sebesar *Rp. " . number_format($invoice->amount, 0, ',', '.') . "* telah dibuat." . PHP_EOL;
            
            if ($paymentLink) {
                $message .= "Kunjungi tautan berikut untuk melakukan pembayaran:" . PHP_EOL;
                $message .= $paymentLink . PHP_EOL . PHP_EOL;
            } else {
                $message .= "Silakan masuk ke aplikasi untuk melakukan pembayaran." . PHP_EOL;
            }
            
            $message .= "Terima kasih." . PHP_EOL;

            SendWhatsAppMessage::dispatch($user->phone, $message);
            LogService::log("Tagihan dibuat: Rp. " . number_format($invoice->amount) . " untuk {$user->personal->name}", 'info', [
                'invoiceId' => $invoice->id,
                'reference' => $invoice->reference
            ]);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        //
    }
}
