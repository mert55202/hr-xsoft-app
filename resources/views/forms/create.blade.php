@extends('layouts.app')

@section('title', 'Yeni form')

@section('content')
    <h1 class="h3 mb-3">Yeni form</h1>
    <section class="app-panel">
        <form method="post" action="{{ route('forms.store') }}">
            @include('forms._meta', ['method' => 'post', 'submit' => 'Oluştur'])
        </form>
    </section>
@endsection
