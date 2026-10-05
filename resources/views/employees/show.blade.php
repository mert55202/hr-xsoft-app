@extends('layouts.app')

@section('title', $employee->fullName())

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">{{ $employee->fullName() }}</h1>
            <p class="text-secondary mb-0">{{ $employee->position?->name ?? 'Pozisyon yok' }} · {{ $employee->position?->department?->name ?? 'Departman yok' }}</p>
        </div>
        @if ($canManage)
            <a class="btn btn-outline-secondary" href="{{ route('personnel.edit', $employee) }}">Düzenle</a>
        @endif
    </div>

    <ul class="nav nav-pills flex-nowrap overflow-auto gap-2 mb-3">
        <li class="nav-item"><a class="nav-link" href="#genel">Genel</a></li>
        <li class="nav-item"><a class="nav-link" href="#kimlik">Kimlik</a></li>
        <li class="nav-item"><a class="nav-link" href="#iletisim">İletişim</a></li>
        <li class="nav-item"><a class="nav-link" href="#ogrenim">Öğrenim</a></li>
        <li class="nav-item"><a class="nav-link" href="#deneyim">Deneyim</a></li>
        <li class="nav-item"><a class="nav-link" href="#organizasyon">Organizasyon</a></li>
        <li class="nav-item"><a class="nav-link" href="#ucret">Ücret</a></li>
        <li class="nav-item"><a class="nav-link" href="#envanter">Envanter</a></li>
        <li class="nav-item"><a class="nav-link" href="#evrak">Özlük</a></li>
        <li class="nav-item"><a class="nav-link" href="#saglik">Sağlık</a></li>
        <li class="nav-item"><a class="nav-link" href="#aile">Aile</a></li>
        <li class="nav-item"><a class="nav-link" href="#kayit">Ödül ve uyarı</a></li>
        <li class="nav-item"><a class="nav-link" href="#isg">İş güvenliği</a></li>
        @if ($canManage && $employee->user)
            <li class="nav-item"><a class="nav-link" href="#yetki">Yetki</a></li>
        @endif
    </ul>

    <div class="d-flex flex-column gap-3">
        <section class="app-panel" id="genel">
            <h2 class="h5">Genel</h2>
            <p class="mb-1">{{ $employee->fullName() }}</p>
            <p class="mb-0 text-secondary">{{ $employee->user ? 'Hesap: '.$employee->user->email : 'Giriş hesabı bağlı değil' }}</p>
        </section>

        <section class="app-panel" id="kimlik">
            <h2 class="h5">Kimlik</h2>
            <p class="mb-1">T.C. {{ $employee->national_id ?: '—' }}</p>
            <p class="mb-0">Doğum {{ $employee->birth_date?->format('d.m.Y') ?? '—' }}</p>
        </section>

        <section class="app-panel" id="iletisim">
            <h2 class="h5">İletişim</h2>
            <p class="mb-1">{{ $employee->email ?: 'E-posta yok' }}</p>
            <p class="mb-0">{{ $employee->phone ?: 'Telefon yok' }}</p>
        </section>

        <section class="app-panel" id="ogrenim">
            <h2 class="h5">Öğrenim</h2>
            @forelse ($employee->educations as $education)
                <div class="d-flex justify-content-between gap-3 mb-2">
                    <div>
                        <p class="mb-0">{{ $education->school }}</p>
                        <p class="mb-0 text-secondary">{{ $education->field ?: 'Bölüm yok' }} · {{ $education->graduated_on?->format('Y') ?? '—' }}</p>
                    </div>
                    @if ($canManage)
                        <form method="post" action="{{ route('personnel.educations.destroy', [$employee, $education]) }}">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-secondary" type="submit">Sil</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-secondary">Öğrenim kaydı yok.</p>
            @endforelse

            @if ($canManage)
                <form class="row g-2 align-items-end" method="post" action="{{ route('personnel.educations.store', $employee) }}">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label" for="school">Okul</label>
                        <input class="form-control @error('school') is-invalid @enderror" id="school" name="school" value="{{ old('school') }}" required>
                        @error('school')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="field">Bölüm</label>
                        <input class="form-control" id="field" name="field" value="{{ old('field') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="graduated_on">Mezuniyet</label>
                        <input class="form-control" id="graduated_on" name="graduated_on" type="date" value="{{ old('graduated_on') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Ekle</button>
                    </div>
                </form>
            @endif
        </section>

        <section class="app-panel" id="deneyim">
            <h2 class="h5">Deneyim</h2>
            @forelse ($employee->experiences as $experience)
                <div class="d-flex justify-content-between gap-3 mb-2">
                    <div>
                        <p class="mb-0">{{ $experience->title }} · {{ $experience->company }}</p>
                        <p class="mb-0 text-secondary">{{ $experience->started_on?->format('Y') ?? '—' }} – {{ $experience->ended_on?->format('Y') ?? 'devam' }}</p>
                    </div>
                    @if ($canManage)
                        <form method="post" action="{{ route('personnel.experiences.destroy', [$employee, $experience]) }}">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-secondary" type="submit">Sil</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-secondary">Deneyim kaydı yok.</p>
            @endforelse

            @if ($canManage)
                <form class="row g-2 align-items-end" method="post" action="{{ route('personnel.experiences.store', $employee) }}">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label" for="company">Firma</label>
                        <input class="form-control @error('company') is-invalid @enderror" id="company" name="company" value="{{ old('company') }}" required>
                        @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="title">Unvan</label>
                        <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="started_on">Başlangıç</label>
                        <input class="form-control" id="started_on" name="started_on" type="date" value="{{ old('started_on') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="ended_on">Bitiş</label>
                        <input class="form-control" id="ended_on" name="ended_on" type="date" value="{{ old('ended_on') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Ekle</button>
                    </div>
                </form>
            @endif
        </section>

        <section class="app-panel" id="organizasyon">
            <h2 class="h5">Organizasyon</h2>
            <p class="mb-1">Pozisyon: {{ $employee->position?->name ?? '—' }}</p>
            <p class="mb-1">Departman: {{ $employee->position?->department?->name ?? '—' }}</p>
            <p class="mb-1">Birinci amir: @include('employees._names', ['people' => $firstManagers, 'position' => $employee->position?->parent])</p>
            @if ($employee->position?->parent_id && $firstManagers->isEmpty())
                <p class="alert alert-warning">Önce o pozisyona personel atanmalıdır.</p>
            @endif
            <p class="mb-0">İkinci amir: @include('employees._names', ['people' => $secondManagers, 'position' => $employee->position?->parent?->parent])</p>
        </section>

        <section class="app-panel" id="ucret">
            <h2 class="h5">Ücret</h2>
            @if ($canManage || auth()->id() === $employee->user_id)
                <p class="mb-0">{{ $employee->pay_type?->label() ?? 'Tür seçilmedi' }} · {{ $employee->pay_amount ?? '—' }} {{ $employee->currency }}</p>
            @else
                <p class="mb-0 text-secondary">Ücret bu kartta kapalı.</p>
            @endif
        </section>

        <section class="app-panel" id="envanter">
            <h2 class="h5">Envanter</h2>
            @forelse ($employee->assets as $asset)
                <p class="mb-1">{{ $asset->name }} <span class="text-secondary">{{ $asset->given_on?->format('d.m.Y') }}</span></p>
            @empty
                <p class="text-secondary">Envanter yok.</p>
            @endforelse
            @if ($canManage)
                <form class="row g-2 align-items-end" method="post" action="{{ route('personnel.assets.store', $employee) }}">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label" for="asset_name">Ad</label>
                        <input class="form-control" id="asset_name" name="name" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="given_on">Veriliş</label>
                        <input class="form-control" id="given_on" name="given_on" type="date">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Ekle</button>
                    </div>
                </form>
            @endif
        </section>

        <section class="app-panel" id="evrak">
            <h2 class="h5">Özlük dosyası</h2>
            @forelse ($employee->documents as $document)
                <div class="d-flex justify-content-between gap-3 mb-2">
                    <a href="{{ route('personnel.documents.download', [$employee, $document]) }}">{{ $document->original_name }}</a>
                    @if ($document->verified_at)
                        <span class="text-success">Doğrulandı</span>
                    @elseif ($canManage)
                        <form method="post" action="{{ route('personnel.documents.verify', [$employee, $document]) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary" type="submit">Doğrula</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-secondary">Evrak yok.</p>
            @endforelse
            @if ($canManage || auth()->user()->employee?->is($employee))
                <form method="post" action="{{ route('personnel.documents.store', $employee) }}" enctype="multipart/form-data">
                    @csrf
                    <label class="form-label" for="file">Dosya</label>
                    <input class="form-control mb-2" id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                    <button class="btn btn-primary" type="submit">Yükle</button>
                </form>
            @endif
        </section>

        <section class="app-panel" id="saglik">
            <h2 class="h5">Sağlık</h2>
            <p>{{ $employee->health?->note ?: 'Kayıt yok.' }}</p>
            @if ($canManage || auth()->user()->employee?->is($employee))
                <form method="post" action="{{ route('personnel.health.store', $employee) }}">
                    @csrf
                    <label class="form-label" for="health_note">Not</label>
                    <textarea class="form-control mb-2" id="health_note" name="note" rows="2" required>{{ $employee->health?->note }}</textarea>
                    <button class="btn btn-primary" type="submit">Kaydet</button>
                </form>
            @endif
        </section>

        <section class="app-panel" id="aile">
            <h2 class="h5">Aile</h2>
            @forelse ($employee->relatives as $relative)
                <p class="mb-1">{{ $relative->name }} <span class="text-secondary">{{ $relative->kinship }}</span></p>
            @empty
                <p class="text-secondary">Yakın yok.</p>
            @endforelse
            @if ($canManage)
                <form class="row g-2 align-items-end" method="post" action="{{ route('personnel.relatives.store', $employee) }}">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label" for="relative_name">Ad</label>
                        <input class="form-control" id="relative_name" name="name" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="kinship">Yakınlık</label>
                        <input class="form-control" id="kinship" name="kinship">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Ekle</button>
                    </div>
                </form>
            @endif
        </section>

        <section class="app-panel" id="kayit">
            <h2 class="h5">Ödül ve uyarı</h2>
            @forelse ($employee->notes as $note)
                <p class="mb-1">{{ $note->kind === 'odul' ? 'Ödül' : 'Uyarı' }}: {{ $note->body }}</p>
            @empty
                <p class="text-secondary">Kayıt yok.</p>
            @endforelse
            @if ($canManage)
                <form method="post" action="{{ route('personnel.notes.store', $employee) }}">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label" for="kind">Tür</label>
                            <select class="form-select" id="kind" name="kind" required>
                                <option value="odul">Ödül</option>
                                <option value="uyari">Uyarı</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="note_body">Metin</label>
                            <input class="form-control" id="note_body" name="body" required>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit">Ekle</button>
                        </div>
                    </div>
                </form>
            @endif
        </section>

        <section class="app-panel" id="isg">
            <h2 class="h5">İş güvenliği</h2>
            @forelse ($employee->safetyRecords as $record)
                <p class="mb-1">{{ $record->recorded_on?->format('d.m.Y') }} · {{ $record->body }}</p>
            @empty
                <p class="text-secondary">Kayıt yok.</p>
            @endforelse
            @if ($canManage)
                <form class="row g-2 align-items-end" method="post" action="{{ route('personnel.safety.store', $employee) }}">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label" for="recorded_on">Tarih</label>
                        <input class="form-control" id="recorded_on" name="recorded_on" type="date" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label" for="safety_body">Kayıt</label>
                        <input class="form-control" id="safety_body" name="body" required>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Ekle</button>
                    </div>
                </form>
            @endif
        </section>

        @if ($canManage && $employee->user)
            <section class="app-panel" id="yetki">
                <h2 class="h5">Yetki</h2>
                <p class="text-secondary">İşaretlenen modül bu kişide kapanır. Departmanda olmayan modül açılamaz.</p>
                <form method="post" action="{{ route('personnel.modules', $employee) }}">
                    @csrf
                    @method('put')
                    @foreach (\App\Enums\AppModule::grantableCases() as $module)
                        @if (in_array($module->value, $employee->user->department?->modules ?? [], true))
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="hidden_modules[]" value="{{ $module->value }}" id="hide-{{ $module->value }}" @checked(in_array($module->value, $employee->user->hidden_modules ?? [], true))>
                                <label class="form-check-label" for="hide-{{ $module->value }}">{{ $module->label() }} menüden gizle</label>
                            </div>
                        @endif
                    @endforeach
                    <button class="btn btn-primary mt-2" type="submit">Kaydet</button>
                </form>
            </section>
        @endif
    </div>
@endsection
