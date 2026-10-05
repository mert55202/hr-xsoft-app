@extends('layouts.app')

@section('title', 'Personel')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">Personel</h1>
            <p class="text-secondary mb-0">Kendin ve altındaki kadro. Üstün kartı bu listede yok.</p>
        </div>
        @if ($canCreate)
            <a class="btn btn-primary" href="{{ route('personnel.create') }}">Yeni personel</a>
        @endif
    </div>

    @if ($employees->isEmpty())
        <section class="app-empty">
            <span class="app-empty-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
            <h2 class="h5">Kayıt yok</h2>
            <p class="text-secondary mb-0">Görebildiğin personel kartı henüz yok.</p>
        </section>
    @else
        <div class="d-md-none d-flex flex-column gap-2">
            @foreach ($employees as $employee)
                <a class="app-panel text-decoration-none text-body" href="{{ route('personnel.show', $employee) }}">
                    <h2 class="h6 mb-1">{{ $employee->fullName() }}</h2>
                    <p class="mb-0 text-secondary">{{ $employee->position?->name ?? 'Pozisyon yok' }}</p>
                </a>
            @endforeach
        </div>

        <div class="d-none d-md-block app-panel">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Pozisyon</th>
                        <th>Departman</th>
                        <th>Birinci amir</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $employee)
                        <tr>
                            <td>{{ $employee->fullName() }}</td>
                            <td>{{ $employee->position?->name ?? '—' }}</td>
                            <td>{{ $employee->position?->department?->name ?? '—' }}</td>
                            <td>{{ $employee->position?->parent?->name ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('personnel.show', $employee) }}">Kart</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
