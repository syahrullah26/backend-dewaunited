<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('provinces')) {
            return;
        }

        if (DB::table('provinces')->exists()) {
            return;
        }

        $file = base_path('database/seeders/data/regions/provinces.csv');

        if (!file_exists($file)) {
            throw new \Exception("CSV file not found: {$file}");
        }

        $rows = array_map('str_getcsv', file($file));
        unset($rows[0]);
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'id'   => $row[0],
                'name' => $row[1],
            ];
        }

        DB::table('provinces')->insert($data);
    }
}
