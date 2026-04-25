<?php

namespace App\Observers\Account;

use App\Models\Account\Transaction;
use App\Models\Institution\Account;
use App\Models\System\Log;
use App\Services\LogService;

class TransactionObserver
{
    private int $balance = 0;

    public function creating(Transaction $transaction): void
    {
        $lastTransaction = Transaction::latest('created_at')->first();
        if ($lastTransaction) {
            $transaction->balance = ($lastTransaction?->balance ?? 0) + ($transaction->debit ?? 0) - ($transaction->credit ?? 0);
        } else {
            $transaction->balance = $transaction->debit;
        }
    }

    public function created(Transaction $transaction): void
    {
        $acc = Account::whereInstitutionid($transaction->institutionId)
            ->whereMethod($transaction->payment->method);
        $account = $acc->first();
        $credit = $account->credit + $transaction->credit;
        $debit = $account->debit + $transaction->debit;
        $balance = ($account->debit + $transaction->debit) - ($account->credit + $transaction->credit);
        $acc->update([
            'credit' => $credit,
            'debit' => $debit,
            'balance' => $balance,
        ]);


    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
