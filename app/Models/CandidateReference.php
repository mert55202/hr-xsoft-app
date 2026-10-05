<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'name', 'notes'])]
class CandidateReference extends Model
{
    protected $table = 'candidate_references';
}
