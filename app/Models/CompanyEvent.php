<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['department_id', 'title', 'starts_on'])]
class CompanyEvent extends Model
{
    protected $table = 'events';

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
        ];
    }
}
