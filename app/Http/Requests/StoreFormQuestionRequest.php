<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\FormIdentity;
use App\Enums\QuestionType;
use App\Models\Form;
use App\Models\FormSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFormQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', Form::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:300'],
            'help' => ['nullable', 'string', 'max:300'],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'required' => ['nullable', 'boolean'],
            'scale_min' => ['nullable', 'integer', 'min:0', 'max:10'],
            'scale_max' => ['nullable', 'integer', 'min:1', 'max:10'],
            'max' => ['nullable', 'integer', 'min:1', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'prompt' => 'soru',
            'type' => 'tür',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $section = $this->route('section');
            $form = $section instanceof FormSection ? $section->form : null;

            if (! $form instanceof Form || ! $form->isDraft()) {
                $validator->errors()->add('prompt', 'Yayındaki forma soru eklenmez.');

                return;
            }

            $type = QuestionType::tryFrom((string) $this->input('type'));

            if ($type?->namedOnly() && $form->identity === FormIdentity::Anonymous) {
                $validator->errors()->add('type', 'Anonim formda personel seçimi kurulamaz.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function questionAttributes(FormSection $section): array
    {
        $type = QuestionType::from($this->string('type')->toString());
        $config = [];

        if ($type === QuestionType::LinearScale) {
            $config = [
                'scale_min' => $this->filled('scale_min') ? $this->integer('scale_min') : 1,
                'scale_max' => $this->filled('scale_max') ? $this->integer('scale_max') : 5,
            ];
        }

        if (in_array($type, [QuestionType::ShortText, QuestionType::Paragraph], true) && $this->filled('max')) {
            $config['max'] = $this->integer('max');
        }

        return [
            'form_section_id' => $section->id,
            'type' => $type,
            'prompt' => $this->string('prompt')->toString(),
            'help' => $this->filled('help') ? $this->string('help')->toString() : null,
            'required' => $this->boolean('required'),
            'sort' => ((int) $section->questions()->max('sort')) + 1,
            'config' => $config === [] ? null : $config,
        ];
    }
}
