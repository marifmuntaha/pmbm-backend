<?php

namespace App\Models;

use App\Models\Institution\Activity;
use App\Models\Institution\Program;
use App\Models\Master\Rule;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Institution extends Model
{
    protected $fillable = [
        'name',
        'surname',
        'tagline',
        'npsn',
        'nsm',
        'address',
        'phone',
        'email',
        'website',
        'head',
        'logo',
        'createdBy',
        'updatedBy',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(
            Activity::class,
            'institutionId',
            'id'
        );
    }

    public function programs(): HasMany
    {
        return $this->hasMany(
            Program::class,
            'institutionId',
            'id'
        );
    }

    protected function logo(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => url(Storage::url($value)),
        );
    }
    public function rules(): HasMany
    {
        return $this->hasMany(
            Rule::class,
            'institutionId',
            'id'
        );
    }
}
