<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PayType;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'position_id',
    'is_candidate',
    'first_name',
    'last_name',
    'email',
    'phone',
    'national_id',
    'birth_date',
    'started_on',
    'pay_type',
    'pay_amount',
    'currency',
    'left_on',
])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'started_on' => 'date',
            'left_on' => 'date',
            'is_candidate' => 'boolean',
            'pay_type' => PayType::class,
            'pay_amount' => 'decimal:2',
        ];
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * @return HasMany<EmployeeEducation, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class)->orderByDesc('graduated_on');
    }

    /**
     * @return HasMany<EmployeeExperience, $this>
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(EmployeeExperience::class)->orderByDesc('started_on');
    }

    public function manager(): ?self
    {
        $parentId = $this->position?->parent_id;

        if ($parentId === null) {
            return null;
        }

        return self::query()
            ->where('position_id', $parentId)
            ->where('is_candidate', false)
            ->orderBy('id')
            ->first();
    }

    /**
     * @return HasMany<Interview, $this>
     */
    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    /**
     * @return HasMany<CandidateReference, $this>
     */
    public function references(): HasMany
    {
        return $this->hasMany(CandidateReference::class);
    }

    /**
     * @return HasMany<Offer, $this>
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * @return HasMany<Asset, $this>
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * @return HasMany<EmployeeDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * @return HasOne<EmployeeHealth, $this>
     */
    public function health(): HasOne
    {
        return $this->hasOne(EmployeeHealth::class);
    }

    /**
     * @return HasMany<EmployeeRelative, $this>
     */
    public function relatives(): HasMany
    {
        return $this->hasMany(EmployeeRelative::class);
    }

    /**
     * @return HasMany<EmployeeNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(EmployeeNote::class);
    }

    /**
     * @return HasMany<SafetyRecord, $this>
     */
    public function safetyRecords(): HasMany
    {
        return $this->hasMany(SafetyRecord::class);
    }
}
