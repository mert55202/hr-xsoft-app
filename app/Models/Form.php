<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FormIdentity;
use App\Enums\FormStatus;
use Database\Factories\FormFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['template', 'name', 'description', 'thank_you', 'identity', 'status', 'starts_on', 'ends_on'])]
class Form extends Model
{
    /** @use HasFactory<FormFactory> */
    use HasFactory;

    protected $table = 'forms';

    protected static function booted(): void
    {
        static::creating(function (Form $form): void {
            $form->public_id ??= (string) Str::uuid();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'identity' => FormIdentity::class,
            'status' => FormStatus::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function isOpen(): bool
    {
        if ($this->status !== FormStatus::Published) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->starts_on !== null && $this->starts_on->greaterThan($today)) {
            return false;
        }

        if ($this->ends_on !== null && $this->ends_on->endOfDay()->lessThan(now())) {
            return false;
        }

        return true;
    }

    public function isDraft(): bool
    {
        return $this->status === FormStatus::Draft;
    }

    /**
     * @return HasMany<FormSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(FormSection::class)->orderBy('sort');
    }

    /**
     * @return HasMany<FormSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    /**
     * @return HasMany<FormLink, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(FormLink::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
