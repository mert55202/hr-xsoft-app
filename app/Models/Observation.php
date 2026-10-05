<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'author_id', 'body', 'observed_on'])]
class Observation extends Model
{
    protected $table = 'observations';

    protected function casts(): array
    {
        return [
            'observed_on' => 'date',
        ];
    }
}
