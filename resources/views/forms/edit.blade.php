@extends('layouts.app')

@section('title', $form->name)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div>
            <p class="text-secondary mb-0">{{ $form->identity->label() }} · {{ $form->status->label() }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary d-lg-none" href="{{ route('forms.preview', $form) }}">Önizleme</a>
            <a class="btn btn-outline-secondary" href="{{ route('forms.results', $form) }}">Sonuç</a>
            @if ($form->isDraft())
                <form method="post" action="{{ route('forms.publish', $form) }}">
                    @csrf
                    <button class="btn btn-primary" type="submit">Yayınla</button>
                </form>
            @elseif ($form->status === \App\Enums\FormStatus::Published)
                <form method="post" action="{{ route('forms.close', $form) }}">
                    @csrf
                    <button class="btn btn-outline-secondary" type="submit">Kapat</button>
                </form>
            @endif
            <form method="post" action="{{ route('forms.clone', $form) }}">
                @csrf
                <button class="btn btn-outline-secondary" type="submit">Kopyala</button>
            </form>
        </div>
    </div>

    <div class="alert alert-danger" data-builder-error hidden></div>

    <div class="form-stage" data-form-builder @if ($form->isDraft()) data-draft @endif>
        @if ($form->isDraft())
            <div class="form-tools">
                @php $target = $form->sections->last(); @endphp
                @if ($target)
                    <form method="post" action="{{ route('forms.questions.store', [$form, $target]) }}">
                        @csrf
                        <input type="hidden" name="prompt" value="Soru">
                        <input type="hidden" name="type" value="multiple_choice">
                        <button class="form-tool" type="submit">
                            <i class="bi bi-plus-lg" aria-hidden="true"></i>
                            <span class="d-lg-none">Soru ekle</span>
                            <span class="visually-hidden d-none d-lg-inline">Soru ekle</span>
                        </button>
                    </form>
                @endif
                <form method="post" action="{{ route('forms.sections.store', $form) }}">
                    @csrf
                    <input type="hidden" name="title" value="Yeni bölüm">
                    <button class="form-tool" type="submit">
                        <i class="bi bi-layout-text-window" aria-hidden="true"></i>
                        <span class="d-lg-none">Bölüm</span>
                        <span class="visually-hidden d-none d-lg-inline">Bölüm ekle</span>
                    </button>
                </form>
            </div>
        @endif

        <div class="form-canvas">
            <article class="form-card">
                <form data-form-meta method="post" action="{{ route('forms.update', $form) }}">
                    @csrf
                    @method('put')
                    <input class="form-card-title" name="name" value="{{ $form->name }}" aria-label="Form adı" @disabled(! $form->isDraft()) required>
                    <textarea class="form-card-description" name="description" rows="2" placeholder="Açıklama" aria-label="Açıklama" @disabled(! $form->isDraft())>{{ $form->description }}</textarea>
                    <details class="mt-3">
                        <summary>Ayarlar</summary>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label" for="identity">Kimlik</label>
                                <select class="form-select" id="identity" name="identity" @disabled(! $form->isDraft())>
                                    @foreach (\App\Enums\FormIdentity::cases() as $identity)
                                        <option value="{{ $identity->value }}" @selected($form->identity === $identity)>{{ $identity->label() }}</option>
                                    @endforeach
                                </select>
                                @unless ($form->isDraft())
                                    <input type="hidden" name="identity" value="{{ $form->identity->value }}">
                                @endunless
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="starts_on">Başlangıç</label>
                                <input class="form-control" id="starts_on" name="starts_on" type="date" value="{{ $form->starts_on?->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="ends_on">Bitiş</label>
                                <input class="form-control" id="ends_on" name="ends_on" type="date" value="{{ $form->ends_on?->format('Y-m-d') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="thank_you">Teşekkür</label>
                                <textarea class="form-control" id="thank_you" name="thank_you" rows="2">{{ $form->thank_you }}</textarea>
                            </div>
                        </div>
                    </details>
                </form>
                @if ($form->identity === \App\Enums\FormIdentity::Anonymous)
                    <p class="small text-secondary mt-3 mb-0">Herkese açık adres: {{ route('forms.public', $form) }}</p>
                @endif
            </article>

            @foreach ($form->sections as $section)
                <section class="form-card form-section">
                    <input class="form-card-section" value="{{ $section->title }}" aria-label="Bölüm adı" data-section-url="{{ route('forms.sections.update', [$form, $section]) }}" @disabled(! $form->isDraft())>
                    <div data-sort-list data-kind="question" data-sort-url="{{ route('forms.questions.reorder', [$form, $section]) }}">
                        @foreach ($section->questions as $question)
                            @include('forms._card', ['question' => $question])
                        @endforeach
                    </div>
                </section>
            @endforeach

            @if ($form->identity === \App\Enums\FormIdentity::Link)
                <section class="form-card">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                        <h2 class="h6 mb-0">Kişisel bağlantılar</h2>
                        <form method="post" action="{{ route('forms.links', $form) }}">
                            @csrf
                            <button class="btn btn-outline-secondary btn-sm" type="submit">Üret</button>
                        </form>
                    </div>
                    @forelse ($form->links as $link)
                        <p class="small mb-1">{{ $link->employee->fullName() }} · {{ $link->used_at ? 'kullanıldı' : route('forms.link', $link->token) }}</p>
                    @empty
                        <p class="text-secondary mb-0">Henüz bağlantı yok.</p>
                    @endforelse
                </section>
            @endif
        </div>

        <aside class="form-live d-none d-lg-block">
            <p class="small text-secondary mb-2">Önizleme</p>
            <article class="form-card">
                <h2 class="h5" data-preview="title">{{ $form->name }}</h2>
                <p class="text-secondary" data-preview="description">{{ $form->description }}</p>
            </article>
            @foreach ($form->sections as $section)
                <p class="small text-secondary mb-2" data-preview-section="{{ $section->id }}">{{ $section->title }}</p>
                @foreach ($section->questions as $question)
                    <article class="form-card">
                        <p class="fw-semibold" data-preview-prompt="{{ $question->id }}">{{ $question->prompt }}</p>
                        @foreach ($question->options as $option)
                            <p class="mb-1" data-preview-option="{{ $option->id }}">{{ $option->label }}</p>
                        @endforeach
                        @foreach ($question->rows as $row)
                            <p class="mb-1 text-secondary" data-preview-row="{{ $row->id }}">{{ $row->label }}</p>
                        @endforeach
                    </article>
                @endforeach
            @endforeach
        </aside>
    </div>
@endsection
