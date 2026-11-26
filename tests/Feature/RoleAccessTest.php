<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/marchand')->assertRedirect('/login');
        $this->get('/promoteur')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_each_role_reaches_its_space(): void
    {
        $marchand = User::factory()->create(['role' => 'marchand']);
        $this->actingAs($marchand)->get('/marchand')->assertOk();

        $promoteur = User::factory()->create(['role' => 'promoteur']);
        $this->actingAs($promoteur)->get('/promoteur')->assertOk();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_promoteur_cannot_access_marchand_space(): void
    {
        $promoteur = User::factory()->create(['role' => 'promoteur']);

        $this->actingAs($promoteur)->get('/marchand')->assertForbidden();
        $this->actingAs($promoteur)->get('/admin')->assertForbidden();
    }

    public function test_marchand_cannot_access_promoteur_space(): void
    {
        $marchand = User::factory()->create(['role' => 'marchand']);

        $this->actingAs($marchand)->get('/promoteur')->assertForbidden();
        $this->actingAs($marchand)->get('/admin')->assertForbidden();
    }

    public function test_admin_cannot_access_role_spaces(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/marchand')->assertForbidden();
        $this->actingAs($admin)->get('/promoteur')->assertForbidden();
    }

    public function test_sub_pages_are_protected_by_role(): void
    {
        $promoteur = User::factory()->create(['role' => 'promoteur']);
        $marchand = User::factory()->create(['role' => 'marchand']);

        $this->actingAs($promoteur)->get('/promoteur/liens')->assertOk();
        $this->actingAs($promoteur)->get('/promoteur/portefeuille')->assertOk();
        $this->actingAs($promoteur)->get('/marchand/commandes')->assertForbidden();

        $this->actingAs($marchand)->get('/marchand/articles')->assertOk();
        $this->actingAs($marchand)->get('/marchand/commandes')->assertOk();
        $this->actingAs($marchand)->get('/promoteur/liens')->assertForbidden();

        $this->actingAs($promoteur)->get('/admin/retraits')->assertForbidden();
    }
}
