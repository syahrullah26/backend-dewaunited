<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\District;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('districts')->truncate();

        $file = storage_path('app/regions/districts.csv');
        $rows = array_map('str_getcsv', file($file));

        unset($rows[0]); // skip header

        foreach ($rows as $row) {
            District::create([
                'id' => $row[0],
                'regency_id' => $row[1],
                'name' => $row[2],
            ]);
        }
    }
}
