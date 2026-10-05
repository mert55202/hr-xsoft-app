<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\FormIdentity;
use App\Enums\QuestionType;
use App\Models\Employee;
use App\Models\Form;
use App\Models\FormAnswer;
use App\Models\FormLink;
use App\Models\FormQuestion;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreFormSubmission
{
    public function store(Form $form, ?User $user, Request $request, ?FormLink $link = null): FormSubmission
    {
        $form->loadMissing(['sections.questions']);
        $employeeId = $this->employeeId($form, $user, $link);

        if ($employeeId !== null && FormSubmission::query()->where('form_id', $form->id)->where('employee_id', $employeeId)->exists()) {
            throw ValidationException::withMessages(['form' => 'Bu formu doldurdun.']);
        }

        return DB::transaction(function () use ($form, $user, $request, $employeeId, $link): FormSubmission {
            $submission = FormSubmission::query()->create([
                'form_id' => $form->id,
                'employee_id' => $form->identity === FormIdentity::Anonymous ? null : $employeeId,
                'department_id' => $user?->department_id ?? $link?->employee?->position?->department_id,
                'submitted_at' => now(),
            ]);

            foreach ($form->sections as $section) {
                foreach ($section->questions as $question) {
                    $this->write($submission, $question, $request);
                }
            }

            if ($link !== null) {
                $link->forceFill(['used_at' => now()])->save();
            }

            return $submission;
        });
    }

    private function employeeId(Form $form, ?User $user, ?FormLink $link): ?int
    {
        if ($form->identity === FormIdentity::Anonymous) {
            return null;
        }

        if ($form->identity === FormIdentity::Link) {
            return $link?->employee_id;
        }

        $employee = $user?->employee;

        if (! $employee instanceof Employee) {
            throw ValidationException::withMessages(['form' => 'Bu form için personel kartın olmalı.']);
        }

        return $employee->id;
    }

    private function write(FormSubmission $submission, FormQuestion $question, Request $request): void
    {
        $value = $request->input('answers.'.$question->id);

        match ($question->type) {
            QuestionType::ShortText, QuestionType::Paragraph => $this->text($submission, $question, is_string($value) ? $value : null),
            QuestionType::MultipleChoice, QuestionType::Dropdown => $this->option($submission, $question, $value),
            QuestionType::Checkboxes => $this->options($submission, $question, is_array($value) ? $value : []),
            QuestionType::LinearScale => $this->number($submission, $question, $value),
            QuestionType::ChoiceGrid => $this->grid($submission, $question, is_array($value) ? $value : [], false),
            QuestionType::CheckboxGrid => $this->grid($submission, $question, is_array($value) ? $value : [], true),
            QuestionType::Date => $this->date($submission, $question, is_string($value) ? $value : null),
            QuestionType::Time => $this->time($submission, $question, is_string($value) ? $value : null),
            QuestionType::EmployeePick => $this->text($submission, $question, is_numeric($value) ? (string) $value : null),
            QuestionType::File => $this->file($submission, $question, $request),
        };
    }

    private function text(FormSubmission $submission, FormQuestion $question, ?string $body): void
    {
        if ($body === null || $body === '') {
            return;
        }

        $this->row($submission, $question, ['body' => $body]);
    }

    private function option(FormSubmission $submission, FormQuestion $question, mixed $optionId): void
    {
        if (! is_numeric($optionId)) {
            return;
        }

        $this->row($submission, $question, ['form_option_id' => (int) $optionId]);
    }

    /**
     * @param  array<int, mixed>  $optionIds
     */
    private function options(FormSubmission $submission, FormQuestion $question, array $optionIds): void
    {
        foreach ($optionIds as $optionId) {
            $this->option($submission, $question, $optionId);
        }
    }

    private function number(FormSubmission $submission, FormQuestion $question, mixed $value): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $this->row($submission, $question, ['value_number' => $value]);
    }

    /**
     * @param  array<int|string, mixed>  $rows
     */
    private function grid(FormSubmission $submission, FormQuestion $question, array $rows, bool $multiple): void
    {
        foreach ($rows as $rowId => $option) {
            if ($multiple && is_array($option)) {
                foreach ($option as $optionId) {
                    if (is_numeric($optionId)) {
                        $this->row($submission, $question, [
                            'form_row_id' => (int) $rowId,
                            'form_option_id' => (int) $optionId,
                        ]);
                    }
                }

                continue;
            }

            if (is_numeric($option)) {
                $this->row($submission, $question, [
                    'form_row_id' => (int) $rowId,
                    'form_option_id' => (int) $option,
                ]);
            }
        }
    }

    private function date(FormSubmission $submission, FormQuestion $question, ?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->row($submission, $question, ['value_date' => $value]);
    }

    private function time(FormSubmission $submission, FormQuestion $question, ?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->row($submission, $question, ['value_time' => $value]);
    }

    private function file(FormSubmission $submission, FormQuestion $question, Request $request): void
    {
        $file = $request->file('answers.'.$question->id);

        if ($file === null) {
            return;
        }

        $path = $file->store('forms/'.$submission->id, 'local');
        $this->row($submission, $question, ['body' => $path]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function row(FormSubmission $submission, FormQuestion $question, array $attributes): void
    {
        FormAnswer::query()->create([
            'form_submission_id' => $submission->id,
            'form_question_id' => $question->id,
            ...$attributes,
        ]);
    }
}
