<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'name', 'given_on'])]
class Asset extends Model
{
    protected $table = 'assets';

    protected function casts(): array
    {
        return [
            'given_on' => 'date',
        ];
    }
}
