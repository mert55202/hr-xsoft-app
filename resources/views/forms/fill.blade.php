@extends('layouts.app')

@section('title', $form->name)

@section('content')
    <h1 class="h3 mb-1">{{ $form->name }}</h1>
    @if ($form->description)
        <p class="text-secondary">{{ $form->description }}</p>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="post" action="{{ $action }}" @if (! $preview) enctype="multipart/form-data" @endif>
        @csrf
        @foreach ($form->sections as $section)
            <section class="app-panel mb-3">
                <h2 class="h5">{{ $section->title }}</h2>
                @if ($section->description)
                    <p class="text-secondary">{{ $section->description }}</p>
                @endif
                @foreach ($section->questions as $question)
                    @include('forms._fill_question', ['question' => $question, 'preview' => $preview])
                @endforeach
            </section>
        @endforeach

        @unless ($preview)
            <button class="btn btn-primary" type="submit">Gönder</button>
        @endunless
    </form>
@endsection
