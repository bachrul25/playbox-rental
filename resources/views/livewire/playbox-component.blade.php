<div>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Data PlayBox</h4>
            <small class="text-muted">Kelola unit PlayBox pribadi maupun kerjasama</small>
        </div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Tambah PlayBox</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input wire:model.live.debounce.350ms="search" class="form-control" placeholder="Cari kode / nama / lokasi...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="ownershipFilter" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="pribadi">Pribadi</option>
                        <option value="kerjasama">Kerjasama</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="tersedia">Tersedia</option>
                        <option value="disewa">Disewa</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr>
                        <th>Kode</th><th>Nama</th><th>Tipe</th><th>Mitra</th><th>Lokasi</th><th>Status</th><th class="text-end">Aksi</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($playboxes as $p)
                        <tr>
                            <td><code>{{ $p->code }}</code></td>
                            <td class="fw-semibold">{{ $p->name }}</td>
                            <td>
                                <span class="badge {{ $p->ownership_type === 'pribadi' ? 'bg-info-subtle text-info-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">
                                    {{ ucfirst($p->ownership_type) }}
                                </span>
                            </td>
                            <td>{{ $p->partner?->cafe_name ?? '-' }}</td>
                            <td>{{ $p->location ?? '-' }}</td>
                            <td><span class="badge badge-status {{ $p->statusBadgeClass() }}">{{ str_replace('_', ' ', $p->status) }}</span></td>
                            <td class="text-end">
                                <button wire:click="openEdit({{ $p->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                <button wire:click="confirmDelete({{ $p->id }})" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data PlayBox.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $playboxes->links() }}</div>
        </div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);" wire:keydown.escape="closeModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit="save">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $editingId ? 'Edit PlayBox' : 'Tambah PlayBox' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Kode <span class="text-danger">*</span></label>
                                    <input wire:model.defer="code" class="form-control @error('code') is-invalid @enderror">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nama PlayBox <span class="text-danger">*</span></label>
                                    <input wire:model.defer="name" class="form-control @error('name') is-invalid @enderror">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipe Kepemilikan <span class="text-danger">*</span></label>
                                    <select wire:model.live="ownership_type" class="form-select">
                                        <option value="pribadi">Pribadi</option>
                                        <option value="kerjasama">Kerjasama</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mitra/Cafe @if ($ownership_type === 'kerjasama')<span class="text-danger">*</span>@endif</label>
                                    <select wire:model.defer="partner_id" class="form-select @error('partner_id') is-invalid @enderror" @disabled($ownership_type === 'pribadi')>
                                        <option value="">-- Pilih Mitra --</option>
                                        @foreach ($partners as $partner)
                                            <option value="{{ $partner->id }}">{{ $partner->cafe_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Lokasi</label>
                                    <input wire:model.defer="location" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model.defer="status" class="form-select">
                                        <option value="tersedia">Tersedia</option>
                                        <option value="disewa">Disewa</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="tidak_aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan Kondisi</label>
                                    <textarea wire:model.defer="condition_note" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="save"><i class="bi bi-save"></i> Simpan</span>
                                <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm"></span> Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
