<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\Genre;
use App\Models\Kritik;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\KritikSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FilmCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_link_and_cannot_submit_a_comment(): void
    {
        $film = $this->createFilm();

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Login untuk berkomentar')
            ->assertDontSee(route('movies.comments.store', $film));

        $this->post(route('movies.comments.store', $film), [
            'comment' => 'Komentar dari pengunjung.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('kritiks', 0);
    }

    public function test_authenticated_user_comment_uses_session_user_and_redirects_to_film(): void
    {
        $film = $this->createFilm();
        $user = $this->createUser('user@example.com', 'Penonton');
        $otherUser = $this->createUser('other@example.com', 'Pengguna Lain');

        $response = $this->actingAs($user)->post(route('movies.comments.store', $film), [
            'comment' => 'Film ini sangat menarik.',
            'user_id' => $otherUser->id,
            'film_id' => 999,
        ]);

        $response->assertRedirect(route('movies.show', $film));
        $response->assertSessionHas('status', 'Komentar berhasil dikirim.');
        $this->assertDatabaseHas('kritiks', [
            'comment' => 'Film ini sangat menarik.',
            'user_id' => $user->id,
            'film_id' => $film->id,
        ]);
        $this->assertDatabaseMissing('kritiks', [
            'comment' => 'Film ini sangat menarik.',
            'user_id' => $otherUser->id,
        ]);

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Penonton')
            ->assertSee('Film ini sangat menarik.');
    }

    public function test_comment_must_be_present_and_no_longer_than_500_characters(): void
    {
        $film = $this->createFilm();
        $user = $this->createUser('user@example.com');

        $this->actingAs($user)
            ->from(route('movies.show', $film))
            ->post(route('movies.comments.store', $film), ['comment' => ''])
            ->assertSessionHasErrors('comment');

        $this->actingAs($user)
            ->from(route('movies.show', $film))
            ->post(route('movies.comments.store', $film), ['comment' => str_repeat('a', 501)])
            ->assertSessionHasErrors('comment');

        $this->assertDatabaseCount('kritiks', 0);
    }

    public function test_login_after_choosing_to_comment_returns_to_the_same_film(): void
    {
        $film = $this->createFilm();
        $user = $this->createUser('user@example.com');

        $this->get(route('login', ['film_id' => $film->id]))
            ->assertRedirect(route('home'))
            ->assertSessionHas('open_login', true);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('movies.show', $film));

        $this->assertAuthenticatedAs($user);
    }

    public function test_movie_comments_show_user_name_and_relative_time_with_eager_loading(): void
    {
        $film = $this->createFilm();
        $user = $this->createUser('commenter@example.com', 'Commenter Name');
        $comment = Kritik::create([
            'comment' => 'Komentar dengan nama.',
            'rating' => 0,
            'film_id' => $film->id,
            'user_id' => $user->id,
        ]);
        $comment->created_at = now()->subHours(2);
        $comment->save();

        $userQueries = 0;
        DB::listen(function (QueryExecuted $query) use (&$userQueries): void {
            if (str_contains(strtolower($query->sql), 'from "users"')) {
                $userQueries++;
            }
        });

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Commenter Name')
            ->assertSee($comment->fresh()->created_at->diffForHumans());

        $this->assertSame(1, $userQueries);
    }

    public function test_movie_comment_with_missing_user_shows_fallback_name(): void
    {
        $film = $this->createFilm();

        DB::statement('PRAGMA defer_foreign_keys = ON');
        DB::table('kritiks')->insert([
            'comment' => 'Komentar tanpa user.',
            'rating' => 0,
            'film_id' => $film->id,
            'user_id' => 99999,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Pengguna tidak dikenal')
            ->assertSee('Komentar tanpa user.');
    }

    public function test_comment_seeder_uses_existing_user_and_film_records(): void
    {
        $this->createFilm();
        $this->createUser('commenter@example.com');

        $this->seed(KritikSeeder::class);

        $this->assertDatabaseCount('kritiks', 1000);
        $this->assertSame(
            0,
            DB::table('kritiks')
                ->leftJoin('users', 'users.id', '=', 'kritiks.user_id')
                ->leftJoin('films', 'films.id', '=', 'kritiks.film_id')
                ->whereNull('users.id')
                ->orWhereNull('films.id')
                ->count(),
        );
    }

    private function createFilm(): Film
    {
        $genre = Genre::create(['name' => 'Drama']);

        return Film::create([
            'title' => 'Film Test',
            'sinopsis' => 'Sinopsis film untuk pengujian.',
            'year' => 2025,
            'poster' => 'storage/images/test.jpg',
            'genre_id' => $genre->id,
        ]);
    }

    private function createUser(string $email, string $name = 'Test User'): User
    {
        $role = Role::firstOrCreate(['name' => 'user']);
        $profile = Profile::create([
            'umur' => 20,
            'bio' => 'Test account',
            'alamat' => 'Test address',
        ]);

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'profile_id' => $profile->id,
            'role_id' => $role->id,
            'role' => 'user',
        ]);
    }
}
