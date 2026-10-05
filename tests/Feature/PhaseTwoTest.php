<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppModule;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_closed_module_stays_out_of_the_menu(): void
    {
        $department = Department::factory()->create(['name' => 'İK', 'modules' => []]);
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->actingAs($user)->get('/')->assertOk()->assertDontSee('Anket');
        $this->actingAs($user)->get('/modul/survey')->assertForbidden();
        $this->actingAs($user)->get('/modul/leave')->assertOk()->assertSee('İzin');
    }

    public function test_admin_opens_a_module_for_a_department(): void
    {
        $department = Department::factory()->create(['modules' => []]);
        $user = User::factory()->create(['department_id' => $department->id]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put('/departmanlar/'.$department->id, [
                'modules' => [AppModule::Survey->value, AppModule::OrgChart->value],
            ])
            ->assertRedirect(route('departments.index'));

        $this->actingAs($user)->get('/')->assertOk()->assertSee('Anket')->assertSee('Şema');
        $this->actingAs($user)->get('/modul/survey')->assertOk();
    }

    public function test_employee_cannot_edit_department_modules(): void
    {
        $department = Department::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->get('/departmanlar')->assertForbidden();
        $this->actingAs($user)
            ->put('/departmanlar/'.$department->id, ['modules' => [AppModule::Survey->value]])
            ->assertForbidden();

        $this->assertSame([], $department->fresh()->modules);
    }

    public function test_department_headcount_caps_position_seats(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create(['name' => 'İK', 'headcount' => 4]);
        Position::factory()->create([
            'name' => 'İK Uzmanı',
            'department_id' => $department->id,
            'headcount' => 3,
        ]);

        $this->actingAs($admin)->post('/pozisyonlar', [
            'name' => 'İK Asistanı',
            'department_id' => $department->id,
            'collar' => 'white',
            'headcount' => 2,
        ])->assertSessionHasErrors('headcount');

        $this->assertSame(1, Position::query()->count());

        $this->actingAs($admin)->put('/departmanlar/'.$department->id, [
            'headcount' => 2,
            'modules' => [],
        ])->assertSessionHasErrors('headcount');

        $this->assertSame(4, $department->fresh()->headcount);
    }

    public function test_admin_can_set_a_department_headcount(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create(['name' => 'IT']);

        $this->actingAs($admin)->put('/departmanlar/'.$department->id, [
            'headcount' => 10,
            'modules' => [],
        ])->assertRedirect(route('departments.index'));

        $this->assertSame(10, $department->fresh()->headcount);
    }

    public function test_unknown_module_is_missing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/modul/yok')->assertNotFound();
    }
}
