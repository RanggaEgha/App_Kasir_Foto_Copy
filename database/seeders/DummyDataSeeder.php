<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\{Barang, Jasa, Unit};

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        /* ---------- ambil id unit ---------- */
        $pcs   = Unit::where('kode','pcs')->value('id');
        $paket = Unit::where('kode','paket')->value('id');

        /* ---------- 10 BARANG SAJA ---------- */
        $barangNames = [
            'Kertas HVS A4 80 gsm',
            'Pulpen Standar AE7 Hitam',
            'Photo Paper A4 210 gsm',
            'Tinta Epson 003 Black',
            'Lakban Bening 2″',
            'Flashdisk 16 GB Sandisk',
            'Amplop Cokelat Folio',
            'Spidol Whiteboard Snowman',
            'Binder Clip 32 mm',
            'Map Plastik L Folder'
        ];

        foreach ($barangNames as $nama) {
            $barang = Barang::create([
                'nama'     => $nama,
                'kategori' => str_contains($nama,'Kertas') ? 'Kertas'
                             : (str_contains($nama,'Tinta') ? 'Tinta' : 'ATK'),
            ]);

            /* stok & harga per unit */
            $pricePcs   = $faker->numberBetween(1000, 50000);
            $stockPcs   = $faker->numberBetween(50, 300);
            $pricePaket = $pricePcs * 50 * 0.9;
            $stockPaket = max(1, intdiv($stockPcs, 50));

            $barang->units()->attach($pcs,   ['harga'=>$pricePcs,   'stok'=>$stockPcs]);
            $barang->units()->attach($paket, ['harga'=>$pricePaket, 'stok'=>$stockPaket]);

            /* kolom lama (fallback) */
            $barang->forceFill([
                'tipe_penjualan'=>'satuan',
                'satuan'        =>'pcs',
                'harga_satuan'  =>$pricePcs,
                'stok_satuan'   =>$stockPcs,
                'harga_paket'   =>$pricePaket,
                'stok_paket'    =>$stockPaket,
                'isi_per_paket' =>50,
            ])->save();
        }

        /* ---------- JASA (5 contoh) ---------- */
        $jasaNames = [
            'Fotokopi Hitam-Putih A4',
            'Print Warna A4',
            'Scan Dokumen A4',
            'Jilid Spiral 10 mm',
            'Laminating A4'
        ];

        foreach ($jasaNames as $nama) {
            Jasa::create([
                'nama'             => $nama,
                'jenis'            => 'Layanan',
                'satuan'           => 'lembar',
                'harga_per_satuan' => $faker->numberBetween(500, 3000),
            ]);
        }
    }
}
