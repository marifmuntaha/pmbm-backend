<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
    protected $table = 'student_parents';
    protected $fillable = [
        'id',
        'userId',
        'numberKk',
        'headFamily',
        'fatherStatus',
        'fatherName',
        'fatherNik',
        'fatherBirthPlace',
        'fatherBirthDate',
        'fatherStudy',
        'fatherJob',
        'fatherPhone',
        'motherStatus',
        'motherName',
        'motherNik',
        'motherBirthPlace',
        'motherBirthDate',
        'motherStudy',
        'motherJob',
        'motherPhone',
        'guardStatus',
        'guardName',
        'guardNik',
        'guardBirthPlace',
        'guardBirthDate',
        'guardStudy',
        'guardJob',
        'guardPhone',
        'createdBy',
        'updatedBy',
    ];

    protected function casts(): array
    {
        return [
            'fatherStatus' => 'int',
            'fatherStudy' => 'int',
            'fatherJob' => 'int',
            'motherStatus' => 'int',
            'motherStudy' => 'int',
            'motherJob' => 'int',
            'guardStatus' => 'int',
            'guardStudy' => 'int',
            'guardJob' => 'int',
        ];
    }
}
