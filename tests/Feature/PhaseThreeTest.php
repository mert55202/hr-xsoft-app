<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppModule;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseThreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_chart_shows_the_whole_tree_when_the_module_is_open(): void
    {
        $home = Department::factory()->create([
            'name' => 'İK',
            'modules' => [AppModule::OrgChart->value],
        ]);
        $other = Department::factory()->create(['name' => 'Satış']);
        $user = User::factory()->create(['department_id' => $home->id]);
        Position::factory()->create(['name' => 'Genel Müdür']);
        Position::factory()->create(['name' => 'Satış Uzmanı', 'department_id' => $other->id]);

        $this->actingAs($user)
            ->get('/sema')
            ->assertOk()
            ->assertSee('Genel Müdür')
            ->assertSee('Satış Uzmanı');
    }

    public function test_chart_stays_closed_without_the_module(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/sema')->assertForbidden();
    }

    public function test_more_than_one_root_warns(): void
    {
        $admin = User::factory()->admin()->create();
        Position::factory()->create(['name' => 'Kök A']);
        Position::factory()->create(['name' => 'Kök B']);

        $this->actingAs($admin)
            ->get('/pozisyonlar')
            ->assertOk()
            ->assertSee('Üstü boş pozisyonlar')
            ->assertSee('Kök A')
            ->assertSee('Kök B');
    }

    public function test_a_single_root_does_not_warn(): void
    {
        $admin = User::factory()->admin()->create();
        $root = Position::factory()->create(['name' => 'Genel Müdür']);
        Position::factory()->create(['name' => 'Uzman', 'parent_id' => $root->id]);

        $this->actingAs($admin)->get('/pozisyonlar')->assertOk()->assertDontSee('Üstü boş pozisyonlar');
        $this->actingAs($admin)->get('/sema')->assertOk()->assertSee('Uzman');
    }

    public function test_parent_cannot_be_a_descendant(): void
    {
        $admin = User::factory()->admin()->create();
        $root = Position::factory()->create(['name' => 'Kök']);
        $child = Position::factory()->create(['name' => 'Alt', 'parent_id' => $root->id]);

        $this->actingAs($admin)->put('/pozisyonlar/'.$root->id, [
            'name' => 'Kök',
            'collar' => 'white',
            'parent_id' => $child->id,
            'headcount' => 1,
        ])->assertSessionHasErrors('parent_id');

        $this->assertNull($root->fresh()->parent_id);
    }

    public function test_deleting_a_parent_empties_the_children(): void
    {
        $admin = User::factory()->admin()->create();
        $root = Position::factory()->create(['name' => 'Kök']);
        $child = Position::factory()->create(['name' => 'Alt', 'parent_id' => $root->id]);
        Position::factory()->create(['name' => 'Diğer', 'parent_id' => $root->id]);

        $this->actingAs($admin)->delete('/pozisyonlar/'.$root->id)->assertRedirect(route('positions.index'));

        $this->assertNull($child->fresh()->parent_id);
        $this->actingAs($admin)->get('/pozisyonlar')->assertSee('Üstü boş pozisyonlar');
    }

    public function test_second_manager_is_the_parent_of_the_parent(): void
    {
        $admin = User::factory()->admin()->create();
        $root = Position::factory()->create(['name' => 'Genel Müdür']);
        $mid = Position::factory()->create(['name' => 'Müdür', 'parent_id' => $root->id]);
        $leaf = Position::factory()->create(['name' => 'Uzman', 'parent_id' => $mid->id]);

        $this->actingAs($admin)
            ->get('/pozisyonlar/'.$leaf->id.'/duzenle')
            ->assertOk()
            ->assertSee('İkinci amir: Genel Müdür');
    }

    public function test_employee_cannot_create_a_position(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/pozisyonlar', [
            'name' => 'Yeni',
            'collar' => 'white',
            'headcount' => 1,
        ])->assertForbidden();

        $this->assertSame(0, Position::query()->count());
    }
}
