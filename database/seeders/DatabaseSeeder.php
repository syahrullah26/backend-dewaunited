<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (DB::table('provinces')->count() === 0) {
            $this->call([
                ProvinceSeeder::class,
                RegencySeeder::class,
                DistrictSeeder::class,
                VillageSeeder::class,
                UserSeeder::class,
                ProductSeeder::class,
                AdminSeeder::class,
            ]);
        }
    }
}
