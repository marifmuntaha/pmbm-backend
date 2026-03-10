<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StudentFile extends Model
{
    protected $table = 'student_files';
    protected $fillable = [
        'userId',
        'filePhoto',
        'fileKk',
        'fileKtp',
        'numberAkta',
        'fileAkta',
        'numberIjazah',
        'fileIjazah',
        'numberSkl',
        'fileSkl',
        'numberKip',
        'fileKip',
    ];
    protected $appends = ['filePhoto', 'fileKk', 'fileKtp', 'fileAkta', 'fileIjazah', 'fileSkl', 'fileKip'];

    public function getFilePhotoAttribute(): string
    {
        $file = $this->attributes['filePhoto'] ?? null;
        return $file ? url(Storage::url($file)) : '';
    }

    public function getFileKkAttribute(): string
    {
        $file = $this->attributes['fileKk'] ?? null;
        return $file ? url(Storage::url($file)) : '';
    }

    public function getFileKtpAttribute(): string
    {
        $file = $this->attributes['fileKtp'] ?? null;
        return $file ? url(Storage::url($file)) : '';
    }

    public function getFileAktaAttribute(): string
    {
        $file = $this->attributes['fileAkta'] ?? null;
        return $file ? url(Storage::url($file)) : '';
    }

    public function getFileIjazahAttribute(): string
    {
        $file = $this->attributes['fileIjazah'] ?? null;
        return $file ? url(Storage::url($file)) : '';
    }
    public function getFileSklAttribute(): string
    {
        $file =  $this->attributes['fileSkl'] ?? null;
        return $file ? url(Storage::url($file)) : '';
    }
    public function getFileKipAttribute(): string
    {
        $file =  $this->attributes['fileKip'] ?? null;
        return $file ? url(Storage::url($file)) : '';
    }
}
