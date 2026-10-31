<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_hashed_user_and_logs_them_in(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Movie Fan',
            'email' => '  FAN@example.com ',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'fan@example.com')->firstOrFail();

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status');
        $this->assertAuthenticatedAs($user);
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('profiles', ['id' => $user->profile_id]);
    }

    public function test_registration_validates_name_email_password_and_confirmation(): void
    {
        $response = $this->from(route('home'))->post(route('register.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrorsIn('register', ['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_duplicate_email_is_rejected_without_regard_to_case(): void
    {
        $this->createUser('fan@example.com');

        $response = $this->from(route('home'))->post(route('register.store'), [
            'name' => 'Another Fan',
            'email' => ' FAN@EXAMPLE.COM ',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrorsIn('register', ['email']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_invalid_login_uses_a_generic_error_message(): void
    {
        $response = $this->from(route('home'))->post(route('login.store'), [
            'email' => 'unknown@example.com',
            'password' => 'incorrect-password',
        ]);

        $response->assertSessionHasErrorsIn('login', ['email']);
        $this->assertSame(
            'Email atau password yang dimasukkan salah.',
            session('errors')->getBag('login')->first('email'),
        );
        $this->assertGuest();
    }

    public function test_users_and_admins_are_redirected_to_their_respective_pages(): void
    {
        $user = $this->createUser('user@example.com');
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('admin'))->assertForbidden();

        $this->post(route('logout'));
        $admin = $this->createUser('admin@example.com', 'admin');
        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password123',
        ])->assertRedirect(route('admin'));
        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin'))->assertOk();
    }

    public function test_logout_clears_the_authenticated_session(): void
    {
        $this->actingAs($this->createUser('user@example.com'));

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status');
        $this->assertGuest();
    }

    private function createUser(string $email, string $roleName = 'user'): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        $profile = Profile::create([
            'umur' => 20,
            'bio' => 'Test account',
            'alamat' => 'Test address',
        ]);

        return User::create([
            'name' => $roleName,
            'email' => $email,
            'password' => 'password123',
            'role' => $roleName,
            'role_id' => $role->id,
            'profile_id' => $profile->id,
        ]);
    }
}
