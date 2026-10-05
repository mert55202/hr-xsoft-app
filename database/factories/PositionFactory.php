<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Collar;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Pozisyon',
            'collar' => Collar::White,
            'headcount' => 1,
        ];
    }
}
