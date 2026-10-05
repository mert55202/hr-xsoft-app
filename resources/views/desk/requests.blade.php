@extends('layouts.app')

@section('title', 'Talep')

@section('content')
    <h1 class="h3 mb-3">Talep</h1>
    <form class="app-panel mb-3" method="post" action="{{ route('requests.store') }}">
        @csrf
        <label class="form-label" for="body">Talebin</label>
        <textarea class="form-control" id="body" name="body" rows="3" required></textarea>
        <button class="btn btn-primary mt-2" type="submit">Gönder</button>
    </form>
    @foreach ($rows as $row)
        <article class="app-panel mb-2">
            <p class="fw-semibold mb-1">{{ $row->employee->fullName() }}</p>
            <p class="mb-0">{{ $row->body }}</p>
        </article>
    @endforeach
@endsection
