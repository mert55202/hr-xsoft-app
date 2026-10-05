@extends('layouts.app')

@section('title', 'Seçme')

@section('content')
    <h1 class="h3 mb-3">Seçme</h1>
    <form class="app-panel mb-3" method="post" action="{{ route('recruitment.requests.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="department_id">Departman</label>
                <select class="form-select" id="department_id" name="department_id" required>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="title">Başlık</label>
                <input class="form-control" id="title" name="title" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="headcount">Kadro</label>
                <input class="form-control" id="headcount" name="headcount" type="number" min="1" value="1" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary" type="submit">İstek</button>
            </div>
        </div>
    </form>
    <form class="app-panel mb-3" method="post" action="{{ route('recruitment.candidates.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="first_name" placeholder="Ad" required></div>
            <div class="col-md-4"><input class="form-control" name="last_name" placeholder="Soyad" required></div>
            <div class="col-md-4"><button class="btn btn-outline-secondary" type="submit">Aday ekle</button></div>
        </div>
    </form>
    @foreach ($requests as $item)
        <p>{{ $item->title }} · {{ $item->department?->name }} · {{ $item->headcount }}</p>
    @endforeach
    @foreach ($candidates as $candidate)
        <article class="app-panel mb-3">
            <h2 class="h6">{{ $candidate->fullName() }}</h2>
            <form class="row g-2 mb-2" method="post" action="{{ route('recruitment.interviews.store', $candidate) }}">
                @csrf
                <div class="col-md-8"><input class="form-control" name="notes" placeholder="Mülakat notu"></div>
                <div class="col-md-4"><button class="btn btn-outline-secondary btn-sm" type="submit">Mülakat</button></div>
            </form>
            <form class="row g-2 mb-2" method="post" action="{{ route('recruitment.references.store', $candidate) }}">
                @csrf
                <div class="col-md-8"><input class="form-control" name="name" placeholder="Referans adı" required></div>
                <div class="col-md-4"><button class="btn btn-outline-secondary btn-sm" type="submit">Referans</button></div>
            </form>
            <form class="row g-2 mb-2" method="post" action="{{ route('recruitment.offers.store', $candidate) }}">
                @csrf
                <div class="col-md-8"><input class="form-control" name="amount" placeholder="Teklif tutarı" required></div>
                <div class="col-md-4"><button class="btn btn-outline-secondary btn-sm" type="submit">Teklif</button></div>
            </form>
            <form class="row g-2" method="post" action="{{ route('recruitment.hire', $candidate) }}">
                @csrf
                <div class="col-md-8">
                    <select class="form-select" name="position_id" required>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}">{{ $position->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><button class="btn btn-primary btn-sm" type="submit">İşe al</button></div>
            </form>
        </article>
    @endforeach
@endsection
