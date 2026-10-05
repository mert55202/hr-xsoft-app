@extends('layouts.app')

@section('title', $partner->name)

@section('content')
    <h1 class="h3 mb-3">{{ $partner->name }}</h1>
    <div data-chat="{{ route('chat.show', $partner) }}" data-after="{{ $messages->last()?->id ?? 0 }}">
        @foreach ($messages as $message)
            <p class="mb-1"><strong>{{ $message->sender->name }}:</strong> {{ $message->body }}</p>
        @endforeach
    </div>
    <form class="mt-3" method="post" action="{{ route('chat.store', $partner) }}">
        @csrf
        <label class="visually-hidden" for="body">Mesaj</label>
        <textarea class="form-control mb-2" id="body" name="body" rows="3" required></textarea>
        <button class="btn btn-primary" type="submit">Gönder</button>
    </form>
@endsection
