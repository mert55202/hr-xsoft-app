<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeMove;
use App\Models\Position;
use App\Models\User;
use App\Support\EmployeeAccess;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(EmployeeAccess $access): View
    {
        Gate::authorize('viewAny', Employee::class);

        $viewer = auth()->user();

        return view('employees.index', [
            'employees' => $access->visibleQuery($viewer)
                ->where('is_candidate', false)
                ->with(['position.department', 'position.parent'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
            'canCreate' => $viewer->can('create', Employee::class),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Employee::class);

        return view('employees.create', [
            'employee' => new Employee,
            'positions' => Position::query()->orderBy('name')->get(),
            'users' => $this->freeUsers(),
        ]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        Gate::authorize('create', Employee::class);

        $employee = Employee::query()->create($request->employeeAttributes());

        return redirect()
            ->route('personnel.show', $employee)
            ->with('status', 'Personel eklendi.');
    }

    public function show(Employee $employee, EmployeeAccess $access): View
    {
        Gate::authorize('view', $employee);

        $employee->load([
            'position.department',
            'position.parent.parent',
            'user.department',
            'educations',
            'experiences',
            'assets',
            'documents',
            'health',
            'relatives',
            'notes',
            'safetyRecords',
        ]);

        $parentId = $employee->position?->parent_id;
        $grandId = $employee->position?->parent?->parent_id;
        $seated = Employee::query()
            ->whereIn('position_id', array_filter([$parentId, $grandId]))
            ->orderBy('last_name')
            ->get()
            ->groupBy('position_id');

        return view('employees.show', [
            'employee' => $employee,
            'firstManagers' => $parentId === null ? collect() : $seated->get($parentId, collect()),
            'secondManagers' => $grandId === null ? collect() : $seated->get($grandId, collect()),
            'visibleIds' => $access->visibleIds(auth()->user()),
            'canManage' => auth()->user()->can('update', $employee),
        ]);
    }

    public function edit(Employee $employee, EmployeeAccess $access): View
    {
        Gate::authorize('update', $employee);

        return view('employees.edit', [
            'employee' => $employee,
            'positions' => $this->positionsFor(auth()->user(), $access, $employee),
            'users' => $this->freeUsers($employee),
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);

        $from = $employee->position_id;
        $employee->update($request->employeeAttributes());

        if ((int) $from !== (int) $employee->position_id) {
            EmployeeMove::query()->create([
                'employee_id' => $employee->id,
                'from_position_id' => $from,
                'to_position_id' => $employee->position_id,
                'moved_on' => now()->toDateString(),
            ]);
        }

        return redirect()
            ->route('personnel.show', $employee)
            ->with('status', 'Personel güncellendi.');
    }

    public function modules(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);
        $account = $employee->user;
        abort_unless($account !== null, 404);

        $data = $request->validate([
            'hidden_modules' => ['nullable', 'array'],
            'hidden_modules.*' => ['string'],
        ]);
        $allowed = $account->department?->modules ?? [];
        $hidden = $data['hidden_modules'] ?? [];
        $extra = array_diff($hidden, $allowed);

        if ($extra !== []) {
            throw ValidationException::withMessages([
                'hidden_modules' => 'Departmanda kapalı modül kişiye yazılmaz.',
            ]);
        }

        $account->update(['hidden_modules' => array_values($hidden)]);

        return back()->with('status', 'Modül istisnası yazıldı.');
    }

    /**
     * @return Collection<int, Position>
     */
    private function positionsFor(User $user, EmployeeAccess $access, Employee $employee): Collection
    {
        if ($user->isAdmin()) {
            return Position::query()->orderBy('name')->get();
        }

        $ids = $access->assignablePositionIds($user);

        if ($employee->position_id !== null) {
            $ids[] = $employee->position_id;
        }

        return Position::query()->whereIn('id', $ids)->orderBy('name')->get();
    }

    /**
     * @return Collection<int, User>
     */
    private function freeUsers(?Employee $employee = null): Collection
    {
        return User::query()
            ->where(function ($query) use ($employee): void {
                $query->whereDoesntHave('employee');

                if ($employee?->user_id !== null) {
                    $query->orWhereKey($employee->user_id);
                }
            })
            ->orderBy('name')
            ->get();
    }
}
