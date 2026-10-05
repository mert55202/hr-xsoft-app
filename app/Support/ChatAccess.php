<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Employee;
use App\Models\User;

class ChatAccess
{
    /**
     * Alt ağaçtaki hesaplar ve doğrudan birinci amir. Yan dal ve amirin amiri yok.
     *
     * @return list<int>
     */
    public function partnerIds(User $user): array
    {
        $self = $user->employee;

        if ($self === null) {
            return [];
        }

        $ids = [];
        $manager = $self->manager();

        if ($manager?->user_id !== null) {
            $ids[] = (int) $manager->user_id;
        }

        $positionIds = $self->position?->subtreeIds() ?? [];

        if ($positionIds !== []) {
            $below = Employee::query()
                ->where('is_candidate', false)
                ->whereIn('position_id', $positionIds)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            foreach ($below as $id) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique(array_filter(
            $ids,
            fn (int $id): bool => $id !== (int) $user->id,
        )));
    }

    public function allows(User $user, User $other): bool
    {
        return in_array((int) $other->id, $this->partnerIds($user), true);
    }
}
