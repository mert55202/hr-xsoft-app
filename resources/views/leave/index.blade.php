@extends('layouts.app')

@section('title', 'İzin')

@section('content')
    <h1 class="h3 mb-3">İzin</h1>
    @if ($self)
        <section class="app-panel mb-3">
            <h2 class="h6">Bakiye</h2>
            @forelse ($balances as $balance)
                <p class="mb-1">{{ $balance->lookup?->name }} · {{ $balance->unit === 'hour' ? 'saat' : 'gün' }} {{ $balance->amount }}</p>
            @empty
                <p class="text-secondary mb-0">Bakiye satırı yok.</p>
            @endforelse
        </section>
        <form class="app-panel mb-3" method="post" action="{{ route('leave.store') }}">
            @csrf
            <h2 class="h6">Talep</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="lookup_id">Tip</label>
                    <select class="form-select" id="lookup_id" name="lookup_id" required>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="mode">Birim</label>
                    <select class="form-select" id="mode" name="mode">
                        <option value="day">Gün</option>
                        <option value="hour">Saat</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="starts_on">Başlangıç</label>
                    <input class="form-control" id="starts_on" name="starts_on" type="date" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="ends_on">Bitiş günü</label>
                    <input class="form-control" id="ends_on" name="ends_on" type="date">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="starts_at">Başlangıç saati</label>
                    <input class="form-control" id="starts_at" name="starts_at" type="time">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="ends_at">Bitiş saati</label>
                    <input class="form-control" id="ends_at" name="ends_at" type="time">
                </div>
            </div>
            <button class="btn btn-primary mt-3" type="submit">Talep aç</button>
        </form>
    @else
        <p class="text-secondary">İzin talebi personel kartına bağlı hesaptan açılır.</p>
    @endif

    @foreach ($requests as $leave)
        <article class="app-panel mb-2">
            <p class="mb-1 fw-semibold">{{ $leave->employee->fullName() }} · {{ $leave->lookup?->name }}</p>
            <p class="mb-1">{{ $leave->mode === 'hour' ? $leave->hours.' saat' : $leave->dayCount().' gün' }} · {{ $leave->status }}</p>
            @if ($leave->status === 'pending' && app(\App\Support\LeaveDecider::class)->canDecide(auth()->user(), $leave))
                <form class="d-flex gap-2" method="post" action="{{ route('leave.update', $leave) }}">
                    @csrf
                    @method('put')
                    <button class="btn btn-primary btn-sm" name="status" value="approved" type="submit">Onayla</button>
                    <button class="btn btn-outline-secondary btn-sm" name="status" value="rejected" type="submit">Reddet</button>
                </form>
            @endif
        </article>
    @endforeach
@endsection
