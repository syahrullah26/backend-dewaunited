<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Village;
use Illuminate\Support\Facades\DB;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('villages')->truncate();

        $file = storage_path('app/regions/villages.csv');
        $rows = array_map('str_getcsv', file($file));

        unset($rows[0]); // skip header

        foreach ($rows as $row) {
            Village::create([
                'id' => $row[0],
                'district_id' => $row[1],
                'name' => $row[2],
            ]);
        }
    }
}
