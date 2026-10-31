<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Cast;
use Faker\Factory as Faker;

class CastSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Cast::query()->exists()) {
            return;
        }

        $faker = Faker::create('id_ID');
        for ($i = 1; $i <= 50; $i++) {
            Cast::create([
                'name' => $faker->unique()->name,
                'age' => $faker->numberBetween(18, 80),
                'bio' => $faker->company,
            ]);
        }
    }
}
