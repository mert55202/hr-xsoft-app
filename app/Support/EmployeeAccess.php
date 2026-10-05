<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class EmployeeAccess
{
    public function canView(User $viewer, Employee $employee): bool
    {
        if ($viewer->isAdmin()) {
            return true;
        }

        $ids = $this->visibleIds($viewer);

        return in_array($employee->id, $ids ?? [], true);
    }

    public function canManage(User $viewer, Employee $employee): bool
    {
        if ($viewer->isAdmin()) {
            return true;
        }

        $self = $viewer->employee;

        if ($self === null || $self->is($employee)) {
            return false;
        }

        return $this->canView($viewer, $employee);
    }

    /**
     * Null means every card. Otherwise the viewer's own id plus people on descendant positions.
     *
     * @return list<int>|null
     */
    public function visibleIds(User $viewer): ?array
    {
        if ($viewer->isAdmin()) {
            return null;
        }

        return $this->visibleQuery($viewer)->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
    }

    /**
     * @return Builder<Employee>
     */
    public function visibleQuery(User $viewer): Builder
    {
        if ($viewer->isAdmin()) {
            return Employee::query();
        }

        $self = $viewer->employee;

        if ($self === null) {
            return Employee::query()->whereKey([]);
        }

        $positionIds = [];

        if ($self->position_id !== null) {
            $position = Position::query()->find($self->position_id);
            $positionIds = $position?->subtreeIds() ?? [];
        }

        return Employee::query()->where(function (Builder $query) use ($self, $positionIds): void {
            $query->whereKey($self->id);

            if ($positionIds !== []) {
                $query->orWhereIn('position_id', $positionIds);
            }
        });
    }

    /**
     * Positions a non-admin may assign: strictly below their own seat.
     *
     * @return list<int>
     */
    public function assignablePositionIds(User $viewer): array
    {
        $self = $viewer->employee;

        if ($self?->position_id === null) {
            return [];
        }

        $position = Position::query()->find($self->position_id);

        return $position?->subtreeIds() ?? [];
    }
}
