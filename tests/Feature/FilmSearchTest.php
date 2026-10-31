<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilmSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_search_uses_get_and_shows_only_matching_films(): void
    {
        $genre = Genre::create(['name' => 'Drama']);
        Film::create([
            'title' => 'Searchable Film',
            'sinopsis' => 'A matching film.',
            'year' => 2024,
            'poster' => 'storage/images/searchable.jpg',
            'genre_id' => $genre->id,
        ]);
        Film::create([
            'title' => 'Unrelated Film',
            'sinopsis' => 'A different film.',
            'year' => 2023,
            'poster' => 'storage/images/unrelated.jpg',
            'genre_id' => $genre->id,
        ]);

        $this->get(route('home', ['search' => 'Searchable']))
            ->assertOk()
            ->assertSee('Searchable Film')
            ->assertDontSee('Unrelated Film')
            ->assertSee('name="search"', false)
            ->assertSee('value="Searchable"', false);
    }

    public function test_legacy_post_search_redirects_to_get_search_results(): void
    {
        $genre = Genre::create(['name' => 'Drama']);
        Film::create([
            'title' => 'Searchable Film',
            'sinopsis' => 'A matching film.',
            'year' => 2024,
            'poster' => 'storage/images/searchable.jpg',
            'genre_id' => $genre->id,
        ]);

        $this->post(route('home.search.legacy'), ['Search' => 'Searchable'])
            ->assertRedirect(route('home', ['search' => 'Searchable']));

        $this->get(route('home', ['search' => 'Searchable']))
            ->assertOk()
            ->assertSee('Searchable Film');
    }

    public function test_home_search_shows_empty_message_and_preserves_query_in_pagination(): void
    {
        $this->get(route('home', ['search' => 'No matching film']))
            ->assertOk()
            ->assertSee('Film tidak ditemukan.');

        $genre = Genre::create(['name' => 'Drama']);
        for ($number = 1; $number <= 19; $number++) {
            Film::create([
                'title' => "Searchable Film {$number}",
                'sinopsis' => 'A matching film.',
                'year' => 2024,
                'poster' => 'storage/images/searchable.jpg',
                'genre_id' => $genre->id,
                'created_at' => now()->addSeconds($number),
                'updated_at' => now()->addSeconds($number),
            ]);
        }

        $this->get(route('home', ['search' => 'Searchable']))
            ->assertOk()
            ->assertSee('Searchable Film 1')
            ->assertDontSee('>Searchable Film 19<', false)
            ->assertSee('search=Searchable', false);

        $this->get(route('home', ['search' => 'Searchable', 'page' => 2]))
            ->assertOk()
            ->assertSee('Searchable Film 19')
            ->assertDontSee('>Searchable Film 1<', false);
    }
}
