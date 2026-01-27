<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        // 🔒 Pastikan tabel ada
        if (!Schema::hasTable('villages')) {
            return;
        }

        // 🔒 Jangan seed ulang (aman redeploy)
        if (DB::table('villages')->exists()) {
            return;
        }

        // ✅ Path CSV dari repo (Railway-safe)
        $file = base_path('database/seeders/data/regions/villages.csv');

        if (!file_exists($file)) {
            throw new \Exception("CSV file not found: {$file}");
        }

        $rows = array_map('str_getcsv', file($file));

        // Skip header
        unset($rows[0]);

        // ⚡ Batch insert (WAJIB untuk data desa)
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'id'          => $row[0],
                'district_id' => $row[1],
                'name'        => $row[2],
            ];
        }

        DB::table('villages')->insert($data);
    }
}
