<?php

namespace Tests\Feature\Auth;

use App\Models\Portefeuille;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function donnees(array $surcharge = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'telephone' => '0123456789',
            'email' => 'test@example.com',
            'role' => 'promoteur',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $surcharge);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_as_promoteur(): void
    {
        $response = $this->post('/register', $this->donnees());

        $this->assertAuthenticated();
        $response->assertRedirect('/promoteur');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'role' => 'promoteur']);
    }

    public function test_new_users_can_register_as_marchand(): void
    {
        $response = $this->post('/register', $this->donnees(['role' => 'marchand']));

        $this->assertAuthenticated();
        $response->assertRedirect('/marchand');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'role' => 'marchand']);
    }

    public function test_registration_creates_a_portefeuille(): void
    {
        $this->post('/register', $this->donnees());

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertNotNull($user->portefeuille);
        $this->assertSame(0.0, (float) $user->portefeuille->solde_disponible);
        $this->assertSame(0.0, (float) $user->portefeuille->solde_en_attente);
        $this->assertSame(1, Portefeuille::count());
    }

    public function test_admin_role_cannot_be_chosen_at_registration(): void
    {
        $response = $this->post('/register', $this->donnees(['role' => 'admin']));

        $this->assertGuest();
        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_telephone_must_be_unique(): void
    {
        User::factory()->create(['telephone' => '0123456789']);

        $response = $this->post('/register', $this->donnees());

        $this->assertGuest();
        $response->assertSessionHasErrors('telephone');
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/register', $this->donnees());

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
