<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['kind', 'name', 'meta', 'sort'])]
class Lookup extends Model
{
    protected $table = 'lookups';

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    /**
     * İzin tipinde onay bakiyeden düşer mi. Ayarı olmayan eski kayıt düşer sayılır.
     */
    public function tracksBalance(): bool
    {
        return (bool) ($this->meta['balance'] ?? true);
    }

    /**
     * @return list<string>
     */
    public function leaveUnits(): array
    {
        $unit = $this->meta['unit'] ?? 'both';

        return $unit === 'both' ? ['day', 'hour'] : [$unit];
    }
}
