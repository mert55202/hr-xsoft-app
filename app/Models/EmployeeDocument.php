<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'lookup_id', 'path', 'original_name', 'verified_at'])]
class EmployeeDocument extends Model
{
    protected $table = 'employee_documents';

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }
}
