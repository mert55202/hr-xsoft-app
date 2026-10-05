<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'lookup_id',
    'mode',
    'starts_on',
    'ends_on',
    'starts_at',
    'ends_at',
    'hours',
    'status',
    'decided_by',
])]
class LeaveRequest extends Model
{
    protected $table = 'leave_requests';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'hours' => 'decimal:2',
        ];
    }

    public function dayCount(): int
    {
        $end = $this->ends_on ?? $this->starts_on;

        return (int) $this->starts_on->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<Lookup, $this>
     */
    public function lookup(): BelongsTo
    {
        return $this->belongsTo(Lookup::class);
    }
}
