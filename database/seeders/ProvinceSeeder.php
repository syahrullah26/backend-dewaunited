<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('provinces')->truncate();

        $file = storage_path('app/regions/provinces.csv');
        $rows = array_map('str_getcsv', file($file));

        unset($rows[0]);

        foreach ($rows as $row) {
            Province::create([
                'id'   => $row[0],
                'name' => $row[1],
            ]);
        }
    }
}
