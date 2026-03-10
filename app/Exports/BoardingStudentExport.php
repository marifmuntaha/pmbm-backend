<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BoardingStudentExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($row): array
    {
        return [
            $row->registration_number ?? '-',
            $row->personal->name,
            $row->personal->birthPlace . ', ' . $row->personal->birthDate,
            $row->personal->gender == 1 ? 'Laki-laki' : 'Perempuan',
            $row->parent->guardName,
            $row->address->street,
            $row->institution->surname ?? '-',
            $row->boarding->name,
            $row->room->name ?? 'Belum diatur',
        ];
    }

    public function headings(): array
    {
        return [
            'No. Pendaftaran',
            'Nama Lengkap',
            'TTL',
            'Gender',
            'Wali',
            'Alamat',
            'Lembaga',
            'Program Boarding',
            'Kamar',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
