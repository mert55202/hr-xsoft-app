<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'school', 'field', 'graduated_on'])]
class EmployeeEducation extends Model
{
    protected $table = 'employee_educations';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'graduated_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
