<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;

class StudentPersonal extends Model
{
    protected $table = 'student_personals';
    protected $fillable = [
        'id',
        'userId',
        'name',
        'nik',
        'nisn',
        'gender',
        'birthPlace',
        'birthDate',
        'phone',
        'birthNumber',
        'sibling',
        'createdBy',
        'updatedBy',
    ];

    protected function casts(): array
    {
        return [
            'gender' => 'int'
        ];
    }
}
