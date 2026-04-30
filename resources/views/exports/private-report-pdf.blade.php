@php use App\Support\Rupiah; @endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Pribadi</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h2 { margin: 0 0 4px; }
    .muted { color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #cbd5e1; padding: 6px 8px; }
    th { background: #f1f5f9; text-align: left; }
    .totals td { font-weight: bold; background: #fafafa; }
    .right { text-align: right; }
</style>
</head>
<body>
    <h2>Laporan Rental Pribadi</h2>
    <div class="muted">Periode: {{ $period_label }}</div>
    <div class="muted">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>PlayBox</th>
                <th class="right">Total Pendapatan</th>
                <th class="right">Maintenance 20%</th>
                <th class="right">Owner 80%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $r)
                <tr>
                    <td>{{ $r->report_date?->format('d-m-Y') }}</td>
                    <td>{{ $r->rental?->invoice_number }}</td>
                    <td>{{ optional($r->rental?->playbox)->name }}</td>
                    <td class="right">{{ Rupiah::format($r->total_income) }}</td>
                    <td class="right">{{ Rupiah::format($r->maintenance_amount) }}</td>
                    <td class="right">{{ Rupiah::format($r->owner_profit) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center; padding:12px;">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="3" class="right">TOTAL ({{ $summary['count'] }} transaksi)</td>
                <td class="right">{{ Rupiah::format($summary['total_income']) }}</td>
                <td class="right">{{ Rupiah::format($summary['total_maintenance']) }}</td>
                <td class="right">{{ Rupiah::format($summary['total_owner_profit']) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
