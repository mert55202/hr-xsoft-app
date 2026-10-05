<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'amount', 'status'])]
class Offer extends Model
{
    protected $table = 'offers';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }
}
