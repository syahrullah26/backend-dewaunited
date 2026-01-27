<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // if (!Schema::hasTable('provinces')) {
        //     return;
        // }

        // if (DB::table('provinces')->exists()) {
        //     return;
        // }

        $this->call([
            ProvinceSeeder::class,
            RegencySeeder::class,
            DistrictSeeder::class,
            VillageSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
