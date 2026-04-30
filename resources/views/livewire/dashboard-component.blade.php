@php use App\Support\Rupiah; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Dashboard</h4>
            <small class="text-muted">Ringkasan operasional PlayBox Rental</small>
        </div>
    </div>

    <div class="row g-3">
        @php
            $cards = [
                ['label' => 'Pendapatan Hari Ini', 'value' => Rupiah::format($summary['todayIncome']), 'icon' => 'bi-cash-coin', 'bg' => '#16a34a'],
                ['label' => 'Pendapatan Bulan Ini', 'value' => Rupiah::format($summary['monthIncome']), 'icon' => 'bi-graph-up-arrow', 'bg' => '#0b3d91'],
                ['label' => 'Total Transaksi', 'value' => number_format($summary['totalRentals'], 0, ',', '.'), 'icon' => 'bi-receipt', 'bg' => '#0ea5e9'],
                ['label' => 'PlayBox Aktif', 'value' => number_format($summary['activePlayboxes'], 0, ',', '.'), 'icon' => 'bi-controller', 'bg' => '#7c3aed'],
                ['label' => 'Total Mitra/Cafe', 'value' => number_format($summary['totalPartners'], 0, ',', '.'), 'icon' => 'bi-shop', 'bg' => '#f59e0b'],
                ['label' => 'Pendapatan Pribadi', 'value' => Rupiah::format($summary['privateIncome']), 'icon' => 'bi-person-badge', 'bg' => '#0284c7'],
                ['label' => 'Pendapatan Kerjasama', 'value' => Rupiah::format($summary['partnershipIncome']), 'icon' => 'bi-people-fill', 'bg' => '#db2777'],
                ['label' => 'Biaya Maintenance', 'value' => Rupiah::format($summary['maintenanceCost']), 'icon' => 'bi-tools', 'bg' => '#ef4444'],
                ['label' => 'Keuntungan Owner', 'value' => Rupiah::format($summary['ownerProfit']), 'icon' => 'bi-piggy-bank', 'bg' => '#0f766e'],
            ];
        @endphp

        @foreach ($cards as $c)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card pb-card-stat p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon" style="background: {{ $c['bg'] }}"><i class="bi {{ $c['icon'] }}"></i></div>
                        <div>
                            <div class="text-muted small text-uppercase">{{ $c['label'] }}</div>
                            <div class="fs-5 fw-bold">{{ $c['value'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mt-2">
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Grafik Pendapatan Bulanan (12 bulan)</h6>
                    <canvas id="incomeChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Pribadi vs Kerjasama</h6>
                    <canvas id="comparisonChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Transaksi Terbaru</h6>
                <a href="{{ route('rentals') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-list"></i> Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-sm">
                    <thead><tr>
                        <th>Invoice</th><th>Tanggal</th><th>PlayBox</th><th>Tipe</th><th>Total</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($recent as $r)
                            <tr>
                                <td><code>{{ $r->invoice_number }}</code></td>
                                <td>{{ $r->rental_date->format('d M Y') }}</td>
                                <td>{{ $r->playbox?->name }}</td>
                                <td>
                                    <span class="badge {{ $r->rental_type === 'pribadi' ? 'bg-info-subtle text-info-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">
                                        {{ ucfirst($r->rental_type) }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ Rupiah::format($r->total_income) }}</td>
                                <td>
                                    <span class="badge {{ $r->payment_status === 'lunas' ? 'bg-success' : 'bg-secondary' }}">{{ str_replace('_', ' ', $r->payment_status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                const labels = @json($chart['labels']);
                const incomeData = @json($chart['income']);
                const privateData = @json($chart['private']);
                const partnershipData = @json($chart['partnership']);

                new Chart(document.getElementById('incomeChart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Pendapatan',
                            data: incomeData,
                            borderColor: '#0b3d91',
                            backgroundColor: 'rgba(11,61,145,.1)',
                            fill: true,
                            tension: .3,
                        }]
                    },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });

                new Chart(document.getElementById('comparisonChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Pribadi', data: privateData, backgroundColor: '#0ea5e9' },
                            { label: 'Kerjasama', data: partnershipData, backgroundColor: '#f59e0b' },
                        ]
                    },
                    options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
                });
            })();
        </script>
    @endpush
</div>
