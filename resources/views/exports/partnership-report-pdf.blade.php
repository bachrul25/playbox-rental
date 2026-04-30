@php use App\Support\Rupiah; @endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Kerjasama</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    h2 { margin: 0 0 4px; }
    .muted { color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #cbd5e1; padding: 5px 6px; }
    th { background: #f1f5f9; text-align: left; }
    .totals td { font-weight: bold; background: #fafafa; }
    .right { text-align: right; }
</style>
</head>
<body>
    <h2>Laporan Rental Kerjasama</h2>
    <div class="muted">Periode: {{ $period_label }}</div>
    <div class="muted">Mitra: {{ $partner_label }}</div>
    <div class="muted">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Mitra</th>
                <th>PlayBox</th>
                <th class="right">Pendapatan</th>
                <th class="right">Staff</th>
                <th class="right">Net</th>
                <th class="right">Owner 50%</th>
                <th class="right">Cafe 50%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $r)
                <tr>
                    <td>{{ $r->report_date?->format('d-m-Y') }}</td>
                    <td>{{ $r->rental?->invoice_number }}</td>
                    <td>{{ $r->partner?->cafe_name }}</td>
                    <td>{{ optional($r->rental?->playbox)->name }}</td>
                    <td class="right">{{ Rupiah::format($r->total_income) }}</td>
                    <td class="right">{{ Rupiah::format($r->staff_cost) }}</td>
                    <td class="right">{{ Rupiah::format($r->net_income) }}</td>
                    <td class="right">{{ Rupiah::format($r->owner_share) }}</td>
                    <td class="right">{{ Rupiah::format($r->partner_share) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center; padding:12px;">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="4" class="right">TOTAL ({{ $summary['count'] }} transaksi)</td>
                <td class="right">{{ Rupiah::format($summary['total_income']) }}</td>
                <td class="right">{{ Rupiah::format($summary['total_staff_cost']) }}</td>
                <td class="right">{{ Rupiah::format($summary['total_net_income']) }}</td>
                <td class="right">{{ Rupiah::format($summary['total_owner_share']) }}</td>
                <td class="right">{{ Rupiah::format($summary['total_partner_share']) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
