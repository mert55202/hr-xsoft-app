<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Collar;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PositionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'collar' => ['required', Rule::enum(Collar::class)],
            'parent_id' => ['nullable', 'integer', 'exists:positions,id'],
            'headcount' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $parentId = $this->integer('parent_id');
            $position = $this->route('position');

            if ($parentId !== 0 && $position instanceof Position && ($position->id === $parentId || in_array($parentId, $position->subtreeIds(), true))) {
                $validator->errors()->add('parent_id', 'Amir, bu pozisyonun altı olamaz.');
            }

            $departmentId = $this->integer('department_id');

            if ($departmentId === 0) {
                return;
            }

            $department = Department::query()->find($departmentId);
            $limit = $department?->headcount;

            if ($limit === null) {
                return;
            }

            $current = $this->route('position');
            $used = (int) Position::query()
                ->where('department_id', $departmentId)
                ->when($current instanceof Position, fn ($query) => $query->whereKeyNot($current->id))
                ->sum('headcount');

            if ($used + $this->integer('headcount') > $limit) {
                $validator->errors()->add('headcount', "{$department->name} en fazla {$limit} kişi. Bu kadro sınırı aşıyor.");
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Pozisyon adı gerekli.',
            'collar.required' => 'Yaka seçin.',
            'parent_id.exists' => 'Seçilen üst pozisyon yok.',
            'headcount.min' => 'Norm kadro en az 1.',
        ];
    }
}
