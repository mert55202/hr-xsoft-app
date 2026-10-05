<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Firma',
            'email' => 'ik@hr-xsoft.test',
            'phone' => '02120000000',
            'primary_color' => '#0d6efd',
            'personnel_quota' => 50,
            'founded_on' => '2020-01-01',
        ];
    }
}
