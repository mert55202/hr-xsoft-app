<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PositionRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Support\EmployeeAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function chart(EmployeeAccess $access): View
    {
        Gate::authorize('viewChart', Position::class);

        $positions = Position::query()->with(['department', 'parent.parent'])->orderBy('name')->get();

        return view('positions.chart', [
            'roots' => $positions->whereNull('parent_id')->values(),
            'children' => $positions->whereNotNull('parent_id')->groupBy('parent_id'),
            'looseRoots' => Position::looseRoots(),
            'employees' => Employee::query()->where('is_candidate', false)->orderBy('last_name')->get()->groupBy('position_id'),
            'visibleIds' => $access->visibleIds(auth()->user()),
            'vacantParents' => $this->vacantParents($positions),
        ]);
    }

    public function index(): View
    {
        Gate::authorize('manage', Position::class);

        return view('positions.index', [
            'positions' => Position::query()->with(['department', 'parent.parent'])->orderBy('name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'parents' => Position::query()->orderBy('name')->get(),
            'looseRoots' => Position::looseRoots(),
            'vacantParents' => $this->vacantParents(Position::query()->with('parent')->get()),
        ]);
    }

    public function store(PositionRequest $request): RedirectResponse
    {
        Gate::authorize('manage', Position::class);

        Position::query()->create($this->attributes($request));

        return redirect()
            ->route('positions.index')
            ->with('status', 'Pozisyon eklendi.');
    }

    public function edit(Position $position): View
    {
        Gate::authorize('manage', Position::class);

        $position->load(['department', 'parent.parent']);

        $blocked = [$position->id, ...$position->subtreeIds()];

        return view('positions.edit', [
            'position' => $position,
            'departments' => Department::query()->orderBy('name')->get(),
            'parents' => Position::query()->whereKeyNot($blocked)->orderBy('name')->get(),
            'looseRoots' => Position::looseRoots(),
        ]);
    }

    public function update(PositionRequest $request, Position $position): RedirectResponse
    {
        Gate::authorize('manage', Position::class);

        $position->update($this->attributes($request));

        return redirect()
            ->route('positions.index')
            ->with('status', 'Pozisyon güncellendi.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        Gate::authorize('manage', Position::class);

        $position->delete();

        return redirect()
            ->route('positions.index')
            ->with('status', 'Pozisyon silindi. Alt pozisyonların üstü boşaldı.');
    }

    /**
     * @return array{name: string, department_id: int|null, collar: string, parent_id: int|null, headcount: int}
     */
    private function attributes(PositionRequest $request): array
    {
        $parentId = $request->integer('parent_id');

        return [
            'name' => $request->string('name')->toString(),
            'department_id' => $request->filled('department_id') ? $request->integer('department_id') : null,
            'collar' => $request->string('collar')->toString(),
            'parent_id' => $parentId > 0 ? $parentId : null,
            'headcount' => $request->integer('headcount'),
        ];
    }

    /**
     * @param  Collection<int, Position>  $positions
     * @return Collection<int, string>
     */
    private function vacantParents(Collection $positions): Collection
    {
        $occupied = Employee::query()
            ->where('is_candidate', false)
            ->whereNotNull('position_id')
            ->pluck('position_id');

        return $positions
            ->filter(fn (Position $position): bool => $position->parent_id !== null && ! $occupied->contains($position->parent_id))
            ->map(fn (Position $position): string => (string) $position->parent?->name)
            ->filter()
            ->unique()
            ->values();
    }
}
