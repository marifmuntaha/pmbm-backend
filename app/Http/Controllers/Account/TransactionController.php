<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\TransactionRequest;
use App\Http\Resources\Account\TransactionResource;
use App\Models\Account\Transaction;
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
}
