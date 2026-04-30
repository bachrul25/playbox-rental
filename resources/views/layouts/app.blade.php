<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PlayBox Rental') &middot; {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css">

    <style>
        :root {
            --pb-primary: #0b3d91;
            --pb-primary-dark: #082963;
            --pb-accent: #16a34a;
            --pb-bg: #f5f7fb;
        }
        body { background-color: var(--pb-bg); font-family: 'Segoe UI', system-ui, sans-serif; }
        .pb-sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, var(--pb-primary), var(--pb-primary-dark));
            color: #fff;
            position: fixed; top: 0; left: 0; bottom: 0;
            z-index: 1030;
            transition: transform .25s ease;
        }
        .pb-sidebar a.nav-link { color: #cdd9f0; padding: .65rem 1rem; border-radius: .5rem; margin-bottom: 4px; }
        .pb-sidebar a.nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .pb-sidebar a.nav-link.active { background: rgba(255,255,255,.15); color: #fff; font-weight: 600; }
        .pb-sidebar .brand { font-weight: 700; font-size: 1.25rem; letter-spacing: .5px; }
        .pb-content { margin-left: 260px; min-height: 100vh; }
        .pb-topbar { background: #fff; border-bottom: 1px solid #e5e7eb; padding: .75rem 1.25rem; }
        .pb-card-stat { border: 0; border-radius: 14px; box-shadow: 0 4px 18px rgba(11,61,145,.06); }
        .pb-card-stat .icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #fff; }
        .table thead { background: #f1f5fb; }
        .badge-status { font-weight: 500; padding: .35em .65em; border-radius: 999px; }
        .pb-toggle-btn { display: none; }
        @media (max-width: 991.98px) {
            .pb-sidebar { transform: translateX(-100%); }
            .pb-sidebar.show { transform: translateX(0); }
            .pb-content { margin-left: 0; }
            .pb-toggle-btn { display: inline-flex; }
        }
    </style>
    @livewireStyles
</head>
<body>
@auth
    @php
        $user = auth()->user();
        $role = $user->role;
        $isAdmin = $user->isAdmin();
        $isOwner = $user->isOwner();
        $isMitra = $user->isMitra();
    @endphp
    <aside class="pb-sidebar" id="pbSidebar">
        <div class="px-3 py-3 d-flex align-items-center gap-2 border-bottom border-light border-opacity-10">
            <i class="bi bi-controller fs-3"></i>
            <span class="brand">PlayBox Rental</span>
        </div>
        <nav class="p-3">
            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            @if ($isAdmin || $isOwner)
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('playboxes') ? 'active' : '' }}" href="{{ route('playboxes') }}">
                    <i class="bi bi-controller"></i> Data PlayBox
                </a>
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('partners') ? 'active' : '' }}" href="{{ route('partners') }}">
                    <i class="bi bi-shop"></i> Data Mitra/Cafe
                </a>
            @endif
            @if ($isAdmin)
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('rentals') ? 'active' : '' }}" href="{{ route('rentals') }}">
                    <i class="bi bi-cash-coin"></i> Transaksi Rental
                </a>
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('expenses') ? 'active' : '' }}" href="{{ route('expenses') }}">
                    <i class="bi bi-wallet2"></i> Biaya
                </a>
            @endif
            @if ($isAdmin || $isOwner)
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('reports.private') ? 'active' : '' }}" href="{{ route('reports.private') }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Laporan Pribadi
                </a>
            @endif
            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('reports.partnership') ? 'active' : '' }}" href="{{ route('reports.partnership') }}">
                <i class="bi bi-people"></i> Laporan Kerjasama
            </a>
            @if ($isAdmin)
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('users') ? 'active' : '' }}" href="{{ route('users') }}">
                    <i class="bi bi-person-gear"></i> Manajemen User
                </a>
            @endif
            <hr class="border-light border-opacity-25">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="nav-link d-flex align-items-center gap-2 w-100 text-start bg-transparent border-0">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </nav>
    </aside>

    <div class="pb-content">
        <div class="pb-topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light pb-toggle-btn" type="button" onclick="document.getElementById('pbSidebar').classList.toggle('show')">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <div class="text-muted small">Selamat datang,</div>
                    <strong>{{ $user->name }}</strong>
                    <span class="badge bg-info-subtle text-info-emphasis text-uppercase ms-1">{{ $role }}</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small d-none d-md-inline">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
        </div>

        <main class="p-3 p-md-4">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
@else
    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('toast', (payload) => {
            const data = Array.isArray(payload) ? payload[0] : payload;
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2800,
                timerProgressBar: true,
                icon: data?.type ?? 'success',
                title: data?.message ?? 'Berhasil'
            });
        });
        Livewire.on('confirm-delete', (payload) => {
            const data = Array.isArray(payload) ? payload[0] : payload;
            Swal.fire({
                title: data?.title ?? 'Hapus data?',
                text: data?.text ?? 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch(data.event, { id: data.id });
                }
            });
        });
    });
</script>
@livewireScripts
@stack('scripts')
</body>
</html>
