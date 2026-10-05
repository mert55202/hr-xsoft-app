<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\PayType;
use App\Models\Employee;

class PayrollMath
{
    public static function gross(Employee $employee, ?float $hours): float
    {
        $amount = (float) $employee->pay_amount;

        return match ($employee->pay_type) {
            PayType::Hourly => round($amount * (float) $hours, 2),
            PayType::Monthly => round($amount, 2),
            PayType::Yearly => round($amount / 12, 2),
            default => 0.0,
        };
    }
}
