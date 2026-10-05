@extends('layouts.app')

@section('title', 'Eğitim')

@section('content')
    <h1 class="h3 mb-3">Eğitim</h1>
    <form class="row g-2 mb-3" method="post" action="{{ route('training.courses.store') }}">
        @csrf
        <div class="col-md-8"><input class="form-control" name="name" placeholder="Eğitim adı" required></div>
        <div class="col-md-4"><button class="btn btn-primary" type="submit">Eğitim ekle</button></div>
    </form>
    <form class="app-panel mb-3" method="post" action="{{ route('training.sessions.store') }}">
        @csrf
        <div class="row g-2">
            <div class="col-md-3">
                <select class="form-select" name="course_id" required>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><input class="form-control" name="held_on" type="date" required></div>
            <div class="col-md-3">
                <select class="form-select" name="pre_form_id">
                    <option value="">Ön test yok</option>
                    @foreach ($forms as $form)
                        <option value="{{ $form->id }}">{{ $form->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="post_form_id">
                    <option value="">Son test yok</option>
                    @foreach ($forms as $form)
                        <option value="{{ $form->id }}">{{ $form->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button class="btn btn-outline-secondary mt-2" type="submit">Etkinlik</button>
    </form>
    @foreach ($sessions as $session)
        <article class="app-panel mb-3">
            <h2 class="h6">{{ $session->course?->name }} · {{ $session->held_on?->format('d.m.Y') }}</h2>
            @if ($session->preForm)
                <a href="{{ route('forms.show', $session->preForm) }}">Ön test</a>
            @endif
            @if ($session->postForm)
                <a href="{{ route('forms.show', $session->postForm) }}">Son test</a>
            @endif
            <form class="row g-2 mt-2" method="post" action="{{ route('training.enroll', $session) }}">
                @csrf
                <div class="col-md-8">
                    <select class="form-select" name="employee_id">
                        @foreach ($people as $person)
                            <option value="{{ $person->id }}">{{ $person->fullName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><button class="btn btn-outline-secondary btn-sm" type="submit">Katılımcı</button></div>
            </form>
            <ul class="mb-0 mt-2">
                @foreach ($session->enrollments as $enrollment)
                    <li>
                        {{ $enrollment->employee?->fullName() }}
                        @if ($enrollment->attended) · katıldı @endif
                        <form class="d-inline" method="post" action="{{ route('training.attend', [$session, $enrollment->employee]) }}">
                            @csrf
                            <button class="btn btn-link btn-sm" type="submit">Yoklama</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </article>
    @endforeach

    @if ($courses->isNotEmpty() && $people->isNotEmpty())
        <h2 class="h5 mt-4">Matris</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Kişi</th>
                        @foreach ($courses as $course)
                            <th>{{ $course->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($people as $person)
                        <tr>
                            <th scope="row">{{ $person->fullName() }}</th>
                            @foreach ($courses as $course)
                                <td>
                                    @if ($sessions->contains(fn ($session) => (int) $session->course_id === (int) $course->id && $session->enrollments->contains(fn ($enrollment) => (int) $enrollment->employee_id === (int) $person->id)))
                                        Katılımcı
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
