@extends('layouts.app')

@section('title', 'Pozisyonlar')

@section('content')
    <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">Pozisyonlar</h1>
            <p class="text-secondary mb-0">Birinci amir başka bir pozisyondur. İkinci amir, onun üstünden gelir.</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('positions.chart') }}">Şema</a>
    </div>

    @include('positions._warning')

    <section class="app-panel mb-3">
        <h2 class="h5">Yeni pozisyon</h2>
        <form method="post" action="{{ route('positions.store') }}">
            @include('positions._form', ['method' => 'post', 'submit' => 'Ekle', 'position' => new \App\Models\Position])
        </form>
    </section>

    <div class="d-none d-md-block app-panel">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Pozisyon</th>
                    <th>Departman</th>
                    <th>Yaka</th>
                    <th>Birinci amir</th>
                    <th>İkinci amir</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($positions as $position)
                    <tr>
                        <td>{{ $position->name }}</td>
                        <td>{{ $position->department?->name ?? '—' }}</td>
                        <td>{{ $position->collar->label() }}</td>
                        <td>{{ $position->parent?->name ?? 'Kök' }}</td>
                        <td>{{ $position->secondManager()?->name ?? '—' }}</td>
                        <td class="text-end"><a href="{{ route('positions.edit', $position) }}">Düzenle</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Henüz pozisyon yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-md-none d-grid gap-3">
        @foreach ($positions as $position)
            <article class="app-panel">
                <h2 class="h5 mb-1">{{ $position->name }}</h2>
                <p class="mb-1">{{ $position->department?->name ?? 'Departman yok' }} · {{ $position->collar->label() }}</p>
                <p class="text-secondary mb-2">Amir: {{ $position->parent?->name ?? 'Kök' }}</p>
                <a href="{{ route('positions.edit', $position) }}">Düzenle</a>
            </article>
        @endforeach
    </div>
@endsection
