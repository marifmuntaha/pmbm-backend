<?php

namespace App\Models\Account;

use App\Models\Institution;
use App\Models\Master\Year;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $table = 'account_transactions';
    protected $fillable = [
        'id',
        'yearId',
        'institutionId',
        'accountId',
        'paymentId',
        'name',
        'credit',
        'debit',
        'balance',
        'createdBy',
        'updatedBy',
    ];

    public function year(): HasOne
    {
        return $this->hasOne(Year::class, 'id', 'yearId');
    }

    public function institution(): HasOne
    {
        return $this->hasOne(Institution::class, 'id', 'institutionId');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'id', 'paymentId');
    }
}
