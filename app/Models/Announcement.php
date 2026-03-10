<?php

namespace App\Models;

use App\Models\Master\Year;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'yearId',
        'institutionId',
        'user_id',
        'title',
        'description',
        'type',
        'is_wa_sent',
        'createdBy',
        'updatedBy',
    ];

    protected $casts = [
        'is_wa_sent' => 'boolean',
    ];

    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class, 'yearId');
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institutionId');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updatedBy');
    }
}
