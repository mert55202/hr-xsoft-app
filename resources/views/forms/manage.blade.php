@extends('layouts.app')

@section('title', 'Anket')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">Anket</h1>
            <p class="text-secondary mb-0">Taslakta yapı değişir. Yayında soru ve şık silinmez.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('forms.create') }}">Yeni form</a>
    </div>

    @if ($forms->isEmpty())
        <section class="app-empty">
            <span class="app-empty-icon" aria-hidden="true"><i class="bi bi-ui-checks"></i></span>
            <h2 class="h5">Form yok</h2>
        </section>
    @else
        <div class="d-flex flex-column gap-2">
            @foreach ($forms as $form)
                <a class="app-panel text-decoration-none text-body" href="{{ route('forms.edit', $form) }}">
                    <h2 class="h6 mb-1">{{ $form->name }}</h2>
                    <p class="mb-0 text-secondary">{{ $form->identity->label() }} · {{ $form->status->label() }} · {{ $form->submissions_count }} yanıt</p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
