<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PayType;
use App\Models\Employee;
use App\Models\Position;
use App\Support\EmployeeAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $employee = $this->route('employee');

        if ($employee instanceof Employee) {
            return $user->can('update', $employee);
        }

        return $user->can('create', Employee::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:32'],
            'national_id' => ['nullable', 'string', 'max:11', Rule::unique('employees', 'national_id')->ignore($employee)],
            'birth_date' => ['nullable', 'date'],
            'started_on' => ['nullable', 'date'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id', Rule::unique('employees', 'user_id')->ignore($employee)],
            'pay_type' => ['nullable', Rule::enum(PayType::class)],
            'pay_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'ad',
            'last_name' => 'soyad',
            'national_id' => 'T.C. kimlik no',
            'birth_date' => 'doğum tarihi',
            'started_on' => 'işe giriş',
            'position_id' => 'pozisyon',
            'user_id' => 'hesap',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function employeeAttributes(): array
    {
        return [
            'first_name' => $this->string('first_name')->toString(),
            'last_name' => $this->string('last_name')->toString(),
            'email' => $this->filled('email') ? $this->string('email')->toString() : null,
            'phone' => $this->filled('phone') ? $this->string('phone')->toString() : null,
            'national_id' => $this->filled('national_id') ? $this->string('national_id')->toString() : null,
            'birth_date' => $this->filled('birth_date') ? $this->date('birth_date') : null,
            'started_on' => $this->filled('started_on') ? $this->date('started_on') : null,
            'position_id' => $this->integer('position_id'),
            'user_id' => $this->filled('user_id') ? $this->integer('user_id') : null,
            'pay_type' => $this->filled('pay_type') ? PayType::from($this->string('pay_type')->toString()) : null,
            'pay_amount' => $this->filled('pay_amount') ? $this->input('pay_amount') : null,
            'currency' => $this->filled('currency') ? $this->string('currency')->toString() : 'TRY',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->filled('pay_type') && ! $this->filled('pay_amount')) {
                $validator->errors()->add('pay_amount', 'Maaş türü seçilince tutar gerekir.');

                return;
            }

            $user = $this->user();
            $position = Position::query()->with('department')->find($this->integer('position_id'));

            if ($position === null || $user === null) {
                return;
            }

            if (! $user->isAdmin()) {
                $allowed = app(EmployeeAccess::class)->assignablePositionIds($user);

                if (! in_array($position->id, $allowed, true)) {
                    $validator->errors()->add('position_id', 'Bu pozisyon senin altında değil.');

                    return;
                }
            }

            $current = $this->route('employee');
            $except = $current instanceof Employee ? $current->id : null;

            $onSeat = Employee::query()
                ->where('is_candidate', false)
                ->where('position_id', $position->id)
                ->when($except !== null, fn ($query) => $query->whereKeyNot($except))
                ->count();

            if ($onSeat >= $position->headcount) {
                $validator->errors()->add('position_id', "{$position->name} kadrosu dolu ({$position->headcount}).");
            }

            $limit = $position->department?->headcount;

            if ($limit === null) {
                return;
            }

            $inDepartment = Employee::query()
                ->where('is_candidate', false)
                ->whereHas('position', fn ($query) => $query->where('department_id', $position->department_id))
                ->when($except !== null, fn ($query) => $query->whereKeyNot($except))
                ->count();

            if ($inDepartment >= $limit) {
                $validator->errors()->add('position_id', "{$position->department->name} en fazla {$limit} kişi.");
            }
        });
    }
}
