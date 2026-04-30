<div>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Manajemen User</h4>
            <small class="text-muted">Kelola akun admin, owner, dan mitra</small>
        </div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-person-plus"></i> Tambah User</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-7">
                    <input wire:model.live.debounce.350ms="search" class="form-control" placeholder="Cari nama / email...">
                </div>
                <div class="col-md-5">
                    <select wire:model.live="roleFilter" class="form-select">
                        <option value="">Semua Role</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                        <option value="mitra">Mitra</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Mitra Terhubung</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td class="fw-semibold">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge bg-info-subtle text-info-emphasis text-uppercase">{{ $u->role }}</span></td>
                            <td>{{ $u->partner?->cafe_name ?? '-' }}</td>
                            <td class="text-end">
                                <button wire:click="openEdit({{ $u->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                <button wire:click="confirmDelete({{ $u->id }})" class="btn btn-sm btn-outline-danger" @disabled($u->id === auth()->id())><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada user.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $users->links() }}</div>
        </div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);" wire:keydown.escape="closeModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit="save">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $editingId ? 'Edit User' : 'Tambah User' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                                    <input wire:model.defer="name" class="form-control @error('name') is-invalid @enderror">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" wire:model.defer="email" class="form-control @error('email') is-invalid @enderror">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password @if (!$editingId)<span class="text-danger">*</span>@else <small class="text-muted">(Kosongkan jika tidak diubah)</small>@endif</label>
                                    <input type="password" wire:model.defer="password" class="form-control @error('password') is-invalid @enderror">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select wire:model.live="role" class="form-select">
                                        <option value="admin">Admin</option>
                                        <option value="owner">Owner</option>
                                        <option value="mitra">Mitra</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Mitra @if ($role === 'mitra')<span class="text-danger">*</span>@endif</label>
                                    <select wire:model.defer="partner_id" class="form-select @error('partner_id') is-invalid @enderror" @disabled($role !== 'mitra')>
                                        <option value="">-- Pilih Mitra --</option>
                                        @foreach ($partners as $p)
                                            <option value="{{ $p->id }}">{{ $p->cafe_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
