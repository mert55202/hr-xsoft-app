<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['department_id', 'title', 'done'])]
class WorkTask extends Model
{
    protected $table = 'tasks';

    protected function casts(): array
    {
        return [
            'done' => 'boolean',
        ];
    }
}
