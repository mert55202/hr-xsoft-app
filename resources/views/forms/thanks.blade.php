@extends('layouts.app')

@section('title', 'Teşekkürler')

@section('content')
    <section class="app-panel">
        <h1 class="h3">Teşekkürler</h1>
        <p class="mb-0">{{ $form->thank_you ?: 'Yanıtın alındı.' }}</p>
    </section>
@endsection
