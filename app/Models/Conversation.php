<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['low_user_id', 'high_user_id'])]
class Conversation extends Model
{
    protected $table = 'conversations';

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
