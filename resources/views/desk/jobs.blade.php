@extends('layouts.app')

@section('title', 'İş profili')

@section('content')
    <h1 class="h3 mb-3">İş profili</h1>
    @foreach ($positions as $position)
        <article class="app-panel mb-2">
            <p class="fw-semibold mb-1">{{ $position->name }}</p>
            <p class="mb-0 text-secondary">{{ $position->department?->name ?? 'Departman yok' }} · {{ $position->collar->label() }} · norm {{ $position->headcount }}</p>
        </article>
    @endforeach
@endsection
