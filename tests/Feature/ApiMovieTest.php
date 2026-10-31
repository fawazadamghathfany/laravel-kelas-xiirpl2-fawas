<?php

namespace Tests\Feature;

use App\Models\Cast;
use App\Models\Film;
use App\Models\Genre;
use App\Models\Kritik;
use App\Models\Peran;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiMovieTest extends TestCase
{
    use RefreshDatabase;

    public function test_movie_api_lists_and_shows_movies_for_guests(): void
    {
        $movie = $this->createMovie('API Movie');

        $this->getJson('/api/movie')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'API Movie');

        $this->getJson("/api/movie/{$movie->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'API Movie');
    }

    public function test_movie_api_requires_authentication_for_writes(): void
    {
        $movie = $this->createMovie('Original Movie');
        $payload = $this->moviePayload('Created Movie');

        $this->postJson('/api/movie', $payload)->assertUnauthorized();
        $this->putJson("/api/movie/{$movie->id}", $this->moviePayload('Updated Movie'))->assertUnauthorized();
        $this->deleteJson("/api/movie/{$movie->id}")->assertUnauthorized();

        $this->assertDatabaseHas('films', ['id' => $movie->id, 'title' => 'Original Movie']);
    }

    public function test_sanctum_tokens_authenticate_api_requests(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('api-test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', 'api@example.com');
    }

    public function test_authenticated_user_can_create_update_and_delete_movie_via_api(): void
    {
        $this->actingAs($this->createUser(), 'sanctum');

        $genre = Genre::firstOrCreate(['name' => 'Drama']);
        $payload = $this->moviePayload('Created Movie', $genre->id);

        $this->postJson('/api/movie', $payload)
            ->assertCreated()
            ->assertJsonPath('data.title', 'Created Movie');

        $movie = Film::where('title', 'Created Movie')->firstOrFail();
        $cast = Cast::create(['name' => 'API Cast', 'age' => 30, 'bio' => 'Actor']);
        Peran::create(['film_id' => $movie->id, 'cast_id' => $cast->id, 'actor' => 'Lead']);
        Kritik::create([
            'comment' => 'Good movie.',
            'rating' => 5,
            'film_id' => $movie->id,
            'user_id' => auth()->id(),
        ]);

        $this->putJson("/api/movie/{$movie->id}", $this->moviePayload('Updated Movie', $genre->id))
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Movie');

        $this->deleteJson("/api/movie/{$movie->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('films', ['id' => $movie->id]);
        $this->assertDatabaseMissing('perans', ['film_id' => $movie->id]);
        $this->assertDatabaseMissing('kritiks', ['film_id' => $movie->id]);
    }

    public function test_movie_api_validates_movie_fields(): void
    {
        $this->actingAs($this->createUser(), 'sanctum');

        $this->postJson('/api/movie', [
            'title' => '',
            'sinopsis' => '',
            'year' => 1700,
            'poster' => '',
            'genre_id' => 999,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'sinopsis', 'year', 'poster', 'genre_id']);
    }

    private function createMovie(string $title): Film
    {
        return Film::create($this->moviePayload($title));
    }

    private function moviePayload(string $title, ?int $genreId = null): array
    {
        $genreId ??= Genre::firstOrCreate(['name' => 'Drama'])->id;

        return [
            'title' => $title,
            'sinopsis' => 'A film synopsis.',
            'year' => 2024,
            'poster' => 'storage/images/movie.jpg',
            'genre_id' => $genreId,
        ];
    }

    private function createUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'user']);
        $profile = Profile::create([
            'umur' => 20,
            'bio' => 'API test account',
            'alamat' => 'Test address',
        ]);

        return User::create([
            'name' => 'API User',
            'email' => 'api@example.com',
            'password' => 'password123',
            'profile_id' => $profile->id,
            'role_id' => $role->id,
            'role' => 'user',
        ]);
    }
}
