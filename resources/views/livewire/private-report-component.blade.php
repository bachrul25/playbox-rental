@php use App\Support\Rupiah; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Laporan Rental Pribadi</h4>
            <small class="text-muted">Bagi hasil 20% maintenance & 80% keuntungan owner</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-danger" wire:click="exportPdf"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
            <button class="btn btn-success" wire:click="exportExcel"><i class="bi bi-file-earmark-excel"></i> Excel</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="btn-group flex-wrap" role="group">
                    @foreach (['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan', 'tahunan' => 'Tahunan', 'kustom' => 'Kustom'] as $val => $label)
                        <button type="button" wire:click="setPeriod('{{ $val }}')" class="btn {{ $period === $val ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</button>
                    @endforeach
                </div>
                @if ($period === 'kustom')
                    <input type="date" wire:model.live="dateFrom" class="form-control" style="max-width:180px;">
                    <span>s.d.</span>
                    <input type="date" wire:model.live="dateTo" class="form-control" style="max-width:180px;">
                @endif
                <span class="ms-auto badge bg-light text-dark">Periode: {{ $rangeFrom->translatedFormat('d M Y') }} – {{ $rangeTo->translatedFormat('d M Y') }}</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card pb-card-stat p-3"><small class="text-muted">Jumlah Transaksi</small><div class="fs-5 fw-bold">{{ $summary['count'] }}</div></div></div>
        <div class="col-md-3"><div class="card pb-card-stat p-3"><small class="text-muted">Total Pendapatan</small><div class="fs-5 fw-bold">{{ Rupiah::format($summary['total_income']) }}</div></div></div>
        <div class="col-md-3"><div class="card pb-card-stat p-3"><small class="text-muted">Total Maintenance 20%</small><div class="fs-5 fw-bold text-danger">{{ Rupiah::format($summary['total_maintenance']) }}</div></div></div>
        <div class="col-md-3"><div class="card pb-card-stat p-3"><small class="text-muted">Total Owner 80%</small><div class="fs-5 fw-bold text-success">{{ Rupiah::format($summary['total_owner_profit']) }}</div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Tanggal</th><th>Invoice</th><th>PlayBox</th><th class="text-end">Total</th><th class="text-end">Maintenance</th><th class="text-end">Owner</th></tr></thead>
                    <tbody>
                    @forelse ($reports as $r)
                        <tr>
                            <td>{{ $r->report_date->format('d M Y') }}</td>
                            <td><code>{{ $r->rental?->invoice_number }}</code></td>
                            <td>{{ optional($r->rental?->playbox)->name }}</td>
                            <td class="text-end">{{ Rupiah::format($r->total_income) }}</td>
                            <td class="text-end text-danger">{{ Rupiah::format($r->maintenance_amount) }}</td>
                            <td class="text-end text-success fw-semibold">{{ Rupiah::format($r->owner_profit) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data laporan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $reports->links() }}</div>
        </div>
    </div>
</div>
