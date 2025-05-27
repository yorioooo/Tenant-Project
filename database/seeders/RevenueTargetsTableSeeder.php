<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RevenueTargetsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('revenue_targets')->insert([
            [
                'periode' => 2025,
                'bulan' => 4,
                'revenue_target' => 1000000,
                'flag' => 'Y',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'periode' => 2025,
                'bulan' => 5,
                'revenue_target' => 1200000,
                'flag' => 'Y',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'periode' => 2025,
                'bulan' => 6,
                'revenue_target' => 900000,
                'flag' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
