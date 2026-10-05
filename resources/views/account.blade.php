@extends('layouts.app')

@section('title', 'Hesabım')

@section('content')
    <section class="app-panel">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 mb-4">
            <span class="app-avatar app-avatar-lg" aria-hidden="true">{{ auth()->user()->initials() }}</span>
            <div>
                <h1 class="h3 mb-1">{{ auth()->user()->name }}</h1>
                <span class="badge rounded-pill text-bg-light">{{ auth()->user()->role->label() }}</span>
            </div>
        </div>
        <dl class="row mb-4">
            <dt class="col-sm-3 text-secondary">E-posta</dt>
            <dd class="col-sm-9">{{ auth()->user()->email }}</dd>
            <dt class="col-sm-3 text-secondary">Rol</dt>
            <dd class="col-sm-9 mb-0">{{ auth()->user()->role->label() }}</dd>
        </dl>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-secondary" type="submit">Çıkış</button>
        </form>
    </section>
@endsection
