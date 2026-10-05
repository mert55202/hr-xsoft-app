<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>:root { --app-accent: {{ $company?->primary_color ?: '#0d6efd' }}; }</style>
</head>
<body>
    <div class="login-screen">
        <section class="login-panel">
            <div>
                <p class="app-kicker mb-2">{{ config('app.name') }}</p>
                <h1 class="display-6 fw-bold">{{ $company?->name ?? 'Firma' }}</h1>
                <p class="mb-0 col-lg-8">{{ $company?->vision ?: 'Personel, izin ve firma işleri tek girişte.' }}</p>
            </div>
            <ul class="list-unstyled d-grid gap-2 mb-0">
                <li><i class="bi bi-people me-2" aria-hidden="true"></i>Personel kartları</li>
                <li><i class="bi bi-calendar2-check me-2" aria-hidden="true"></i>İzin ve bordro</li>
                <li><i class="bi bi-diagram-3 me-2" aria-hidden="true"></i>Organizasyon şeması</li>
            </ul>
        </section>
        <main class="login-form-wrap">
            <form method="post" action="{{ route('login') }}" class="login-card d-grid gap-3" novalidate>
                @csrf
                <div>
                    <h2 class="h4 mb-1">Giriş</h2>
                    <p class="text-secondary mb-0">E-posta adresinle devam et.</p>
                </div>
                <div>
                    <label class="form-label" for="email">E-posta</label>
                    <input class="form-control form-control-lg @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="form-label" for="password">Şifre</label>
                    <input class="form-control form-control-lg @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check">
                    <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                    <label class="form-check-label" for="remember">Beni hatırla</label>
                </div>
                <button class="btn btn-primary btn-lg" type="submit">Giriş</button>
            </form>
        </main>
    </div>
</body>
</html>
