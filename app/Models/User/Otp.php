<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $table = 'user_otps';
    protected $fillable = ['email', 'token', 'expires_at'];
    public $timestamps = false;
}
