<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppModule;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFourTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_person_opens_their_own_card_and_the_cards_below_them(): void
    {
        [$boss, $staff, $chief, $peer] = $this->tree();

        $this->actingAs($boss->user)
            ->get('/personel/'.$staff->id)
            ->assertOk()
            ->assertSee('Ece Uzman')
            ->assertSee('Ali Müdür');

        $this->actingAs($boss->user)
            ->get('/personel/'.$boss->id)
            ->assertOk();

        $this->actingAs($boss->user)
            ->get('/personel/'.$chief->id)
            ->assertForbidden();

        $this->actingAs($boss->user)
            ->get('/personel/'.$peer->id)
            ->assertForbidden();
    }

    public function test_the_list_hides_superiors_and_peers(): void
    {
        [$boss, $staff, $chief, $peer] = $this->tree();

        $this->actingAs($boss->user)
            ->get('/personel')
            ->assertOk()
            ->assertSee('Ali Müdür')
            ->assertSee('Ece Uzman')
            ->assertDontSee('Deniz Genel')
            ->assertDontSee('Can Satış');

        $this->actingAs($staff->user)
            ->get('/personel')
            ->assertOk()
            ->assertSee('Ece Uzman')
            ->assertDontSee('Ali Müdür');
    }

    public function test_a_manager_edits_a_subordinate_and_not_themselves(): void
    {
        [$boss, $staff] = $this->tree();

        $this->actingAs($boss->user)->put('/personel/'.$staff->id, [
            'first_name' => 'Ece',
            'last_name' => 'Kaya',
            'position_id' => $staff->position_id,
        ])->assertRedirect('/personel/'.$staff->id);

        $this->assertSame('Kaya', $staff->fresh()->last_name);

        $this->actingAs($staff->user)->put('/personel/'.$staff->id, [
            'first_name' => 'Ece',
            'last_name' => 'Yeni',
            'position_id' => $staff->position_id,
        ])->assertForbidden();
    }

    public function test_a_manager_cannot_move_someone_above_themselves(): void
    {
        [$boss, $staff, $chief] = $this->tree();

        $this->actingAs($boss->user)->put('/personel/'.$staff->id, [
            'first_name' => 'Ece',
            'last_name' => 'Uzman',
            'position_id' => $chief->position_id,
        ])->assertSessionHasErrors('position_id');

        $this->assertSame($staff->position_id, $staff->fresh()->position_id);
    }

    public function test_admin_opens_every_card_and_adds_a_person(): void
    {
        [, , $chief] = $this->tree();
        $admin = User::factory()->admin()->create();
        $open = Position::factory()->create(['name' => 'Açık Kadro', 'headcount' => 2]);

        $this->actingAs($admin)->get('/personel/'.$chief->id)->assertOk()->assertSee('Deniz Genel');

        $this->actingAs($admin)->post('/personel', [
            'first_name' => 'Selin',
            'last_name' => 'Ak',
            'position_id' => $open->id,
            'email' => 'selin@firma.test',
        ])->assertRedirect();

        $this->assertDatabaseHas('employees', ['first_name' => 'Selin', 'email' => 'selin@firma.test']);
    }

    public function test_a_full_position_and_a_full_department_reject_another_person(): void
    {
        $admin = User::factory()->admin()->create();
        $seat = Position::factory()->create(['name' => 'Tek Koltuk', 'headcount' => 1]);
        Employee::factory()->create(['position_id' => $seat->id]);

        $this->actingAs($admin)->post('/personel', [
            'first_name' => 'Fazla',
            'last_name' => 'Koltuk',
            'position_id' => $seat->id,
        ])->assertSessionHasErrors('position_id');

        $department = Department::factory()->create(['name' => 'İK', 'headcount' => 1]);
        $room = Position::factory()->create([
            'name' => 'İK Uzmanı',
            'department_id' => $department->id,
            'headcount' => 5,
        ]);
        Employee::factory()->create(['position_id' => $room->id]);

        $this->actingAs($admin)->post('/personel', [
            'first_name' => 'Fazla',
            'last_name' => 'Departman',
            'position_id' => $room->id,
        ])->assertSessionHasErrors('position_id');
    }

    public function test_the_chart_links_only_the_cards_the_viewer_can_open(): void
    {
        $department = Department::factory()->create(['modules' => [AppModule::OrgChart->value]]);
        [$boss, $staff, $chief] = $this->tree();
        $boss->user->update(['department_id' => $department->id]);

        $response = $this->actingAs($boss->user)->get('/sema');

        $response->assertOk()
            ->assertSee('Deniz Genel')
            ->assertSee('Ece Uzman')
            ->assertSee('/personel/'.$staff->id, false)
            ->assertSee('/personel/'.$boss->id, false)
            ->assertDontSee('/personel/'.$chief->id, false);
    }

    public function test_learning_and_experience_belong_to_the_card(): void
    {
        [, $staff] = $this->tree();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/personel/'.$staff->id.'/ogrenim', [
            'school' => 'ODTÜ',
            'field' => 'Endüstri',
        ])->assertRedirect('/personel/'.$staff->id);

        $this->actingAs($admin)->post('/personel/'.$staff->id.'/deneyim', [
            'company' => 'Eski Firma',
            'title' => 'Uzman',
        ])->assertRedirect('/personel/'.$staff->id);

        $this->actingAs($admin)
            ->get('/personel/'.$staff->id)
            ->assertOk()
            ->assertSee('ODTÜ')
            ->assertSee('Eski Firma');

        $this->actingAs($staff->user)->post('/personel/'.$staff->id.'/ogrenim', [
            'school' => 'Gizli',
        ])->assertForbidden();
    }

    public function test_someone_without_a_card_sees_an_empty_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/personel')->assertOk()->assertSee('Kayıt yok');
        $this->actingAs($user)->post('/personel', [
            'first_name' => 'Yok',
            'last_name' => 'Yetki',
            'position_id' => Position::factory()->create()->id,
        ])->assertForbidden();
    }

    /**
     * @return array{0: Employee, 1: Employee, 2: Employee, 3: Employee}
     */
    private function tree(): array
    {
        $root = Position::factory()->create(['name' => 'Genel Müdür', 'headcount' => 1]);
        $child = Position::factory()->create(['name' => 'İK Müdürü', 'parent_id' => $root->id, 'headcount' => 1]);
        $peer = Position::factory()->create(['name' => 'Satış Müdürü', 'parent_id' => $root->id, 'headcount' => 1]);
        $leaf = Position::factory()->create(['name' => 'İK Uzmanı', 'parent_id' => $child->id, 'headcount' => 2]);

        $boss = Employee::factory()->create([
            'first_name' => 'Ali',
            'last_name' => 'Müdür',
            'position_id' => $child->id,
            'user_id' => User::factory()->create()->id,
        ]);
        $staff = Employee::factory()->create([
            'first_name' => 'Ece',
            'last_name' => 'Uzman',
            'position_id' => $leaf->id,
            'user_id' => User::factory()->create()->id,
        ]);
        $chief = Employee::factory()->create([
            'first_name' => 'Deniz',
            'last_name' => 'Genel',
            'position_id' => $root->id,
        ]);
        $sales = Employee::factory()->create([
            'first_name' => 'Can',
            'last_name' => 'Satış',
            'position_id' => $peer->id,
            'user_id' => User::factory()->create()->id,
        ]);

        return [$boss, $staff, $chief, $sales];
    }
}
