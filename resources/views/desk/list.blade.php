@extends('layouts.app')

@section('title', $title)

@section('content')
    <h1 class="h3 mb-3">{{ $title }}</h1>
    <form class="app-panel mb-3" method="post" action="{{ $action }}">
        @csrf
        <div class="row g-2">
            @foreach ($fields as $name => $label)
                <div class="col-md-6">
                    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
                    @if (($date ?? null) === $name)
                        <input class="form-control" id="{{ $name }}" name="{{ $name }}" type="date" required>
                    @elseif ($name === 'body')
                        <textarea class="form-control" id="{{ $name }}" name="{{ $name }}" rows="2" required></textarea>
                    @else
                        <input class="form-control" id="{{ $name }}" name="{{ $name }}" required>
                    @endif
                </div>
            @endforeach
        </div>
        <button class="btn btn-primary mt-2" type="submit">Ekle</button>
    </form>
    @foreach ($rows as $row)
        <article class="app-panel mb-2">
            <p class="mb-0">{{ $row->title }} {{ $row->body ?? '' }} {{ $row->served_on?->format('d.m.Y') }} {{ $row->starts_on?->format('d.m.Y') }}</p>
        </article>
    @endforeach
@endsection
