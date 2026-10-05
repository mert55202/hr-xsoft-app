@extends('layouts.app')

@section('title', 'Sözlük')

@section('content')
    <h1 class="h3 mb-3">Sözlük</h1>
    <p class="text-secondary">{{ count($kinds) }} tip tek tabloda.</p>
    <form class="app-panel mb-3" method="post" action="{{ route('lookups.store') }}">
        @csrf
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label" for="kind">Tip</label>
                <select class="form-select" id="kind" name="kind">
                    @foreach ($kinds as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="name">Ad</label>
                <input class="form-control" id="name" name="name" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="unit">İzin birimi</label>
                <select class="form-select" id="unit" name="unit">
                    <option value="both">Gün ve saat</option>
                    <option value="day">Gün</option>
                    <option value="hour">Saat</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary" type="submit">Ekle</button>
            </div>
        </div>
    </form>
    @foreach ($rows as $row)
        <p class="mb-1">{{ $kinds[$row->kind] ?? $row->kind }} · {{ $row->name }}</p>
    @endforeach
@endsection
