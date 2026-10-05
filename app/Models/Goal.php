<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'title', 'score'])]
class Goal extends Model
{
    protected $table = 'goals';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
