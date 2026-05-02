<?php

namespace App\Models\Student;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function studentParent(): HasOne
    {
        return $this->hasOne(StudentParent::class, 'userId', 'userId');
    }

    public function studentAddress(): HasOne
    {
        return $this->hasOne(StudentAddress::class, 'userId', 'userId');
    }
    public function studentProgram(): HasOne
    {
        return $this->hasOne(StudentProgram::class, 'userId', 'userId');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'userId', 'userId');
    }
}
