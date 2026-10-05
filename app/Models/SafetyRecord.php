<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'body', 'recorded_on'])]
class SafetyRecord extends Model
{
    protected $table = 'safety_records';

    protected function casts(): array
    {
        return [
            'recorded_on' => 'date',
        ];
    }
}
