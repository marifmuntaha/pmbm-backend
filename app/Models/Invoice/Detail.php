<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detail extends Model
{
    protected $table = 'invoiceDetails';
    protected $fillable = [
        'id',
        'invoiceId',
        'productId',
        'name',
        'price',
        'discount',
        'amount',
        'createdBy',
        'updatedBy',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Invoice::class, 'invoiceId', 'id');
    }
}
