<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;

class StudentVerification extends Model
{

    protected $table = "student_verifications";
    protected $fillable = [
        'id',
        'userId',
        'twins',
        'twinsName',
        'graduate',
        'domicile',
        'student',
        'teacherSon',
        'sibling',
        'siblingInstitution',
        'siblingName',
        'createdBy',
        'updatedBy',
    ];
}
