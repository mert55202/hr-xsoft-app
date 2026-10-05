<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AppModule;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Department;
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
}
