<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bugün') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>:root { --app-accent: {{ $company?->primary_color ?: '#0d6efd' }}; }</style>
</head>
<body>
    <div class="app-shell">
        @auth
        <aside class="app-sidebar d-none d-lg-flex">
            <a class="app-brand" href="{{ route('today') }}">
                <span class="app-mark" aria-hidden="true">{{ mb_strtoupper(mb_substr($company?->name ?? config('app.name'), 0, 1)) }}</span>
                <span class="text-truncate">{{ $company?->name ?? config('app.name') }}</span>
            </a>
            <nav class="app-sidenav" aria-label="Ana menü">
                @include('layouts.nav')
            </nav>
            <a class="app-user app-sidebar-user" href="{{ route('account') }}">
                <span class="app-avatar" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                <span class="text-truncate">{{ auth()->user()->name }}</span>
            </a>
        </aside>
        @endauth

        <div class="app-stage">
            <header class="app-topbar d-lg-none">
                <a class="app-brand" href="{{ route('today') }}">
                    <span class="app-mark" aria-hidden="true">{{ mb_strtoupper(mb_substr($company?->name ?? config('app.name'), 0, 1)) }}</span>
                    <span class="text-truncate">{{ $company?->name ?? config('app.name') }}</span>
                </a>
                @auth
                <a class="app-user" href="{{ route('account') }}">
                    <span class="app-avatar" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                </a>
                @endauth
            </header>

            <main class="app-main">
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    @auth
    <nav class="app-bottom-nav d-lg-none navbar fixed-bottom" aria-label="Ana menü">
        <div class="container-fluid px-0">
            <ul class="nav nav-fill w-100">
                @include('layouts.nav-items', ['bottom' => true])
            </ul>
        </div>
    </nav>
    @endauth
</body>
</html>
