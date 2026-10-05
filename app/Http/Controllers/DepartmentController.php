<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AppModule;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentModulesRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Department::class);

        return view('departments.index', [
            'departments' => Department::query()->orderBy('name')->get(),
            'modules' => AppModule::grantableCases(),
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        Gate::authorize('create', Department::class);

        Department::query()->create([
            'name' => $request->validated('name'),
            'headcount' => $request->filled('headcount') ? $request->integer('headcount') : null,
            'modules' => [],
        ]);

        return redirect()
            ->route('departments.index')
            ->with('status', 'Departman eklendi.');
    }

    public function update(UpdateDepartmentModulesRequest $request, Department $department): RedirectResponse
    {
        Gate::authorize('update', $department);

        $modules = collect($request->validated('modules') ?? [])
            ->map(fn (string $value): AppModule => AppModule::from($value))
            ->filter(fn (AppModule $module): bool => $module->grantable())
            ->map(fn (AppModule $module): string => $module->value)
            ->values()
            ->all();

        $attributes = ['modules' => $modules];

        if ($request->exists('headcount')) {
            $attributes['headcount'] = $request->filled('headcount') ? $request->integer('headcount') : null;
        }

        $department->update($attributes);

        return redirect()
            ->route('departments.index')
            ->with('status', 'Modül listesi güncellendi.');
    }
}
