<?php

namespace Tests\Feature;

use App\Models\Cast;
use App\Models\Film;
use App\Models\Genre;
use App\Models\Peran;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FilmCastTest extends TestCase
{
    use RefreshDatabase;

    public function test_movie_detail_displays_cast_names_and_roles_without_n_plus_one_queries(): void
    {
        $film = $this->createFilm();
        $firstCast = $this->createCast('Aktor Pertama');
        $secondCast = $this->createCast('Aktris Kedua');

        Peran::create([
            'film_id' => $film->id,
            'cast_id' => $firstCast->id,
            'actor' => 'Pemeran utama',
        ]);
        Peran::create([
            'film_id' => $film->id,
            'cast_id' => $secondCast->id,
            'actor' => 'Pemeran pendukung',
        ]);

        $castQueries = 0;
        $peranQueries = 0;
        DB::listen(function (QueryExecuted $query) use (&$castQueries, &$peranQueries): void {
            $sql = strtolower($query->sql);

            if (str_contains($sql, 'from "casts"')) {
                $castQueries++;
            }

            if (str_contains($sql, 'from "perans"')) {
                $peranQueries++;
            }
        });

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Aktor Pertama')
            ->assertSee('Pemeran utama')
            ->assertSee('Aktris Kedua')
            ->assertSee('Pemeran pendukung');

        $this->assertSame(1, $castQueries);
        $this->assertSame(1, $peranQueries);
    }

    public function test_movie_detail_shows_message_when_cast_is_not_available(): void
    {
        $film = $this->createFilm();

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Data cast belum tersedia');
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

    private function createCast(string $name): Cast
    {
        return Cast::create([
            'name' => $name,
            'age' => 30,
            'bio' => 'Test cast bio.',
        ]);
    }
}
