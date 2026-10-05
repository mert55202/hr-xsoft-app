@extends('layouts.app')

@section('title', 'Şema')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">Şema</h1>
            <p class="text-secondary mb-0">Ağacın tamamı. Görebildiğin isim karta gider.</p>
        </div>
        <div class="d-flex gap-2">
            @if (auth()->user()->isAdmin())
                <a class="btn btn-outline-secondary" href="{{ route('positions.index') }}">Pozisyonlar</a>
            @endif
            <button class="btn btn-outline-secondary" type="button" data-org-zoom-out>Uzaklaştır</button>
            <button class="btn btn-outline-secondary" type="button" data-org-zoom-in>Yakınlaştır</button>
        </div>
    </div>

    @include('positions._warning')

    @if ($roots->isEmpty())
        <section class="app-empty">
            <span class="app-empty-icon" aria-hidden="true"><i class="bi bi-diagram-3"></i></span>
            <h2 class="h5">Pozisyon yok</h2>
            <p class="text-secondary mb-0">Ağaç, ilk pozisyonla çizilir.</p>
        </section>
    @else
        <div class="org-board" data-org-board>
            <ul class="org-tree" data-org-tree>
                @foreach ($roots as $root)
                    @include('positions.node', ['position' => $root, 'children' => $children])
                @endforeach
            </ul>
        </div>
    @endif
@endsection
