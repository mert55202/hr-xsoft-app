<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'year', 'month', 'hours', 'gross', 'deduction', 'net'])]
class Payslip extends Model
{
    protected $table = 'payslips';

    protected function casts(): array
    {
        return [
            'hours' => 'decimal:2',
            'gross' => 'decimal:2',
            'deduction' => 'decimal:2',
            'net' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
