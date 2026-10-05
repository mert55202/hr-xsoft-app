<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeEducationRequest;
use App\Models\Employee;
use App\Models\EmployeeEducation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EmployeeEducationController extends Controller
{
    public function store(StoreEmployeeEducationRequest $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);

        $employee->educations()->create([
            'school' => $request->string('school')->toString(),
            'field' => $request->filled('field') ? $request->string('field')->toString() : null,
            'graduated_on' => $request->filled('graduated_on') ? $request->date('graduated_on') : null,
        ]);

        return redirect()
            ->route('personnel.show', $employee)
            ->with('status', 'Öğrenim eklendi.');
    }

    public function destroy(Employee $employee, EmployeeEducation $education): RedirectResponse
    {
        Gate::authorize('update', $employee);
        abort_unless($education->employee_id === $employee->id, 404);

        $education->delete();

        return redirect()
            ->route('personnel.show', $employee)
            ->with('status', 'Öğrenim silindi.');
    }
}
