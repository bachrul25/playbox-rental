<div>
    <div class="card login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width:64px;height:64px;">
                <i class="bi bi-controller fs-2"></i>
            </div>
            <h4 class="mt-3 fw-bold">PlayBox Rental</h4>
            <p class="text-muted small mb-0">Sistem Manajemen Rental PlayBox Pribadi & Kerjasama</p>
        </div>

        <form wire:submit="login">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input wire:model.defer="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="admin@playbox.com" autofocus>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input wire:model.defer="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-check mb-3">
                <input wire:model="remember" class="form-check-input" type="checkbox" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <button class="btn btn-primary w-100 py-2" type="submit">
                <span wire:loading.remove wire:target="login"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</span>
                <span wire:loading wire:target="login"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
            </button>
        </form>

        <hr class="my-4">
        <div class="small text-muted">
            <strong>Akun demo:</strong>
            <ul class="mb-0 ps-3">
                <li>admin@playbox.com / password</li>
                <li>owner@playbox.com / password</li>
                <li>mitra@playbox.com / password</li>
            </ul>
        </div>
    </div>
</div>
