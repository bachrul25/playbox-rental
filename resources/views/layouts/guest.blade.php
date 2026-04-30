<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') &middot; {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #0b3d91 0%, #16a34a 100%); display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 440px; width: 100%; border-radius: 18px; border: 0; box-shadow: 0 10px 40px rgba(0,0,0,.18); }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot ?? '' }}
    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
