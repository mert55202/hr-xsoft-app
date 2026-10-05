@php
    $name = 'answers['.$question->id.']';
    $disabled = $preview ? 'disabled' : '';
@endphp
<div class="mb-4">
    <p class="fw-semibold mb-1">{{ $question->prompt }}
        @if ($question->required)
            <span class="text-danger">*</span>
        @endif
    </p>
    @if ($question->help)
        <p class="small text-secondary">{{ $question->help }}</p>
    @endif

    @if ($question->type === \App\Enums\QuestionType::ShortText)
        <input class="form-control" name="{{ $name }}" maxlength="{{ $question->config['max'] ?? 200 }}" {{ $disabled }} @if ($question->required && ! $preview) required @endif>
    @elseif ($question->type === \App\Enums\QuestionType::Paragraph)
        <textarea class="form-control" name="{{ $name }}" rows="3" maxlength="{{ $question->config['max'] ?? 2000 }}" {{ $disabled }} @if ($question->required && ! $preview) required @endif></textarea>
    @elseif ($question->type === \App\Enums\QuestionType::MultipleChoice)
        @foreach ($question->options as $option)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="{{ $name }}" id="q{{ $question->id }}o{{ $option->id }}" value="{{ $option->id }}" {{ $disabled }}>
                <label class="form-check-label" for="q{{ $question->id }}o{{ $option->id }}">{{ $option->label }}</label>
            </div>
        @endforeach
    @elseif ($question->type === \App\Enums\QuestionType::Checkboxes)
        @foreach ($question->options as $option)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="{{ $name }}[]" id="q{{ $question->id }}o{{ $option->id }}" value="{{ $option->id }}" {{ $disabled }}>
                <label class="form-check-label" for="q{{ $question->id }}o{{ $option->id }}">{{ $option->label }}</label>
            </div>
        @endforeach
    @elseif ($question->type === \App\Enums\QuestionType::Dropdown)
        <select class="form-select" name="{{ $name }}" {{ $disabled }}>
            <option value="">Seçin</option>
            @foreach ($question->options as $option)
                <option value="{{ $option->id }}">{{ $option->label }}</option>
            @endforeach
        </select>
    @elseif ($question->type === \App\Enums\QuestionType::LinearScale)
        <div class="d-flex flex-wrap gap-3">
            @for ($score = (int) ($question->config['scale_min'] ?? 1); $score <= (int) ($question->config['scale_max'] ?? 5); $score++)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="{{ $name }}" id="q{{ $question->id }}s{{ $score }}" value="{{ $score }}" {{ $disabled }}>
                    <label class="form-check-label" for="q{{ $question->id }}s{{ $score }}">{{ $score }}</label>
                </div>
            @endfor
        </div>
    @elseif ($question->type === \App\Enums\QuestionType::Date)
        <input class="form-control" type="date" name="{{ $name }}" {{ $disabled }}>
    @elseif ($question->type === \App\Enums\QuestionType::Time)
        <input class="form-control" type="time" name="{{ $name }}" {{ $disabled }}>
    @elseif ($question->type === \App\Enums\QuestionType::EmployeePick)
        <select class="form-select" name="{{ $name }}" {{ $disabled }}>
            <option value="">Seçin</option>
            @foreach ($employees as $person)
                <option value="{{ $person->id }}">{{ $person->fullName() }}</option>
            @endforeach
        </select>
    @elseif ($question->type === \App\Enums\QuestionType::File)
        <input class="form-control" type="file" name="{{ $name }}" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $disabled }}>
    @elseif ($question->type->needsRows())
        @foreach ($question->rows as $row)
            <fieldset class="mb-3">
                <legend class="col-form-label pt-0">{{ $row->label }}</legend>
                @foreach ($question->options as $option)
                    <div class="form-check">
                        <input class="form-check-input" type="{{ $question->type === \App\Enums\QuestionType::CheckboxGrid ? 'checkbox' : 'radio' }}" name="{{ $name }}[{{ $row->id }}]{{ $question->type === \App\Enums\QuestionType::CheckboxGrid ? '[]' : '' }}" id="q{{ $question->id }}r{{ $row->id }}o{{ $option->id }}" value="{{ $option->id }}" {{ $disabled }}>
                        <label class="form-check-label" for="q{{ $question->id }}r{{ $row->id }}o{{ $option->id }}">{{ $option->label }}</label>
                    </div>
                @endforeach
            </fieldset>
        @endforeach
    @endif
</div>
