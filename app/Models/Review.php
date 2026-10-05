<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'reviewer_id', 'collar', 'period', 'body', 'status'])]
class Review extends Model
{
    protected $table = 'reviews';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
