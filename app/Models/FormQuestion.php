<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['form_section_id', 'type', 'prompt', 'help', 'required', 'sort', 'config'])]
class FormQuestion extends Model
{
    protected $table = 'form_questions';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'required' => 'boolean',
            'config' => 'array',
        ];
    }

    /**
     * @return BelongsTo<FormSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(FormSection::class, 'form_section_id');
    }

    /**
     * @return HasMany<FormOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(FormOption::class)->orderBy('sort');
    }

    /**
     * @return HasMany<FormRow, $this>
     */
    public function rows(): HasMany
    {
        return $this->hasMany(FormRow::class)->orderBy('sort');
    }
}
