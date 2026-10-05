<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'phone',
    'vision',
    'mission',
    'kvkk',
    'primary_color',
    'personnel_quota',
    'founded_on',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'founded_on' => 'date',
            'personnel_quota' => 'integer',
        ];
    }

    public static function current(): self
    {
        $company = static::query()->first();

        if ($company === null) {
            return static::query()->create([
                'name' => 'Firma',
                'primary_color' => '#0d6efd',
            ]);
        }

        return $company;
    }
}
