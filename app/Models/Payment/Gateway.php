<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $table = 'payment_gateways';
    protected $fillable = [
        'provider',
        'is_active',
        'mode',
        'server_key',
        'client_key',
        'secret_key',
        'callback_token',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'encrypted_server_key' => 'encrypted',
        'mode' => 'int',

    ];
}
