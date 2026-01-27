<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('districts')) {
            return;
        }

        if (DB::table('districts')->exists()) {
            return;
        }
        $file = base_path('database/seeders/data/regions/districts.csv');

        if (!file_exists($file)) {
            throw new \Exception("CSV file not found: {$file}");
        }

        $rows = array_map('str_getcsv', file($file));


        unset($rows[0]);

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'id'         => $row[0],
                'regency_id' => $row[1],
                'name'       => $row[2],
            ];
        }

        DB::table('districts')->insert($data);
    }
}
