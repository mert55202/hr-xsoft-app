@extends('layouts.app')

@section('title', 'Yeni personel')

@section('content')
    <h1 class="h3 mb-3">Yeni personel</h1>

    <section class="app-panel">
        <form method="post" action="{{ route('personnel.store') }}">
            @include('employees._form', ['method' => 'post', 'submit' => 'Kaydet'])
        </form>
    </section>
@endsection
