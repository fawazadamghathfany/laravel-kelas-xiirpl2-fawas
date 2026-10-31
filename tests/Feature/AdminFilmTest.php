<?php

namespace Tests\Feature;

use App\Models\Cast;
use App\Models\Film;
use App\Models\Genre;
use App\Models\Peran;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFilmTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_reject_guests_and_non_admin_users(): void
    {
        $this->get(route('admin'))->assertRedirect(route('login'));
        $this->get(route('admin.films.create'))->assertRedirect(route('login'));
        $this->post(route('admin.films.store'))->assertRedirect(route('login'));

        $this->actingAs($this->createUser('user@example.com'))
            ->get(route('admin'))
            ->assertForbidden();

        $this->actingAs(User::where('email', 'user@example.com')->firstOrFail())
            ->get(route('admin.films.create'))
            ->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_a_film(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $genre = Genre::create(['name' => 'Drama']);

        $this->actingAs($admin)
            ->get(route('admin'))
            ->assertOk()
            ->assertSee('Kelola Film');

        $this->post(route('admin.films.store'), [
            'title' => 'Film Baru',
            'sinopsis' => 'Sinopsis baru.',
            'year' => 2025,
            'poster' => 'storage/images/new-film.jpg',
            'genre_id' => $genre->id,
        ])->assertRedirect(route('admin'));

        $film = Film::where('title', 'Film Baru')->firstOrFail();
        $cast = Cast::create(['name' => 'Test Cast', 'age' => 30, 'bio' => 'Test']);
        Peran::create(['film_id' => $film->id, 'cast_id' => $cast->id, 'actor' => 'Pemeran']);

        $this->put(route('admin.films.update', $film), [
            'title' => 'Film Diperbarui',
            'sinopsis' => 'Sinopsis diperbarui.',
            'year' => 2026,
            'poster' => 'storage/images/updated-film.jpg',
            'genre_id' => $genre->id,
        ])->assertRedirect(route('admin'));

        $this->assertDatabaseHas('films', [
            'id' => $film->id,
            'title' => 'Film Diperbarui',
            'year' => 2026,
        ]);

        $this->delete(route('admin.films.destroy', $film))
            ->assertRedirect(route('admin'));

        $this->assertDatabaseMissing('films', ['id' => $film->id]);
        $this->assertDatabaseMissing('perans', ['film_id' => $film->id]);
    }

    public function test_admin_film_form_validates_fields_and_list_searches(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $genre = Genre::create(['name' => 'Drama']);
        $this->actingAs($admin);

        $this->from(route('admin.films.create'))
            ->post(route('admin.films.store'), [
                'title' => '',
                'sinopsis' => '',
                'year' => 1700,
                'poster' => '',
                'genre_id' => 999,
            ])
            ->assertSessionHasErrors(['title', 'sinopsis', 'year', 'poster', 'genre_id']);

        $film = Film::create([
            'title' => 'Film yang Dicari',
            'sinopsis' => 'Sinopsis.',
            'year' => 2024,
            'poster' => 'storage/images/test.jpg',
            'genre_id' => $genre->id,
        ]);
        Film::create([
            'title' => 'Film Lain',
            'sinopsis' => 'Sinopsis lain.',
            'year' => 2023,
            'poster' => 'storage/images/other.jpg',
            'genre_id' => $genre->id,
        ]);

        $this->get(route('admin', ['search' => 'Dicari']))
            ->assertOk()
            ->assertSee($film->title)
            ->assertDontSee('Film Lain');
    }

    public function test_admin_film_list_is_paginated(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $genre = Genre::create(['name' => 'Drama']);

        for ($number = 1; $number <= 16; $number++) {
            Film::create([
                'title' => "Film {$number}",
                'sinopsis' => 'Sinopsis.',
                'year' => 2020,
                'poster' => 'storage/images/test.jpg',
                'genre_id' => $genre->id,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin'))
            ->assertOk()
            ->assertSee('Film 16')
            ->assertDontSee('>Film 1<')
            ->assertSee('Next');
    }

    public function test_admin_seeder_creates_the_documented_admin_account_idempotently(): void
    {
        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => '12345678',
        ])->assertRedirect(route('admin'));

        $this->assertAuthenticatedAs($admin);
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
            'profile_id' => $profile->id,
            'role_id' => $role->id,
            'role' => $roleName,
        ]);
    }
}
