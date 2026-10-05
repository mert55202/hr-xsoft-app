<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'from_position_id', 'to_position_id', 'moved_on'])]
class EmployeeMove extends Model
{
    protected $table = 'employee_moves';

    protected function casts(): array
    {
        return [
            'moved_on' => 'date',
        ];
    }
}
