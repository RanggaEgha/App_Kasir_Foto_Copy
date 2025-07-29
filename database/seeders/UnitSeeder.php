<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'pcs',  'konversi' => 1],
            ['kode' => 'pack', 'konversi' => 12],   // 1 pack  = 12 pcs
            ['kode' => 'lusin','konversi' => 144],  // 1 lusin = 12 pack = 144 pcs
        ];

        foreach ($data as $u)
            Unit::updateOrCreate(['kode' => $u['kode']], $u);
    }
}
