<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentAchievement extends Model
{
    protected $table = 'student_achievements';
    protected $fillable = [
        'userId',
        'level',
        'type',
        'champ',
        'name',
        'file',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'int',
            'champ' => 'int',
            'type' => 'int'
        ];
    }
    protected function file(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => url(Storage::url($value)),
            set: fn (string $value) => Str::chopStart($value, url(Storage::url(''))),
        );
    }
}
