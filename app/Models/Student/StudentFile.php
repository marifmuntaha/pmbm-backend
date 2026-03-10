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
        return $this->attributes['filePhoto'] != null ? url(Storage::url($this->attributes['filePhoto'])) : '';
    }

    public function getFileKkAttribute(): string
    {
        return $this->attributes['fileKk'] != null ? url(Storage::url($this->attributes['fileKk'])) : '';
    }

    public function getFileKtpAttribute(): string
    {
        return $this->attributes['fileKtp'] != null ? url(Storage::url($this->attributes['fileKtp'])) : '';
    }

    public function getFileAktaAttribute(): string
    {
        return $this->attributes['fileAkta'] != null ? url(Storage::url($this->attributes['fileAkta'])) : '';
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
