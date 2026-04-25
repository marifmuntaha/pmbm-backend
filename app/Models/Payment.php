<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'yearId',
        'institutionId',
        'userId',
        'invoiceId',
        'method',
        'status',
        'transaction_id',
        'transaction_time',
        'amount',
        'receipt_number',
        'receipt_token',
        'receipt_generated_at',
        'receipt_generated_by',
        'createdBy',
        'updatedBy',
    ];

    public $casts = [
        'method' => 'integer',
        'receipt_generated_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'id', 'userId');
    }

    public function personal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Student\StudentPersonal::class, 'userId', 'userId');
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoiceId');
    }

    public function institution(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Institution::class, 'id', 'institutionId');
    }
}
