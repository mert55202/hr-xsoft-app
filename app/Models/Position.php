<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Collar;
use Database\Factories\PositionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'department_id', 'collar', 'parent_id', 'headcount'])]
class Position extends Model
{
    /** @use HasFactory<PositionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'collar' => Collar::class,
            'headcount' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function secondManager(): ?self
    {
        return $this->parent?->parent;
    }

    /**
     * @return list<int>
     */
    public function subtreeIds(): array
    {
        $byParent = self::query()->get(['id', 'parent_id'])->groupBy('parent_id');
        $ids = [];

        $walk = function (int $current) use (&$walk, $byParent, &$ids): void {
            foreach ($byParent->get($current, collect()) as $child) {
                $ids[] = $child->id;
                $walk($child->id);
            }
        };

        $walk($this->id);

        return $ids;
    }

    /**
     * @return Collection<int, self>
     */
    public static function looseRoots(): Collection
    {
        $roots = self::query()->whereNull('parent_id')->orderBy('name')->get();

        return $roots->count() > 1 ? $roots : new Collection;
    }
}
