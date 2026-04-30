<?php

namespace App\Exports;

use App\Support\Rupiah;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PrivateReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private Collection $reports) {}

    public function collection(): Collection
    {
        return $this->reports;
    }

    public function headings(): array
    {
        return ['Tanggal', 'Invoice', 'PlayBox', 'Total Pendapatan', 'Maintenance 20%', 'Owner 80%'];
    }

    public function map($row): array
    {
        return [
            $row->report_date?->format('d-m-Y'),
            $row->rental?->invoice_number,
            optional($row->rental?->playbox)->name,
            Rupiah::format($row->total_income),
            Rupiah::format($row->maintenance_amount),
            Rupiah::format($row->owner_profit),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Laporan Pribadi';
    }
}
