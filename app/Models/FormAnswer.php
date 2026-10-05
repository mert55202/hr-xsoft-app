<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'form_submission_id',
    'form_question_id',
    'form_option_id',
    'form_row_id',
    'body',
    'value_number',
    'value_date',
    'value_time',
])]
class FormAnswer extends Model
{
    protected $table = 'form_answers';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value_number' => 'decimal:2',
            'value_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<FormSubmission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    /**
     * @return BelongsTo<FormQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'form_question_id');
    }

    /**
     * @return BelongsTo<FormOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(FormOption::class, 'form_option_id');
    }

    /**
     * @return BelongsTo<FormRow, $this>
     */
    public function row(): BelongsTo
    {
        return $this->belongsTo(FormRow::class, 'form_row_id');
    }
}
