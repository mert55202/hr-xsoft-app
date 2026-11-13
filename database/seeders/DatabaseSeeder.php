<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AppModule;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Department;
use App\Models\Lookup;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Company::query()->doesntExist()) {
            Company::query()->create([
                'name' => 'Firma',
                'primary_color' => '#0d6efd',
            ]);
        }

        $department = Department::query()->firstOrCreate(
            ['name' => 'Genel'],
            ['modules' => [AppModule::Survey->value, AppModule::OrgChart->value]],
        );

        $this->seedLeaveTypes();

        $email = (string) env('ADMIN_EMAIL', 'admin@hr-xsoft.test');
        $admin = User::query()->where('email', $email)->first();

        if ($admin === null) {
            User::query()->create([
                'name' => 'Admin',
                'email' => $email,
                'password' => (string) env('ADMIN_PASSWORD', 'HrXsoft-local-1'),
                'role' => UserRole::Admin,
                'department_id' => $department->id,
            ]);

            return;
        }

        if ($admin->department_id === null) {
            $admin->update(['department_id' => $department->id]);
        }
    }

    private function seedLeaveTypes(): void
    {
        $types = [
            'Yıllık izin' => ['day', true],
            'Mazeret izni' => ['both', false],
            'Ücretsiz izin' => ['day', false],
            'Hastalık izni (rapor)' => ['day', false],
            'Evlilik izni' => ['day', false],
            'Ölüm izni' => ['day', false],
            'Doğum izni' => ['day', false],
            'Babalık izni' => ['day', false],
            'Süt izni' => ['hour', false],
        ];

        foreach ($types as $name => [$unit, $balance]) {
            $type = Lookup::query()->firstOrCreate(
                ['kind' => 'leave_type', 'name' => $name],
                ['meta' => ['unit' => $unit, 'balance' => $balance]],
            );

            if (! array_key_exists('balance', $type->meta ?? [])) {
                $type->update(['meta' => ($type->meta ?? []) + ['balance' => $balance]]);
            }
        }
    }
}
