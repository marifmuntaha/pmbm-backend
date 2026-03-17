<?php

namespace App\Observers;

use App\Jobs\SendWhatsAppMessage;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $message = "*PMBM YAYASAN DARUL HIKMAH*". PHP_EOL . PHP_EOL;
        if ($user->role == 4) {
            if ($user->phone_verified_at == null) {
                $code = mt_rand(100000, 999999);
                $user->otps()->create([
                    'email' => $user->email,
                    'token' => $code,
                    'expires_at' => Carbon::now()->addMinutes(10),
                ]);
                $message .= "Halo, $user->name." . PHP_EOL;
                $message .= "Kode OTP Anda adalah: *$code*" . PHP_EOL;
                $message .= "Kode ini berlaku selama 10 menit. Jangan berikan kode ini kepada siapapun." . PHP_EOL;
            } else {
                $message = $this->message($user);
            }
        }
        if ($user->phone && $message) {
            SendWhatsAppMessage::dispatch($user->phone, $message);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->role == 4) {
            if ($user->getOriginal('phone_verified_at') == null) {
                if ($user->phone) {
                    SendWhatsAppMessage::dispatch($user->phone, $this->message($user));
                }
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    private function message(User $user) : string
    {
        $message = "*PMBM YAYASAN DARUL HIKMAH*". PHP_EOL . PHP_EOL;
        $message .= "ini adalah pesan otomatis dari sistem." . PHP_EOL . PHP_EOL;
        $message .= "Selamat bergabung, $user->name." . PHP_EOL;
        $message .= "Nama Pengguna anda adalah: ". $user->email . PHP_EOL;
        $message .= "Kata Sandi adalah: " . Crypt::decryptString($user->password) . PHP_EOL;
        $message .= "Silahkan login ke aplikasi https://pmbm.darul-hikmah.sch.id/masuk untuk melengkapi pendaftaran." . PHP_EOL;
        $message .= "Jika terdapat kesulitan, silahkan menghubungi admin kami." . PHP_EOL;
        $message .= "Terima kasih." . PHP_EOL;
        return $message;
    }

    /**
     * Handle the User "force deleted" event.
     */
//    public function forceDeleted(User $user): void
//    {
//        //
//    }
}
