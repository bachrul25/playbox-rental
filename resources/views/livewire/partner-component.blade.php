<div>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Data Mitra / Cafe</h4>
            <small class="text-muted">Kelola mitra kerjasama PlayBox</small>
        </div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Tambah Mitra</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input wire:model.live.debounce.350ms="search" class="form-control" placeholder="Cari nama cafe / PJ / nomor HP...">
                    </div>
                </div>
                <div class="col-md-5">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr>
                        <th>Cafe</th><th>PJ</th><th>HP</th><th>Mulai</th><th>PlayBox</th><th>Status</th><th class="text-end">Aksi</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($partners as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->cafe_name }}</td>
                            <td>{{ $p->person_in_charge }}</td>
                            <td>{{ $p->phone }}</td>
                            <td>{{ optional($p->cooperation_start_date)->format('d M Y') ?? '-' }}</td>
                            <td>{{ $p->playboxes_count }}</td>
                            <td><span class="badge {{ $p->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ str_replace('_', ' ', $p->status) }}</span></td>
                            <td class="text-end">
                                <button wire:click="openEdit({{ $p->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                <button wire:click="confirmDelete({{ $p->id }})" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada mitra.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $partners->links() }}</div>
        </div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);" wire:keydown.escape="closeModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit="save">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $editingId ? 'Edit Mitra' : 'Tambah Mitra' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label">Nama Cafe <span class="text-danger">*</span></label>
                                    <input wire:model.defer="cafe_name" class="form-control @error('cafe_name') is-invalid @enderror">
                                    @error('cafe_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Penanggung Jawab <span class="text-danger">*</span></label>
                                    <input wire:model.defer="person_in_charge" class="form-control @error('person_in_charge') is-invalid @enderror">
                                    @error('person_in_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">No. HP <span class="text-danger">*</span></label>
                                    <input wire:model.defer="phone" class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Kerjasama</label>
                                    <input type="date" wire:model.defer="cooperation_start_date" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select wire:model.defer="status" class="form-select">
                                        <option value="aktif">Aktif</option>
                                        <option value="tidak_aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat</label>
                                    <textarea wire:model.defer="address" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan Kerjasama</label>
                                    <textarea wire:model.defer="note" class="form-control" rows="2"></textarea>
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
