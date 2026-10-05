<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AppModule;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDepartmentModulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(AppModule::grantableValues())],
            'headcount' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('headcount')) {
                return;
            }

            $department = $this->route('department');

            if (! $department instanceof Department) {
                return;
            }

            $used = (int) $department->positions()->sum('headcount');

            if ($used > $this->integer('headcount')) {
                $validator->errors()->add('headcount', "Pozisyon kadrolarının toplamı {$used}. Sınır en az bu kadar olmalı.");
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'modules.*.in' => 'Bu modül departmana verilemez.',
            'headcount.min' => 'Kişi sınırı en az 1.',
        ];
    }
}
