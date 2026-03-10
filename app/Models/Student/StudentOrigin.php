<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;

class StudentOrigin extends Model
{
    protected $table = 'student_origins';
    protected $fillable = [
        'userId',
        'name',
        'npsn',
        'address',
        'createdBy',
        'updatedBy',
    ];
}
