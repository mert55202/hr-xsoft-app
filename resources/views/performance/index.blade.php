@extends('layouts.app')

@section('title', 'Performans')

@section('content')
    <h1 class="h3 mb-3">Performans</h1>
    <form class="app-panel mb-3" method="post" action="{{ route('performance.reviews.store') }}">
        @csrf
        <div class="row g-2">
            <div class="col-md-4">
                <select class="form-select" name="employee_id" required>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}">{{ $person->fullName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><input class="form-control" name="period" placeholder="2026-10" required></div>
            <div class="col-md-5"><input class="form-control" name="body" placeholder="Gözlem" required></div>
        </div>
        <button class="btn btn-primary mt-2" type="submit">Değerlendirme</button>
    </form>
    <form class="row g-2 mb-3" method="post" action="{{ route('performance.competencies.store') }}">
        @csrf
        <div class="col-md-5"><input class="form-control" name="name" placeholder="Yetkinlik" required></div>
        <div class="col-md-5">
            <select class="form-select" name="position_id">
                <option value="">Pozisyon yok</option>
                @foreach ($positions as $position)
                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-secondary" type="submit">Ekle</button></div>
    </form>
    @foreach ($reviews as $review)
        <article class="app-panel mb-2">
            <p class="mb-1 fw-semibold">{{ $review->employee->fullName() }} · {{ $review->collar }} · {{ $review->period }}</p>
            <p class="mb-1">{{ $review->body }}</p>
            <p class="mb-0">{{ $review->status === 'approved' ? 'Onaylı' : 'Taslak' }}</p>
            @if ($review->status !== 'approved' && auth()->user()->can('update', $review->employee))
                <form method="post" action="{{ route('performance.reviews.approve', $review) }}">
                    @csrf
                    <button class="btn btn-primary btn-sm" type="submit">Onayla</button>
                </form>
            @endif
        </article>
    @endforeach
@endsection
