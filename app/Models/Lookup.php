<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['kind', 'name', 'meta', 'sort'])]
class Lookup extends Model
{
    protected $table = 'lookups';

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }
}
