<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Institution\AccountRequest;
use Exception;
use Illuminate\Http\Request;
use App\Models\Institution\Account;
use App\Http\Resources\Institution\AccountResource;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        try {
            $accounts = new Account();
            $accounts = $request->has('institutionId') ? $accounts->whereInstitutionid($request->institutionId) : $accounts;
            $accounts = $request->has('method') ? $accounts->whereMethod($request->method) : $accounts;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => AccountResource::collection($accounts->get())
            ]);
        } catch (Exception $e) {
            return response([
                'success' => 'error',
                'successMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(AccountRequest $request)
    {
        try {
            return ($account  = Account::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Rekening Berhasil Dibuat',
                    'result' => new AccountResource($account)
                ]) : throw new Exception('Data Rekening Gagal Dibuat');
        } catch (Exception $e) {
            return response([
                'success' => 'error',
                'successMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Account $account)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new AccountResource($account)
            ]);
        } catch (Exception $e) {
            return response([
                'success' => 'error',
                'successMessage' => $e->getMessage(),
            ], 500);
        }
    }
}
