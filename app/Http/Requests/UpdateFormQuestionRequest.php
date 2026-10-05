<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\FormIdentity;
use App\Enums\QuestionType;
use App\Models\Form;
use App\Models\FormQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateFormQuestionRequest extends FormRequest
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
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $question = $this->route('question');
            $form = $question instanceof FormQuestion ? $question->section?->form : null;

            if (! $form instanceof Form || ! $form->isDraft()) {
                $validator->errors()->add('prompt', 'Yayındaki soru değişmez.');

                return;
            }

            $type = QuestionType::tryFrom((string) $this->input('type'));

            if ($type?->namedOnly() && $form->identity === FormIdentity::Anonymous) {
                $validator->errors()->add('type', 'Anonim formda personel seçimi kurulamaz.');
            }
        });
    }
}
