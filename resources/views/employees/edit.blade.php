@extends('layouts.app')

@section('title', $employee->fullName())

@section('content')
    <h1 class="h3 mb-3">{{ $employee->fullName() }}</h1>

    <section class="app-panel">
        <form method="post" action="{{ route('personnel.update', $employee) }}">
            @include('employees._form', ['method' => 'put', 'submit' => 'Kaydet'])
        </form>
    </section>
@endsection
