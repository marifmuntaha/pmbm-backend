<?php

namespace App\Models\Institution;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $table = 'institution_periods';
    protected $fillable = [
        'id',
        'yearId',
        'institutionId',
        'name',
        'description',
        'start',
        'end',
        'createdBy',
        'updatedBy',
    ];
    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class, 'institutionId');
    }
}
