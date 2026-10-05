<article
    class="form-q"
    id="soru-{{ $question->id }}"
    data-sort-item
    data-kind="question"
    data-question-id="{{ $question->id }}"
    data-type="{{ $question->type->value }}"
    data-save-url="{{ route('forms.questions.update', [$form, $question]) }}"
    @if ((int) session('focus') === $question->id) data-focus @endif
>
    @if ($form->isDraft())
        <button class="form-card-handle" type="button" data-drag-handle aria-label="Soruyu sürükle">
            <i class="bi bi-grip-horizontal" aria-hidden="true"></i>
        </button>
    @endif

    <div class="row g-2 align-items-start">
        <div class="col-md-8">
            <input class="form-card-prompt" data-save="prompt" value="{{ $question->prompt }}" aria-label="Soru" @disabled(! $form->isDraft())>
        </div>
        <div class="col-md-4">
            <label class="visually-hidden" for="type-{{ $question->id }}">Soru türü</label>
            <select class="form-select" id="type-{{ $question->id }}" data-save="type" @disabled(! $form->isDraft())>
                @foreach (\App\Enums\QuestionType::cases() as $type)
                    @continue($type->namedOnly() && $form->identity === \App\Enums\FormIdentity::Anonymous)
                    <option value="{{ $type->value }}" @selected($question->type === $type)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <input class="form-card-help" data-save="help" value="{{ $question->help }}" placeholder="Yardım metni" aria-label="Yardım metni" @disabled(! $form->isDraft())>

    @if ($question->type === \App\Enums\QuestionType::ShortText)
        <input class="form-control mt-3" placeholder="Kısa yanıt" disabled>
    @elseif ($question->type === \App\Enums\QuestionType::Paragraph)
        <textarea class="form-control mt-3" rows="2" placeholder="Uzun yanıt" disabled></textarea>
    @elseif ($question->type === \App\Enums\QuestionType::Date)
        <input class="form-control mt-3" type="date" disabled>
    @elseif ($question->type === \App\Enums\QuestionType::Time)
        <input class="form-control mt-3" type="time" disabled>
    @elseif ($question->type === \App\Enums\QuestionType::File)
        <input class="form-control mt-3" type="file" disabled>
    @elseif ($question->type === \App\Enums\QuestionType::EmployeePick)
        <select class="form-select mt-3" disabled><option>Personel</option></select>
    @elseif ($question->type === \App\Enums\QuestionType::LinearScale)
        <div class="d-flex gap-2 align-items-center mt-3">
            <input class="form-control" data-save="scale_min" type="number" min="0" max="10" value="{{ $question->config['scale_min'] ?? 1 }}" aria-label="Ölçek alt" @disabled(! $form->isDraft())>
            <span class="text-secondary">–</span>
            <input class="form-control" data-save="scale_max" type="number" min="1" max="10" value="{{ $question->config['scale_max'] ?? 5 }}" aria-label="Ölçek üst" @disabled(! $form->isDraft())>
        </div>
    @endif

    @if ($question->type->needsOptions())
        <div class="mt-3" data-sort-list data-kind="option" data-sort-url="{{ route('forms.options.reorder', [$form, $question]) }}">
            @foreach ($question->options as $option)
                <div class="form-option" data-sort-item data-kind="option" data-option-id="{{ $option->id }}">
                    @if ($form->isDraft())
                        <button class="form-card-handle" type="button" data-drag-handle aria-label="Şıkkı sürükle">
                            <i class="bi bi-grip-vertical" aria-hidden="true"></i>
                        </button>
                    @endif
                    <i class="bi {{ $question->type === \App\Enums\QuestionType::Checkboxes || $question->type === \App\Enums\QuestionType::CheckboxGrid ? 'bi-square' : 'bi-circle' }} text-secondary" aria-hidden="true"></i>
                    <input class="form-option-input" value="{{ $option->label }}" aria-label="Şık" data-option-url="{{ route('forms.options.update', [$form, $option]) }}" @disabled(! $form->isDraft())>
                    @if ($form->isDraft())
                        <form method="post" action="{{ route('forms.options.destroy', [$form, $option]) }}">
                            @csrf
                            @method('delete')
                            <button class="btn btn-link text-secondary" type="submit" aria-label="Şıkkı sil"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
        @if ($form->isDraft())
            <form method="post" action="{{ route('forms.options.store', [$form, $question]) }}">
                @csrf
                <input type="hidden" name="label" value="Seçenek {{ $question->options->count() + 1 }}">
                <button class="btn btn-link px-0" type="submit">Seçenek ekle</button>
            </form>
        @endif
    @endif

    @if ($question->type->needsRows())
        <div class="mt-2">
            @foreach ($question->rows as $row)
                <div class="form-option">
                    <input class="form-option-input" value="{{ $row->label }}" aria-label="Satır" data-row-url="{{ route('forms.rows.update', [$form, $row]) }}" @disabled(! $form->isDraft())>
                    @if ($form->isDraft())
                        <form method="post" action="{{ route('forms.rows.destroy', [$form, $row]) }}">
                            @csrf
                            @method('delete')
                            <button class="btn btn-link text-secondary" type="submit" aria-label="Satırı sil"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </form>
                    @endif
                </div>
            @endforeach
            @if ($form->isDraft())
                <form method="post" action="{{ route('forms.rows.store', [$form, $question]) }}">
                    @csrf
                    <input type="hidden" name="label" value="Satır {{ $question->rows->count() + 1 }}">
                    <button class="btn btn-link px-0" type="submit">Satır ekle</button>
                </form>
            @endif
        </div>
    @endif

    @if ($form->isDraft())
        <div class="form-card-foot">
            <form method="post" action="{{ route('forms.questions.duplicate', [$form, $question]) }}">
                @csrf
                <button class="btn btn-link text-secondary" type="submit" aria-label="Soruyu kopyala"><i class="bi bi-copy" aria-hidden="true"></i></button>
            </form>
            <form method="post" action="{{ route('forms.questions.destroy', [$form, $question]) }}">
                @csrf
                @method('delete')
                <button class="btn btn-link text-secondary" type="submit" aria-label="Soruyu sil"><i class="bi bi-trash" aria-hidden="true"></i></button>
            </form>
            <div class="form-check form-switch ms-auto mb-0">
                <input class="form-check-input" data-save="required" type="checkbox" id="required-{{ $question->id }}" @checked($question->required)>
                <label class="form-check-label" for="required-{{ $question->id }}">Zorunlu</label>
            </div>
        </div>
    @endif
</article>
