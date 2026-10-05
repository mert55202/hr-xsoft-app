<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeEducationRequest extends FormRequest
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
            'school' => ['required', 'string', 'max:160'],
            'field' => ['nullable', 'string', 'max:160'],
            'graduated_on' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'school' => 'okul',
            'field' => 'bölüm',
            'graduated_on' => 'mezuniyet',
        ];
    }
}
