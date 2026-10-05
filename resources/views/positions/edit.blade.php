@extends('layouts.app')

@section('title', $position->name)

@section('content')
    <div class="mb-3">
        <h1 class="h3 mb-1">{{ $position->name }}</h1>
        <a href="{{ route('positions.index') }}">Pozisyonlara dön</a>
    </div>

    @include('positions._warning')

    <section class="app-panel mb-3">
        <form method="post" action="{{ route('positions.update', $position) }}">
            @include('positions._form', ['method' => 'put', 'submit' => 'Kaydet'])
        </form>
    </section>

    <form method="post" action="{{ route('positions.destroy', $position) }}">
        @csrf
        @method('delete')
        <button class="btn btn-outline-danger" type="submit">Sil</button>
    </form>
@endsection
