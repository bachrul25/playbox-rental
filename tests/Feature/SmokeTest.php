<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    protected $seeder = DatabaseSeeder::class;

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk()->assertSee('PlayBox Rental');
    }

    public function test_redirect_dashboard_to_login_for_guest(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_dashboard_loads(): void
    {
        $admin = User::where('email', 'admin@playbox.com')->firstOrFail();
        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Pendapatan');
    }

    public function test_admin_can_access_rentals_page(): void
    {
        $admin = User::where('email', 'admin@playbox.com')->firstOrFail();
        $this->actingAs($admin)->get('/rentals')->assertOk()->assertSee('Transaksi Rental');
    }

    public function test_owner_cannot_access_rentals(): void
    {
        $owner = User::where('email', 'owner@playbox.com')->firstOrFail();
        $this->actingAs($owner)->get('/rentals')->assertForbidden();
    }

    public function test_mitra_role_restrictions(): void
    {
        $mitra = User::where('email', 'mitra@playbox.com')->firstOrFail();
        $this->actingAs($mitra)->get('/reports/partnership')->assertOk();
        $this->actingAs($mitra)->get('/reports/private')->assertForbidden();
        $this->actingAs($mitra)->get('/users')->assertForbidden();
    }
}
