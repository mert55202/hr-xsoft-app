<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['form_id', 'title', 'description', 'sort'])]
class FormSection extends Model
{
    protected $table = 'form_sections';

    /**
     * @return BelongsTo<Form, $this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * @return HasMany<FormQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(FormQuestion::class)->orderBy('sort');
    }
}
