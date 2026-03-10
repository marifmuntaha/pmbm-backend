<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginStoreRequest;
use App\Http\Requests\StoreRegisterRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendWhatsAppMessage;
use App\Models\User;
use App\Models\User\Otp;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(StoreRegisterRequest $request)
    {
        try {
            return ($user = User::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Pendaftaran berhasil.',
                    'result' => [
                        'user' => $user->toArray()
                    ]
                ]) : throw new Exception('Pendaftaran gagal.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }

    public function login(LoginStoreRequest $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);
            $user = User::where('email', $credentials['email'])->first();
            if ($user) {
                try {
                    $decryptPassword = Crypt::decryptString($user->password);
                    if ($decryptPassword === $credentials['password']) {
                        Auth::login($user);
                        if ($user->phone_verified_at !== null) {
                            return response([
                                'status' => 'success',
                                'statusMessage' => 'Berhasil masuk, anda akan dialihkan dalam 2 detik.',
                                'result' => [
                                    'user' => $user->toArray(),
                                    'token' => $user->createToken($request->user()->email)->plainTextToken
                                ]
                            ]);
                        } else {
                            return response([
                                'status' => 'success',
                                'statusMessage' => 'Berhasil masuk, anda akan dialihkan dalam 2 detik.',
                                'result' => [
                                    'user' => $user->toArray()
                                ]
                            ]);
                        }
                    } else {
                        throw new Exception('Nama pengguna/kata sandi salah.', 401);
                    }
                } catch (DecryptException $e) {
                    throw new Exception($e->getMessage(), 401);
                }
            } else {
                throw new Exception('Nama pengguna/kata sandi salah.', 401);
            }
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 401);
        }
    }

    public function phoneVerify(Request $request)
    {
        try {
            $otp = Otp::whereEmail($request->email)->whereDate('expires_at', '>=', Carbon::now())->first();
            if ($otp === null) {
                throw new Exception('Kode Verifikasi salah/kadaluarsa.', 442);
            } else {
                if ($request->otp == $otp->token) {
                    $user = User::whereEmail($request->email)->first();
                    $user->password = Crypt::decryptString($user->password);
                    $user->phone_verified_at = Carbon::now();
                    $user->save();
                    $otp->delete();
                    return response([
                        'status' => 'success',
                        'statusMessage' => 'Berhasil masuk, anda akan dialihkan dalam 2 detik.',
                        'statusCode' => 200,
                        'result' => [
                            'user' => $user->toArray(),
                            'token' => $user->createToken($user->email)->plainTextToken
                        ]
                    ]);
                } else {
                    throw new Exception('Kode Verifikasi salah/kadaluarsa.', 442);
                }
            }
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }

    public function getPhoneVerify(Request $request)
    {
        try {
            $otp = Otp::whereEmail($request->email)->get();
            $otp->map(function ($item) {
                $item->delete();
            });
            $code = [
                'email' => $request->email,
                'token' => mt_rand(100000, 999999),
                'expires_at' => Carbon::now()->addMinutes(10),
            ];
            $user = User::whereEmail($request->email)->first();
            $otp = Otp::create($code);
            $message = "*PMBM YAYASAN DARUL HIKMAH*" . PHP_EOL . PHP_EOL;
            $message .= "Halo, $user->name." . PHP_EOL;
            $message .= "Kode OTP Anda adalah: *$otp->token*" . PHP_EOL;
            $message .= "Kode ini berlaku selama 10 menit. Jangan berikan kode ini kepada siapapun." . PHP_EOL;
            SendWhatsAppMessage::dispatch($user->phone, $message);
            return response([
                'status' => 'success',
                'statusMessage' => 'Kode Verifikasi berhasil dikirim ke nomer anda',
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }

    public function logout(Request $request)
    {
        try {
            $accessToken = $request->user()->currentAccessToken();
            if ($accessToken instanceof PersonalAccessToken) {
                $accessToken->delete();
                return response([
                    'status' => 'success',
                    'statusMessage' => 'Berhasil keluar.',
                ]);
            } else {
                return throw new Exception('Terjadi kesalahan server', 500);
            }
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new UserResource($request->user('sanctum'))
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }
}
