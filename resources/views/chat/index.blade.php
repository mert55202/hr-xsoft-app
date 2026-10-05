@extends('layouts.app')

@section('title', 'Sohbet')

@section('content')
    <h1 class="h3 mb-3">Sohbet</h1>
    <p class="text-secondary">Alt ağaç ve doğrudan birinci amir. Genel oda yok.</p>
    @forelse ($partners as $partner)
        <a class="app-panel d-block mb-2 text-decoration-none" href="{{ route('chat.show', $partner) }}">{{ $partner->name }}</a>
    @empty
        <p>Yazışabileceğin kimse yok.</p>
    @endforelse
@endsection
