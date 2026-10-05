<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_id', 'held_on', 'pre_form_id', 'post_form_id'])]
class CourseSession extends Model
{
    protected $table = 'course_sessions';

    protected function casts(): array
    {
        return [
            'held_on' => 'date',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function preForm(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'pre_form_id');
    }

    public function postForm(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'post_form_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
