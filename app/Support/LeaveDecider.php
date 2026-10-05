<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LeaveDecider
{
    public function canDecide(User $actor, LeaveRequest $leave): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        $manager = $leave->employee?->manager();

        return $manager !== null && (int) $manager->user_id === (int) $actor->id;
    }

    public function decide(LeaveRequest $leave, User $actor, string $status): void
    {
        abort_unless($leave->status === 'pending', 404);
        abort_unless($this->canDecide($actor, $leave), 403);

        if ($status === 'rejected') {
            $leave->update(['status' => 'rejected', 'decided_by' => $actor->id]);

            return;
        }

        $needed = $leave->mode === 'hour' ? (float) $leave->hours : $leave->dayCount();
        $balance = LeaveBalance::query()
            ->where('employee_id', $leave->employee_id)
            ->where('lookup_id', $leave->lookup_id)
            ->where('unit', $leave->mode)
            ->first();

        if ($balance === null || (float) $balance->amount < $needed) {
            throw ValidationException::withMessages(['status' => 'Bakiye yetmiyor.']);
        }

        $balance->update(['amount' => round((float) $balance->amount - $needed, 2)]);
        $leave->update(['status' => 'approved', 'decided_by' => $actor->id]);

        if ($leave->employee?->user_id !== null) {
            Notice::query()->create([
                'user_id' => $leave->employee->user_id,
                'body' => 'İzin talebin onaylandı.',
            ]);
        }
    }
}
