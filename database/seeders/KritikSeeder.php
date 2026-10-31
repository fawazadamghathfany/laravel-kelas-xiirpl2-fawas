<?php

namespace Database\Seeders;

use App\Models\Film;
use App\Models\Kritik;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class KritikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Kritik::query()->exists()) {
            return;
        }

        $filmIds = Film::query()->pluck('id')->all();
        $userIds = User::query()->pluck('id')->all();

        if ($filmIds === [] || $userIds === []) {
            throw new \RuntimeException('Seed users and films before creating comments.');
        }

        $faker = Faker::create('id_ID');
        for ($loop = 1; $loop <= 1000; $loop++) {
            Kritik::create([
                'comment' => $faker->text(maxNbChars: 200),
                'rating' => $faker->numberBetween(1, 5),
                'film_id' => $faker->randomElement($filmIds),
                'user_id' => $faker->randomElement($userIds),
            ]);
        }
    }
}
