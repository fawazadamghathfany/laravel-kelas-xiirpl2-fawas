<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $user = User::firstOrNew(['email' => 'admin@example.com']);

        if (! $user->profile_id) {
            $profile = Profile::create([
                'alamat' => $faker->address,
                'umur' => $faker->numberBetween(18, 50),
                'bio' => 'administrator',
            ]);
            $user->profile_id = $profile->id;
        }

        $user->name = 'admin';
        $user->email_verified_at = now();
        $user->password = Hash::make('12345678');
        $user->remember_token = Str::random(10);
        $user->role_id = Role::firstOrCreate(['name' => 'admin'])->id;
        $user->role = 'admin';
        $user->save();
    }
}
