<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'lookup_id', 'body', 'status'])]
class WorkRequest extends Model
{
    protected $table = 'work_requests';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
