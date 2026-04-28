<?php

namespace App\Models;

use App\Models\Student\StudentPersonal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'userId');
    }

    public function personal(): HasOne
    {
        return $this->hasOne(StudentPersonal::class, 'userId', 'userId');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoiceId');
    }

    public function institution(): HasOne
    {
        return $this->hasOne(Institution::class, 'id', 'institutionId');
    }
}
