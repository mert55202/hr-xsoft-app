<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'body', 'read_at'])]
class Notice extends Model
{
    protected $table = 'notices';

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
