<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'lookup_id', 'unit', 'amount'])]
class LeaveBalance extends Model
{
    protected $table = 'leave_balances';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function lookup(): BelongsTo
    {
        return $this->belongsTo(Lookup::class);
    }
}
