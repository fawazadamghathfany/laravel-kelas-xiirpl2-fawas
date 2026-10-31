<?php

namespace App\Interfaces;

use App\Models\Film;
use Illuminate\Database\Eloquent\Collection;

interface MovieRepositoryInterface
{
    public function index(): Collection;

    public function create(array $attributes): Film;

    public function update(Film $film, array $attributes): Film;

    public function delete(Film $film): void;
}
