<?php

namespace App\Exports;

use App\Models\Payment;
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

class PaymentReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
        $query = Payment::with(['personal', 'invoice'])->orderBy('created_at', 'desc');

        if ($this->yearId) {
            $query->where('yearId', $this->yearId);
        }

        if ($this->institutionId) {
            $query->where('institutionId', $this->institutionId);
        }

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
            ['LAPORAN PEMBAYARAN ' . ($yearName ? 'TAHUN PELAJARAN ' . $yearName : '')],
            [
                'ID',
                'Nama Siswa',
                'Nomor Invoice',
                'Jumlah',
                'Metode Pembayaran',
                'Status',
                'Tanggal Transaksi',
            ]
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->personal->name ?? '-',
            $payment->invoice->reference ?? '-',
            'Rp ' . number_format($payment->amount, 0, ',', '.'),
            $payment->method == 1 ? 'Cash' : 'Midtrans',
            $payment->status,
            $payment->transaction_time ? date('d/m/Y H:i', strtotime($payment->transaction_time)) : '-',
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
