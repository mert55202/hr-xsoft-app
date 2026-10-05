<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FormIdentity;
use App\Enums\FormStatus;
use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Anket',
            'identity' => FormIdentity::Named,
            'status' => FormStatus::Draft,
        ];
    }
}
