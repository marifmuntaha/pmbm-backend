<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $table = 'master_discounts';
    protected $fillable = [
        'yearId',
        'institutionId',
        'productId',
        'name',
        'description',
        'price',
        'unit',
        'createdBy',
        'updatedBy',
    ];
}
