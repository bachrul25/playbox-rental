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

class PartnershipReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private Collection $reports) {}

    public function collection(): Collection
    {
        return $this->reports;
    }

    public function headings(): array
    {
        return ['Tanggal', 'Invoice', 'Mitra', 'PlayBox', 'Total Pendapatan', 'Biaya Staff', 'Net', 'Owner 50%', 'Cafe 50%'];
    }

    public function map($row): array
    {
        return [
            $row->report_date?->format('d-m-Y'),
            $row->rental?->invoice_number,
            $row->partner?->cafe_name,
            optional($row->rental?->playbox)->name,
            Rupiah::format($row->total_income),
            Rupiah::format($row->staff_cost),
            Rupiah::format($row->net_income),
            Rupiah::format($row->owner_share),
            Rupiah::format($row->partner_share),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Laporan Kerjasama';
    }
}
