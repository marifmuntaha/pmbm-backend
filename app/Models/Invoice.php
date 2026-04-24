<?php

namespace App\Models;

use App\Models\Invoice\Detail;
use App\Models\Student\StudentAddress;
use App\Models\Student\StudentPersonal;
use App\Models\Student\StudentProgram;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $fillable = [
        'id',
        'yearId',
        'institutionId',
        'userId',
        'reference',
        'name',
        'amount',
        'dueDate',
        'status',
        'link',
        'createdBy',
        'updatedBy',
    ];

    public $timestamps = true;

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'userId');
    }
    public function details(): HasMany
    {
        return $this->hasMany(Detail::class, 'invoiceId', 'id' );
    }

    public function personal(): HasOne
    {
        return $this->hasOne(StudentPersonal::class, 'userId', 'userId' );
    }

    public function address(): HasOne
    {
        return $this->hasOne(StudentAddress::class, 'userId', 'userId' );
    }

    public function program(): HasOne
    {
        return $this->hasOne(StudentProgram::class, 'userId', 'userId' );
    }

    public function institution(): HasOne
    {
        return $this->hasOne(Institution::class, 'id', 'institutionId' );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoiceId', 'id');
    }
}
