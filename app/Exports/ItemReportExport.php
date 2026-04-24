<?php

namespace App\Exports;

use App\Models\Master\Product;
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

class ItemReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
        return Product::with(['invoice'])
            ->when($this->yearId, function ($query) {
                $query->where('yearId', $this->yearId);
            })
            ->when($this->institutionId, function ($query) {
                $query->where('institutionId', $this->institutionId);
            })
            ->get();
    }

    public function headings(): array
    {
        $yearName = '';
        if ($this->yearId) {
            $year = Year::find($this->yearId);
            $yearName = $year ? $year->name : '';
        }

        return [
            ['LAPORAN TAGIHAN PER ITEM ' . ($yearName ? 'TAHUN PELAJARAN ' . $yearName : '')],
            [
                'No',
                'Nama Item',
                'Saldo Tagihan',
                'Saldo Potongan',
                'Jumlah',
            ]
        ];
    }

    protected $rowNumber = 0;

    public function map($product): array
    {
        $this->rowNumber++;

        $invoice = $product->invoice->map(function ($item) use ($product) {
            return $item->details->where('productId', $product->id)->sum('price');
        })->sum();

        $discount = $product->invoice->map(function ($item) use ($product) {
            return $item->details->where('productId', $product->id)->sum('discount');
        })->sum();

        $net = $invoice - $discount;

        return [
            $this->rowNumber,
            $product->name,
            'Rp ' . number_format($invoice, 0, ',', '.'),
            'Rp ' . number_format($discount, 0, ',', '.'),
            'Rp ' . number_format($net, 0, ',', '.'),
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
