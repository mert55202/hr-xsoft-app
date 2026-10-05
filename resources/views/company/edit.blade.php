@extends('layouts.app')

@section('title', 'Firma')

@section('content')
    @php($locked = ! auth()->user()->isAdmin())
    <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">Firma</h1>
            <p class="text-secondary mb-0">Kimlik, iletişim ve vurgu rengi.</p>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('departments.index') }}">Departman modülleri</a>
                <a href="{{ route('positions.index') }}">Pozisyonlar</a>
            @endif
        </div>
    </div>
    <form method="post" action="{{ route('company.update') }}">
        @csrf
        @method('put')
        <div class="row g-3">
            <div class="col-12 col-lg-7">
                <section class="app-panel h-100">
                    <h2 class="h5 mb-3">Kimlik</h2>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="name">Firma adı</label>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $company->name) }}" @disabled($locked) required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">E-posta</label>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $company->email) }}" @disabled($locked)>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Telefon</label>
                            <input class="form-control" id="phone" name="phone" value="{{ old('phone', $company->phone) }}" @disabled($locked)>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="primary_color">Vurgu rengi</label>
                            <div class="d-flex align-items-center gap-2">
                                <input class="form-control form-control-color @error('primary_color') is-invalid @enderror" id="primary_color" name="primary_color" type="color" value="{{ old('primary_color', $company->primary_color) }}" data-color-field="accent" @disabled($locked) required>
                                <span class="font-monospace" data-color-value="accent">{{ old('primary_color', $company->primary_color) }}</span>
                            </div>
                            @error('primary_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="personnel_quota">Kota</label>
                            <input class="form-control" id="personnel_quota" name="personnel_quota" type="number" min="1" value="{{ old('personnel_quota', $company->personnel_quota) }}" @disabled($locked)>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="founded_on">Açılış</label>
                            <input class="form-control" id="founded_on" name="founded_on" type="date" value="{{ old('founded_on', $company->founded_on?->format('Y-m-d')) }}" @disabled($locked)>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-12 col-lg-5">
                <section class="app-panel h-100 d-grid gap-3">
                    <h2 class="h5 mb-0">Metinler</h2>
                    <div>
                        <label class="form-label" for="vision">Vizyon</label>
                        <textarea class="form-control" id="vision" name="vision" rows="3" @disabled($locked)>{{ old('vision', $company->vision) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label" for="mission">Misyon</label>
                        <textarea class="form-control" id="mission" name="mission" rows="3" @disabled($locked)>{{ old('mission', $company->mission) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label" for="kvkk">KVKK</label>
                        <textarea class="form-control" id="kvkk" name="kvkk" rows="4" @disabled($locked)>{{ old('kvkk', $company->kvkk) }}</textarea>
                    </div>
                </section>
            </div>
        </div>
        @unless ($locked)
            <div class="app-savebar mt-3 d-flex justify-content-end">
                <button class="btn btn-primary btn-lg" type="submit">Kaydet</button>
            </div>
        @endunless
    </form>
    @if ($locked)
        <p class="text-secondary mt-3 mb-0">Firma kaydını admin düzenler.</p>
    @endif
@endsection
