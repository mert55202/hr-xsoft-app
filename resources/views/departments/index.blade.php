@extends('layouts.app')

@section('title', 'Departmanlar')

@section('content')
    <div class="mb-3">
        <h1 class="h3 mb-1">Departmanlar</h1>
        <p class="text-secondary mb-0">Modüller menüyü açar. Kişi sınırı, o departmana yazılabilecek kadronun tavanıdır.</p>
    </div>

    <section class="app-panel mb-3">
        <h2 class="h5">Yeni departman</h2>
        <form method="post" action="{{ route('departments.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-12 col-md-6">
                <label class="form-label" for="name">Ad</label>
                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label" for="new-headcount">Kişi sınırı</label>
                <input class="form-control @if (old('_department') === null) @error('headcount') is-invalid @enderror @endif" id="new-headcount" name="headcount" type="number" min="1" value="{{ old('_department') === null ? old('headcount') : '' }}" placeholder="Sınır yok">
                @if (old('_department') === null)
                    @error('headcount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @endif
            </div>
            <div class="col-12 col-md-3">
                <button class="btn btn-primary w-100" type="submit">Ekle</button>
            </div>
        </form>
    </section>

    <div class="d-grid gap-3">
        @forelse ($departments as $department)
            <section class="app-panel">
                <h2 class="h5">{{ $department->name }}</h2>
                <form method="post" action="{{ route('departments.update', $department) }}">
                    @csrf
                    @method('put')
                    <input type="hidden" name="_department" value="{{ $department->id }}">
                    <div class="col-12 col-md-4 mb-3">
                        <label class="form-label" for="headcount-{{ $department->id }}">Kişi sınırı</label>
                        <input
                            class="form-control @if ((string) old('_department') === (string) $department->id) @error('headcount') is-invalid @enderror @endif"
                            id="headcount-{{ $department->id }}"
                            name="headcount"
                            type="number"
                            min="1"
                            value="{{ (string) old('_department') === (string) $department->id ? old('headcount', $department->headcount) : $department->headcount }}"
                            placeholder="Sınır yok"
                        >
                        @if ((string) old('_department') === (string) $department->id)
                            @error('headcount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @endif
                        <div class="form-text">Boş bırakılırsa bu departmanın kadro tavanı yoktur.</div>
                    </div>
                    <div class="row g-2">
                        @foreach ($modules as $module)
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        id="module-{{ $department->id }}-{{ $module->value }}"
                                        name="modules[]"
                                        type="checkbox"
                                        value="{{ $module->value }}"
                                        @checked($department->has($module))
                                    >
                                    <label class="form-check-label" for="module-{{ $department->id }}-{{ $module->value }}">{{ $module->label() }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Kaydet</button>
                </form>
            </section>
        @empty
            <section class="app-empty">
                <span class="app-empty-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
                <h2 class="h5">Departman yok</h2>
                <p class="text-secondary mb-0">Önce bir departman ekle, sonra modüllerini aç.</p>
            </section>
        @endforelse
    </div>
@endsection
