<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['form_question_id', 'label', 'sort', 'points', 'is_correct'])]
class FormOption extends Model
{
    protected $table = 'form_options';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'points' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<FormQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'form_question_id');
    }
}
