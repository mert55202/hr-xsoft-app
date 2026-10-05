@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-3">
        <h1 class="h3 mb-1">{{ $title }}</h1>
        <p class="text-secondary mb-0">{{ $text }}</p>
    </div>

    @if (! empty($cards))
        <div class="row g-3">
            @foreach ($cards as $card)
                <div class="col-12 col-sm-6">
                    @if (! empty($card['href']))
                        <a class="app-module" href="{{ $card['href'] }}">
                            <i class="bi {{ $card['icon'] }}" aria-hidden="true"></i>
                            <h2 class="h5">{{ $card['title'] }}</h2>
                            <p class="text-secondary mb-0">{{ $card['text'] }}</p>
                        </a>
                    @else
                        <article class="app-module">
                            <i class="bi {{ $card['icon'] }}" aria-hidden="true"></i>
                            <h2 class="h5">{{ $card['title'] }}</h2>
                            <p class="text-secondary mb-0">{{ $card['text'] }}</p>
                        </article>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <section class="app-empty">
            <span class="app-empty-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
            <h2 class="h5">Liste boş</h2>
            <p class="text-secondary mb-0">Kayıtlar bu alanda kart olarak duracak.</p>
        </section>
    @endif
@endsection
