@extends('layouts.app')

@section('title', $form->name.' sonuç')

@section('content')
    <h1 class="h3 mb-1">{{ $form->name }}</h1>
    <p class="text-secondary mb-3">{{ $submissions->count() }} yanıt
        @unless ($revealPeople)
            · cevaplayan yok
        @endunless
    </p>

    @foreach ($form->sections as $section)
        @foreach ($section->questions as $question)
            <section class="app-panel mb-3">
                <h2 class="h6">{{ $question->prompt }}</h2>
                @php
                    $answers = $submissions->flatMap->answers->where('form_question_id', $question->id);
                @endphp
                @if ($question->type->needsOptions() && ! $question->type->needsRows())
                    @foreach ($question->options as $option)
                        @php $count = $answers->where('form_option_id', $option->id)->count(); @endphp
                        <p class="mb-1">{{ $option->label }} · {{ $count }}</p>
                    @endforeach
                @elseif ($question->type === \App\Enums\QuestionType::LinearScale)
                    <p class="mb-0">Ortalama {{ $answers->avg('value_number') === null ? '—' : number_format((float) $answers->avg('value_number'), 1, ',', '') }}</p>
                @else
                    @forelse ($answers as $answer)
                        <p class="mb-1">{{ $answer->body ?: $answer->option?->label ?: $answer->value_date?->format('d.m.Y') ?: $answer->value_time ?: '—' }}</p>
                    @empty
                        <p class="text-secondary mb-0">Yanıt yok.</p>
                    @endforelse
                @endif
            </section>
        @endforeach
    @endforeach

    @if ($revealPeople)
        <section class="app-panel">
            <h2 class="h5">Kim yanıtladı</h2>
            @forelse ($submissions as $submission)
                <p class="mb-1">{{ $submission->employee?->fullName() ?? '—' }}</p>
            @empty
                <p class="text-secondary mb-0">Görebildiğin yanıt yok.</p>
            @endforelse
        </section>
    @endif
@endsection
