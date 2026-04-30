@php use App\Support\Rupiah; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Manajemen Biaya</h4>
            <small class="text-muted">Catat biaya maintenance, perawatan, kerusakan, dan staff</small>
        </div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Tambah Biaya</button>
    </div>

    <div class="row g-3 mb-3">
        @php $palette = ['maintenance' => '#0b3d91', 'perawatan' => '#0ea5e9', 'kerusakan' => '#ef4444', 'staff' => '#16a34a', 'lainnya' => '#f59e0b']; @endphp
        @foreach (['maintenance' => 'Maintenance', 'perawatan' => 'Perawatan', 'kerusakan' => 'Kerusakan', 'staff' => 'Staff', 'lainnya' => 'Lainnya'] as $key => $label)
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <small class="text-uppercase text-muted">{{ $label }}</small>
                        <div class="fs-5 fw-bold" style="color: {{ $palette[$key] }}">{{ Rupiah::format($totals[$key] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input wire:model.live.debounce.350ms="search" class="form-control" placeholder="Cari deskripsi...">
                </div>
                <div class="col-md-2">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="perawatan">Perawatan</option>
                        <option value="kerusakan">Kerusakan</option>
                        <option value="staff">Staff</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3"><input type="date" wire:model.live="dateFrom" class="form-control"></div>
                <div class="col-md-3"><input type="date" wire:model.live="dateTo" class="form-control"></div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Tanggal</th><th>Tipe</th><th>PlayBox</th><th>Mitra</th><th>Nominal</th><th>Keterangan</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    @forelse ($expenses as $e)
                        <tr>
                            <td>{{ $e->expense_date->format('d M Y') }}</td>
                            <td><span class="badge bg-light text-dark">{{ ucfirst($e->type) }}</span></td>
                            <td>{{ $e->playbox?->code ?? '-' }}</td>
                            <td>{{ $e->partner?->cafe_name ?? '-' }}</td>
                            <td class="fw-semibold">{{ Rupiah::format($e->amount) }}</td>
                            <td>{{ $e->description }}</td>
                            <td class="text-end">
                                <button wire:click="openEdit({{ $e->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                <button wire:click="confirmDelete({{ $e->id }})" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada biaya tercatat.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $expenses->links() }}</div>
        </div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);" wire:keydown.escape="closeModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit="save">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $editingId ? 'Edit Biaya' : 'Tambah Biaya' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" wire:model.defer="expense_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jenis Biaya <span class="text-danger">*</span></label>
                                    <select wire:model.defer="type" class="form-select">
                                        <option value="maintenance">Maintenance</option>
                                        <option value="perawatan">Perawatan</option>
                                        <option value="kerusakan">Kerusakan</option>
                                        <option value="staff">Staff Penunggu</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nominal <span class="text-danger">*</span></label>
                                    <input type="number" wire:model.defer="amount" min="0" class="form-control @error('amount') is-invalid @enderror">
                                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PlayBox (opsional)</label>
                                    <select wire:model.defer="playbox_id" class="form-select">
                                        <option value="">-- Tidak terkait --</option>
                                        @foreach ($playboxOptions as $pb)
                                            <option value="{{ $pb->id }}">{{ $pb->code }} - {{ $pb->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mitra (opsional)</label>
                                    <select wire:model.defer="partner_id" class="form-select">
                                        <option value="">-- Tidak terkait --</option>
                                        @foreach ($partnerOptions as $p)
                                            <option value="{{ $p->id }}">{{ $p->cafe_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <textarea wire:model.defer="description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
