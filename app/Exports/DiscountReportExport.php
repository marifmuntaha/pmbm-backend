<?php

namespace App\Exports;

use App\Models\Invoice\Detail;
use App\Models\Master\Year;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DiscountReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $yearId;
    protected $institutionId;

    public function __construct($yearId = null, $institutionId = null)
    {
        $this->yearId = $yearId;
        $this->institutionId = $institutionId;
    }

    public function collection()
    {
        $query = Detail::with(['invoice.personal', 'invoice.institution'])
            ->where('discount', '>', 0)
            ->whereHas('invoice', function ($q) {
                if ($this->yearId) {
                    $q->where('yearId', $this->yearId);
                }
                if ($this->institutionId) {
                    $q->where('institutionId', $this->institutionId);
                }
            });

        return $query->get();
    }

    public function headings(): array
    {
        $yearName = '';
        if ($this->yearId) {
            $year = Year::find($this->yearId);
            $yearName = $year ? $year->name : '';
        }

        return [
            ['LAPORAN POTONGAN ' . ($yearName ? 'TAHUN PELAJARAN ' . $yearName : '')],
            [
                'ID',
                'Nama Siswa',
                'Item Pembayaran',
                'Jumlah Potongan',
                'Keterangan',
                'Tanggal Dibuat',
            ]
        ];
    }

    public function map($detail): array
    {
        return [
            $detail->id,
            $detail->invoice->personal->name ?? '-',
            $detail->name ?? '-',
            'Rp ' . number_format($detail->discount, 0, ',', '.'),
            'Potongan untuk ' . ($detail->name ?? 'item'),
            $detail->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Merge title cells
                $sheet->mergeCells('A1:' . $highestColumn . '1');

                // Add borders to the entire table
                $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
