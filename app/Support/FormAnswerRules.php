<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\QuestionType;
use App\Models\Form;
use App\Models\FormQuestion;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class FormAnswerRules
{
    /**
     * @return array<string, mixed>
     */
    public function for(Form $form): array
    {
        $form->loadMissing(['sections.questions.options', 'sections.questions.rows']);
        $rules = [];

        foreach ($form->sections as $section) {
            foreach ($section->questions as $question) {
                $rules = array_merge($rules, $this->questionRules($question));
            }
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    private function questionRules(FormQuestion $question): array
    {
        $key = 'answers.'.$question->id;
        $present = $question->required ? 'required' : 'nullable';
        $option = Rule::exists('form_options', 'id')->where('form_question_id', $question->id);

        return match ($question->type) {
            QuestionType::ShortText => [$key => [$present, 'string', 'max:'.(int) ($question->config['max'] ?? 200)]],
            QuestionType::Paragraph => [$key => [$present, 'string', 'max:'.(int) ($question->config['max'] ?? 2000)]],
            QuestionType::MultipleChoice, QuestionType::Dropdown => [$key => [$present, 'integer', $option]],
            QuestionType::Checkboxes => [
                $key => [$question->required ? 'required' : 'nullable', 'array'],
                $key.'.*' => ['integer', $option],
            ],
            QuestionType::LinearScale => [$key => [
                $present,
                'integer',
                'min:'.(int) ($question->config['scale_min'] ?? 1),
                'max:'.(int) ($question->config['scale_max'] ?? 5),
            ]],
            QuestionType::ChoiceGrid => $this->gridRules($question, $present, $option, false),
            QuestionType::CheckboxGrid => $this->gridRules($question, $present, $option, true),
            QuestionType::Date => [$key => [$present, 'date']],
            QuestionType::Time => [$key => [$present, 'date_format:H:i']],
            QuestionType::EmployeePick => [$key => [$present, 'integer', 'exists:employees,id']],
            QuestionType::File => [$key => [$question->required ? 'required' : 'nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp']],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function gridRules(FormQuestion $question, string $present, Exists $option, bool $multiple): array
    {
        $rules = [];

        foreach ($question->rows as $row) {
            $key = 'answers.'.$question->id.'.'.$row->id;

            if ($multiple) {
                $rules[$key] = [$question->required ? 'required' : 'nullable', 'array'];
                $rules[$key.'.*'] = ['integer', $option];

                continue;
            }

            $rules[$key] = [$present, 'integer', $option];
        }

        return $rules;
    }
}
