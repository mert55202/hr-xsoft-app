<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'author_id', 'kind', 'body'])]
class EmployeeNote extends Model
{
    protected $table = 'employee_notes';
}
