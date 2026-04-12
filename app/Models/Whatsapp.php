<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Whatsapp extends Model
{
    protected $fillable = ['id', 'institutionId', 'device', 'active', 'status'];

    public function institution(): HasOne
    {
        return $this->hasOne(Institution::class, 'id', 'institutionId');
    }
}
