<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\TransactionRequest;
use App\Http\Resources\Account\TransactionResource;
use App\Models\Account\Transaction;
use App\Models\Institution\Account;
use Exception;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $transactions = new Transaction();
            $transactions = $request->has('yearId') ? $transactions->whereYearid($request->yearId) : $transactions;
            $transactions = $request->has('institutionId') ? $transactions->whereInstitutionid($request->institutionId) : $transactions;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => TransactionResource::collection($transactions->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(TransactionRequest $request)
    {
        try {
            return ($transaction = Transaction::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Transaksi berhasil disimpan.',
                    'result' => new TransactionResource($transaction)
                ]) : throw new Exception('Transaksi gagal disimpan.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function dashboard(Request $request)
    {
        try {
            $account = new Account();
            $account = $request->has('institutionId') ? $account->whereInstitutionid($request->institutionId) : $account;
            $cash = clone $account;
            $nonCash = clone $account;
            $credit = clone $account;
            $debit = clone $account;
            $result = [
                'balance' => $account->sum('balance'),
                'cash' => $cash->whereMethod(1)->sum('balance'),
                'nonCash' => $nonCash->whereMethod(2)->sum('balance'),
                'credit' => $credit->sum('credit'),
                'debit' => $debit->sum('debit'),
            ];
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
}
