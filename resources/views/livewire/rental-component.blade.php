@php use App\Support\Rupiah; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Transaksi Rental</h4>
            <small class="text-muted">Catat transaksi rental PlayBox pribadi maupun kerjasama</small>
        </div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Transaksi Baru</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input wire:model.live.debounce.350ms="search" class="form-control" placeholder="Cari invoice / playbox...">
                </div>
                <div class="col-md-2">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="pribadi">Pribadi</option>
                        <option value="kerjasama">Kerjasama</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" wire:model.live="dateFrom" class="form-control" placeholder="Dari">
                </div>
                <div class="col-md-3">
                    <input type="date" wire:model.live="dateTo" class="form-control" placeholder="Sampai">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr>
                        <th>Invoice</th><th>Tanggal</th><th>PlayBox</th><th>Tipe</th><th>Mitra</th>
                        <th>Durasi</th><th>Total</th><th>Bayar</th><th>Status</th><th class="text-end">Aksi</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($rentals as $r)
                        <tr>
                            <td><code>{{ $r->invoice_number }}</code></td>
                            <td>{{ $r->rental_date->format('d M Y') }}</td>
                            <td>{{ $r->playbox?->name }}<br><small class="text-muted">{{ $r->playbox?->code }}</small></td>
                            <td><span class="badge {{ $r->rental_type === 'pribadi' ? 'bg-info-subtle text-info-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">{{ ucfirst($r->rental_type) }}</span></td>
                            <td>{{ $r->partner?->cafe_name ?? '-' }}</td>
                            <td>{{ rtrim(rtrim(number_format($r->duration, 2, ',', '.'), '0'), ',') }} jam</td>
                            <td class="fw-semibold">{{ Rupiah::format($r->total_income) }}</td>
                            <td>{{ strtoupper($r->payment_method) }}</td>
                            <td><span class="badge {{ $r->payment_status === 'lunas' ? 'bg-success' : 'bg-secondary' }}">{{ str_replace('_',' ',$r->payment_status) }}</span></td>
                            <td class="text-end">
                                <button wire:click="openEdit({{ $r->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                <button wire:click="confirmDelete({{ $r->id }})" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $rentals->links() }}</div>
        </div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);" wire:keydown.escape="closeModal">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit="save">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $editingId ? 'Edit Transaksi' : 'Transaksi Baru' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">PlayBox <span class="text-danger">*</span></label>
                                    <select wire:model.live="playbox_id" class="form-select @error('playbox_id') is-invalid @enderror">
                                        <option value="">-- Pilih PlayBox --</option>
                                        @foreach ($playboxOptions as $pb)
                                            <option value="{{ $pb->id }}">{{ $pb->code }} - {{ $pb->name }} ({{ $pb->ownership_type }})</option>
                                        @endforeach
                                    </select>
                                    @error('playbox_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                    <select wire:model.live="rental_type" class="form-select">
                                        <option value="pribadi">Pribadi</option>
                                        <option value="kerjasama">Kerjasama</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mitra/Cafe @if ($rental_type === 'kerjasama')<span class="text-danger">*</span>@endif</label>
                                    <select wire:model.defer="partner_id" class="form-select @error('partner_id') is-invalid @enderror" @disabled($rental_type === 'pribadi')>
                                        <option value="">-- Pilih Mitra --</option>
                                        @foreach ($partnerOptions as $p)
                                            <option value="{{ $p->id }}">{{ $p->cafe_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" wire:model.defer="rental_date" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Mulai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" wire:model.live="start_time" class="form-control @error('start_time') is-invalid @enderror">
                                    @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Selesai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" wire:model.live="end_time" class="form-control @error('end_time') is-invalid @enderror">
                                    @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tarif/Jam <span class="text-danger">*</span></label>
                                    <input type="number" wire:model.live="price_per_hour" class="form-control @error('price_per_hour') is-invalid @enderror" min="0">
                                    @error('price_per_hour') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Durasi (Jam)</label>
                                    <input class="form-control bg-light" value="{{ number_format($duration, 2, ',', '.') }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Total Pendapatan</label>
                                    <input class="form-control bg-light fw-bold" value="{{ Rupiah::format($totalIncome) }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Metode Bayar</label>
                                    <select wire:model.defer="payment_method" class="form-select">
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status Bayar</label>
                                    <select wire:model.defer="payment_status" class="form-select">
                                        <option value="lunas">Lunas</option>
                                        <option value="belum_lunas">Belum Lunas</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <div class="alert alert-light border">
                                        <h6 class="fw-bold mb-2"><i class="bi bi-calculator"></i> Pratinjau Bagi Hasil</h6>
                                        @if ($breakdown['mode'] === 'pribadi')
                                            <div class="row g-2">
                                                <div class="col-md-4"><small class="text-muted">Total Pendapatan</small><div class="fs-6 fw-semibold">{{ Rupiah::format($totalIncome) }}</div></div>
                                                <div class="col-md-4"><small class="text-muted">Maintenance (20%)</small><div class="fs-6 fw-semibold text-danger">{{ Rupiah::format($breakdown['maintenance']) }}</div></div>
                                                <div class="col-md-4"><small class="text-muted">Keuntungan Owner (80%)</small><div class="fs-6 fw-semibold text-success">{{ Rupiah::format($breakdown['owner_profit']) }}</div></div>
                                            </div>
                                        @else
                                            <div class="row g-2">
                                                <div class="col-md-3"><small class="text-muted">Total Pendapatan</small><div class="fs-6 fw-semibold">{{ Rupiah::format($totalIncome) }}</div></div>
                                                <div class="col-md-3"><small class="text-muted">Biaya Staff</small><div class="fs-6 fw-semibold text-danger">- {{ Rupiah::format($breakdown['staff_cost']) }}</div></div>
                                                <div class="col-md-2"><small class="text-muted">Net</small><div class="fs-6 fw-semibold">{{ Rupiah::format($breakdown['net_income']) }}</div></div>
                                                <div class="col-md-2"><small class="text-muted">Owner 50%</small><div class="fs-6 fw-semibold text-success">{{ Rupiah::format($breakdown['owner_share']) }}</div></div>
                                                <div class="col-md-2"><small class="text-muted">Cafe 50%</small><div class="fs-6 fw-semibold text-success">{{ Rupiah::format($breakdown['partner_share']) }}</div></div>
                                            </div>
                                            @if ($breakdown['deficit'])
                                                <div class="alert alert-warning mt-2 mb-0 py-2 small"><i class="bi bi-exclamation-triangle"></i> Pendapatan tidak mencukupi biaya staff penunggu (Rp800.000).</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea wire:model.defer="note" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
