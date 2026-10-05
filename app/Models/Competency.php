<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['position_id', 'name'])]
class Competency extends Model
{
    protected $table = 'competencies';

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
