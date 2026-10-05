<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'name', 'kinship'])]
class EmployeeRelative extends Model
{
    protected $table = 'employee_relatives';
}
