<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/giris')->assertOk()->assertSee('E-posta');
    }

    public function test_user_can_log_in_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'personel@hr-xsoft.test',
            'password' => 'sifre1234',
        ]);

        $this->post('/giris', [
            'email' => $user->email,
            'password' => 'sifre1234',
        ])->assertRedirect(route('today'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_stays_on_login(): void
    {
        User::factory()->create([
            'email' => 'personel@hr-xsoft.test',
            'password' => 'sifre1234',
        ]);

        $this->post('/giris', [
            'email' => 'personel@hr-xsoft.test',
            'password' => 'yanlissifre',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_employee_cannot_change_the_company(): void
    {
        Company::factory()->create(['name' => 'Eski ad']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/firma', ['name' => 'Yeni ad', 'primary_color' => '#112233'])
            ->assertForbidden();

        $this->assertSame('Eski ad', Company::query()->first()->name);
    }

    public function test_admin_updates_the_single_company(): void
    {
        Company::factory()->create(['name' => 'Eski ad']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put('/firma', [
                'name' => 'Yeni ad',
                'primary_color' => '#112233',
                'email' => 'ik@firma.test',
            ])
            ->assertRedirect(route('company.edit'));

        $this->assertSame(1, Company::query()->count());
        $this->assertSame('Yeni ad', Company::query()->first()->name);
    }

    public function test_shell_links_resolve(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertOk()->assertSee('Bugün');
        $this->actingAs($user)->get('/personel')->assertOk();
        $this->actingAs($user)->get('/ik')->assertOk();
        $this->actingAs($user)->get('/firma')->assertOk();
        $this->actingAs($user)->get('/hesabim')->assertOk()->assertSee('Çıkış');
    }
}
