<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegencySeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('regencies')) {
            return;
        }

        if (DB::table('regencies')->exists()) {
            return;
        }

        $file = base_path('database/seeders/data/regions/regencies.csv');

        if (!file_exists($file)) {
            throw new \Exception("CSV file not found: {$file}");
        }

        $rows = array_map('str_getcsv', file($file));

        unset($rows[0]);

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'id'          => $row[0],
                'province_id' => $row[1],
                'name'        => $row[2],
            ];
        }

        DB::table('regencies')->insert($data);
    }
}
