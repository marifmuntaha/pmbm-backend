<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;

class StudentAddress extends Model
{
    protected $table = 'student_addresses';
    protected $fillable = [
        'id',
        'userId',
        'province',
        'city',
        'district',
        'village',
        'street',
        'rt',
        'rw',
        'postal',
        'createdBy',
        'updatedBy',
    ];
}
