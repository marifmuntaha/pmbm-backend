<?php

namespace App\Models\Master;

use App\Models\Institution\Program;
use App\Models\Invoice;
use App\Models\Invoice\Detail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $table = 'master_products';
    protected $fillable = [
        'id',
        'yearId',
        'institutionId',
        'name',
        'surname',
        'price',
        'gender',
        'programId',
        'isBoarding',
        'boardingId',
        'createdBy',
        'updatedBy',
    ];

    protected function casts(): array
    {
        return [
            'gender' => 'int',
            'programId' => 'int',
            'boardingId' => 'int',
            'price' => 'int',
        ];
    }

    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class, 'institutionId', 'institutionId');
    }

    public function details(): HasMany
    {
        return $this->hasMany(Detail::class, 'productId', 'id');
    }

    public function program(): HasOne
    {
        return $this->hasOne(Program::class, 'id', 'programId');
    }

    public function boarding(): HasOne
    {
        return $this->hasOne(Boarding::class, 'id', 'boardingId');
    }
}
