<?php

namespace App\Models\Institution;

use App\Models\Master\Year;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Activity extends Model
{
    protected $table = 'institution_activities';
    protected $fillable = ['yearId', 'institutionId', 'capacity', 'brochure', 'createdBy', 'updatedBy'];

    public function year(): hasOne
    {
        return $this->hasOne(Year::class, 'id', 'yearId');
    }

    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class, 'institutionId');
    }
    public function brochure(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => url(Storage::url($value)),
        );
    }
}
