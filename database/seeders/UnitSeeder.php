<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode'=>'pcs',   'konversi'=>1],     // satuan dasar
            ['kode'=>'paket', 'konversi'=>50],    // 1 paket = 50 pcs
            ['kode'=>'lembar','konversi'=>1],
            ['kode'=>'box',   'konversi'=>100],
            ['kode'=>'lusin', 'konversi'=>12],
        ];

        foreach ($data as $u)
            Unit::updateOrCreate(['kode'=>$u['kode']], $u);
    }
}
