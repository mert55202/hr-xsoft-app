<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\FormIdentity;
use App\Models\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFormRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'thank_you' => ['nullable', 'string', 'max:2000'],
            'identity' => ['required', Rule::enum(FormIdentity::class)],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'ad',
            'identity' => 'kimlik',
            'starts_on' => 'başlangıç',
            'ends_on' => 'bitiş',
            'thank_you' => 'teşekkür',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formAttributes(): array
    {
        return [
            'name' => $this->string('name')->toString(),
            'description' => $this->filled('description') ? $this->string('description')->toString() : null,
            'thank_you' => $this->filled('thank_you') ? $this->string('thank_you')->toString() : null,
            'identity' => $this->string('identity')->toString(),
            'starts_on' => $this->filled('starts_on') ? $this->date('starts_on')->format('Y-m-d') : null,
            'ends_on' => $this->filled('ends_on') ? $this->date('ends_on')->format('Y-m-d') : null,
        ];
    }
}
