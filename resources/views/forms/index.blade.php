@extends('layouts.app')

@section('title', 'Formlar')

@section('content')
    <h1 class="h3 mb-1">Formlar</h1>
    <p class="text-secondary mb-3">Sana açık formlar. Kişisel bağlantı, kendi adresinden açılır.</p>

    @if ($forms->isEmpty())
        <section class="app-empty">
            <span class="app-empty-icon" aria-hidden="true"><i class="bi bi-ui-checks"></i></span>
            <h2 class="h5">Açık form yok</h2>
            <p class="text-secondary mb-0">Yayınlanan form burada durur.</p>
        </section>
    @else
        <div class="d-flex flex-column gap-2">
            @foreach ($forms as $form)
                <a class="app-panel text-decoration-none text-body" href="{{ route('forms.show', $form) }}">
                    <h2 class="h6 mb-1">{{ $form->name }}</h2>
                    <p class="mb-0 text-secondary">{{ $form->identity->label() }}</p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
