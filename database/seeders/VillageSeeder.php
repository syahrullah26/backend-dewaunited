<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        //DB::table('villages')->truncate();

        $file = database_path('seeders/data/regions/villages.csv');
        $rows = array_map('str_getcsv', file($file));

        unset($rows[0]);

        $data = [];
        $chunkSize = 500; 

        foreach ($rows as $row) {
            $data[] = [
                'id' => $row[0],
                'district_id' => $row[1],
                'name' => $row[2],
            ];

            if (count($data) === $chunkSize) {
                DB::table('villages')->insert($data);
                $data = [];
            }
        }
        if (!empty($data)) {
            DB::table('villages')->insert($data);
        }
    }
}
