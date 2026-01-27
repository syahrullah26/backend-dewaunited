<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Regency;
use Illuminate\Support\Facades\DB;

class RegencySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('regencies')->truncate();

        $file = storage_path('app/regions/regencies.csv');
        $rows = array_map('str_getcsv', file($file));

        unset($rows[0]); // skip header

        foreach ($rows as $row) {
            Regency::create([
                'id' => $row[0],
                'province_id' => $row[1],
                'name' => $row[2],
            ]);
        }
    }
}
