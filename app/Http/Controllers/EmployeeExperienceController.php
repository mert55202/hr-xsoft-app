<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeExperienceRequest;
use App\Models\Employee;
use App\Models\EmployeeExperience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EmployeeExperienceController extends Controller
{
    public function store(StoreEmployeeExperienceRequest $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);

        $employee->experiences()->create([
            'company' => $request->string('company')->toString(),
            'title' => $request->string('title')->toString(),
            'started_on' => $request->filled('started_on') ? $request->date('started_on') : null,
            'ended_on' => $request->filled('ended_on') ? $request->date('ended_on') : null,
        ]);

        return redirect()
            ->route('personnel.show', $employee)
            ->with('status', 'Deneyim eklendi.');
    }

    public function destroy(Employee $employee, EmployeeExperience $experience): RedirectResponse
    {
        Gate::authorize('update', $employee);
        abort_unless($experience->employee_id === $employee->id, 404);

        $experience->delete();

        return redirect()
            ->route('personnel.show', $employee)
            ->with('status', 'Deneyim silindi.');
    }
}
