@extends('layouts.app')

@section('title', 'Bugün')

@section('content')
    <section class="app-hero mb-4">
        <p class="app-kicker">{{ now()->translatedFormat('j F Y, l') }}</p>
        <h1 class="h2 mb-2">Merhaba, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</h1>
        <p class="mb-0 col-lg-8">{{ $company->vision ?: $company->name.' için günün özeti burada toplanır.' }}</p>
    </section>

    @foreach ($notices as $notice)
        <p class="alert alert-info">{{ $notice->body }}</p>
    @endforeach

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <a class="app-tile" href="{{ route('personnel') }}">
                <span class="app-tile-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                <span>
                    <span class="d-block fw-semibold">Personel</span>
                    <span class="text-secondary">Kendin ve altındaki kadro</span>
                </span>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="app-tile" href="{{ route('hr') }}">
                <span class="app-tile-icon" aria-hidden="true"><i class="bi bi-briefcase"></i></span>
                <span>
                    <span class="d-block fw-semibold">İK</span>
                    <span class="text-secondary">İzin, bordro, anket</span>
                </span>
            </a>
        </div>
        @if (auth()->user()->isAdmin())
            <div class="col-12 col-md-4">
                <a class="app-tile" href="{{ route('company.edit') }}">
                    <span class="app-tile-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
                    <span>
                        <span class="d-block fw-semibold">Firma</span>
                        <span class="text-secondary">Kimlik ve vurgu rengi</span>
                    </span>
                </a>
            </div>
        @endif
    </div>
@endsection
