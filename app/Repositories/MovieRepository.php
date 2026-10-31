<?php

namespace App\Repositories;

use App\Interfaces\MovieRepositoryInterface;
use App\Models\Film;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MovieRepository implements MovieRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function index(): Collection
    {
        return Film::all();
    }

    public function create(array $attributes): Film
    {
        return Film::create($attributes);
    }

    public function update(Film $film, array $attributes): Film
    {
        $film->update($attributes);

        return $film->refresh();
    }

    public function delete(Film $film): void
    {
        DB::transaction(function () use ($film): void {
            $film->perans()->delete();
            $film->kritiks()->delete();
            $film->delete();
        });
    }
}
