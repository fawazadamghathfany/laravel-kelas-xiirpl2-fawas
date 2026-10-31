<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Cast;
use App\Models\Film;
use App\Models\Peran;
use Faker\Factory as Faker;

class PeranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Peran::query()->exists()) {
            return;
        }

        $filmIds = Film::query()->pluck('id');
        if ($filmIds->isEmpty()) {
            throw new \RuntimeException('Seed films before creating cast roles.');
        }

        if (! Cast::query()->exists()) {
            $this->call(CastSeeder::class);
        }

        $castIds = Cast::query()->pluck('id');
        if ($castIds->isEmpty()) {
            throw new \RuntimeException('No cast records are available to assign to films.');
        }

        $faker = Faker::create('id_ID');
        foreach ($filmIds as $filmId) {
            foreach ($castIds->shuffle()->take(2) as $castId) {
                Peran::create([
                    'film_id' => $filmId,
                    'cast_id' => $castId,
                    'actor' => $faker->randomElement([
                        'Pemeran utama',
                        'Pemeran pendukung',
                        'Protagonis',
                        'Antagonis',
                    ]),
                ]);
            }
        }
    }
}
