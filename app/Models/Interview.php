<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'interviewer_id', 'held_on', 'notes'])]
class Interview extends Model
{
    protected $table = 'interviews';

    protected function casts(): array
    {
        return [
            'held_on' => 'date',
        ];
    }
}
