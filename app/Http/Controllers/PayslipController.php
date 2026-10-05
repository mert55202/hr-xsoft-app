<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PayType;
use App\Models\Employee;
use App\Models\Payslip;
use App\Support\EmployeeAccess;
use App\Support\PayrollMath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PayslipController extends Controller
{
    public function index(EmployeeAccess $access): View
    {
        $user = auth()->user();
        $visible = $access->visibleIds($user);
        $query = Payslip::query()->with('employee')->orderByDesc('year')->orderByDesc('month');

        if ($visible !== null) {
            $query->whereIn('employee_id', $visible === [] ? [0] : $visible);
        }

        $manageable = Employee::query()->where('is_candidate', false)->orderBy('last_name')->get()
            ->filter(fn (Employee $employee): bool => $user->can('update', $employee))
            ->values();

        return view('payroll.index', [
            'payslips' => $query->get(),
            'manageable' => $manageable,
        ]);
    }

    public function store(Request $request, EmployeeAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'hours' => ['nullable', 'numeric', 'min:0', 'max:400'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
        ]);

        $employee = Employee::query()->findOrFail($data['employee_id']);
        abort_unless($access->canManage($request->user(), $employee), 403);
        abort_if($employee->pay_type === null || $employee->pay_amount === null, 422);

        $hours = isset($data['hours']) ? (float) $data['hours'] : null;

        if ($employee->pay_type === PayType::Hourly && ($hours === null || $hours <= 0)) {
            throw ValidationException::withMessages(['hours' => 'Saatlik personelde çalışılan saat gerekir.']);
        }

        $gross = PayrollMath::gross($employee, $hours);
        $deduction = (float) ($data['deduction'] ?? 0);

        Payslip::query()->updateOrCreate(
            ['employee_id' => $employee->id, 'year' => $data['year'], 'month' => $data['month']],
            [
                'hours' => $hours,
                'gross' => $gross,
                'deduction' => $deduction,
                'net' => round($gross - $deduction, 2),
            ],
        );

        return back()->with('status', 'Bordro yazıldı.');
    }
}
