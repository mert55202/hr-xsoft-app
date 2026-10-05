<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['department_id', 'served_on', 'title'])]
class Meal extends Model
{
    protected $table = 'meals';

    protected function casts(): array
    {
        return [
            'served_on' => 'date',
        ];
    }
}
