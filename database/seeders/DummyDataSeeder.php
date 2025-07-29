<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Faker\Factory as Faker;
use App\Models\Barang;
use App\Models\Jasa;
use App\Models\Supplier;
use App\Models\PurchaseOrder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        /* ---------- LIST NAMA BARANG (50) ---------- */
        $barangNames = [
            'Kertas HVS A4 70gsm',          'Kertas HVS A4 80gsm',
            'Kertas HVS F4 70gsm',          'Kertas HVS F4 80gsm',
            'Kertas Warna A4 (Mix)',        'Pulpen Standar AE7 Hitam',
            'Pulpen Standar AE7 Biru',      'Pulpen Standar AE7 Merah',
            'Pulpen Pilot G-2 0.5 Hitam',   'Pulpen Pilot G-2 0.5 Biru',
            'Pulpen Pilot G-2 0.5 Merah',   'Pensil 2B Faber-Castell',
            'Pensil HB Faber-Castell',      'Stabilo Bos Kuning',
            'Stabilo Bos Hijau',            'Penghapus Staedtler Mars Plastic',
            'Tipe-X Joyko',                'Map Kancing A4',
            'Map Kancing F4',               'Clear Holder A4 20lbr',
            'Clear Holder A4 40lbr',        'Binder Clip 25 mm',
            'Binder Clip 41 mm',            'Lakban Coklat 2″',
            'Lakban Bening 2″',             'Lakban Kertas 1″',
            'Lakban Double Tape 1″',        'Spiral Binding 10 mm',
            'Spiral Binding 15 mm',         'Cover Mika A4 200 µ',
            'Cover Mika F4 200 µ',          'Karton Buffalo A4',
            'Karton Buffalo F4',            'Photo Paper A4 210 gsm',
            'Photo Paper 4R 260 gsm',       'CD-R 700 MB',
            'Flashdisk 16 GB Sandisk',      'Flashdisk 32 GB Sandisk',
            'Tinta Canon 810',              'Tinta Canon 811',
            'Tinta Epson 003 BK',           'Tinta Epson 003 C',
            'Tinta Epson 003 M',            'Tinta Epson 003 Y',
            'Kertas Struk 58 mm',           'Kertas Struk 80 mm',
            'Sticky Note 3×3',              'Sticky Note 5×3',
            'Clip Kertas 28 mm',            'Clip Kertas 33 mm',
            'Pembolong Kertas Joyko 2 Holes','Cutter Kecil Joyko'
        ];

        /* ---------- LIST NAMA JASA (50) ------------ */
        $jasaNames = [
            'Fotokopi Hitam Putih A4',      'Fotokopi Hitam Putih F4',
            'Fotokopi Warna A4',            'Fotokopi Warna F4',
            'Print Hitam Putih A4',         'Print Warna A4',
            'Scan Dokumen A4',              'Scan Dokumen F4',
            'Laminating A4',                'Laminating F4',
            'Jilid Spiral 10 mm',           'Jilid Spiral 15 mm',
            'Jilid Spiral 20 mm',           'Jilid Lakban A4',
            'Jilid Lakban F4',              'Cetak Foto 4R',
            'Cetak Foto 5R',                'Cetak Poster A3',
            'Cetak Poster A2',              'Cetak Banner 60×160 cm',
            'Cetak Kartu Nama',             'Cetak Undangan A5',
            'Desain Grafis Sederhana',      'Input Data / Ketik Ulang',
            'Burn CD Data',                 'Burn DVD Data',
            'Print Label Nomor',            'Cetak Brosur A4',
            'Cetak Amplop',                 'Cetak Kop Surat',
            'Cetak Kalender Meja',          'Cetak Stiker A4',
            'Print Film Sablon A3',         'Cutting Sticker per m',
            'Plotter Blueprint A1',         'Plotter Drawing A2',
            'Binding Buku Hard Cover',      'Binding Buku Soft Cover',
            'Scan Foto',                    'Jasa Pengiriman Email PDF',
            'Print Map Rapor',              'Cetak Buku',
            'Cetak Majalah',                'Cetak Agenda',
            'Laminating ID Card',           'Fotokopi ASPT Buku',
            'Jasa Pembuatan Stempel',       'Jasa Foto & Print',
            'Jasa Pembuatan Spanduk',       'Jasa Pembuatan Banner'
        ];

        /* ========== SEED BARANG ========== */
        foreach ($barangNames as $nama) {
            Barang::create([
                'nama'           => $nama,
                'kategori'       => str_contains($nama, 'Kertas') ? 'Kertas'
                                   : (str_contains($nama, 'Tinta') ? 'Tinta' : 'ATK'),
                'tipe_penjualan' => 'satuan',
                'satuan'         => 'pcs',
                'harga_satuan'   => $faker->numberBetween(2_000, 50_000),
                'stok_satuan'    => $faker->numberBetween(10, 300),
            ]);
        }

        /* ========== SEED JASA ============ */
        foreach ($jasaNames as $nama) {
            $jenis = match (true) {
                str_contains($nama,'Fotokopi')   => 'Fotokopi',
                str_contains($nama,'Print')      => 'Print',
                str_contains($nama,'Scan')       => 'Scan',
                str_contains($nama,'Jilid')      => 'Jilid',
                str_contains($nama,'Laminating') => 'Laminating',
                default                          => 'Lain-lain',
            };

            Jasa::create([
                'nama'             => $nama,
                'jenis'            => $jenis,
                'satuan'           => in_array($jenis, ['Fotokopi','Print','Scan','Laminating'])
                                      ? 'lembar' : 'set',
                'harga_per_satuan' => $faker->numberBetween(500, 10_000),
            ]);
        }

        /* ========== SUPPLIER ============ */
        $supplier = Supplier::firstOrCreate(
            ['name' => 'PT Sumber ATK Sejahtera'],
            ['phone' => '021-5551111', 'address' => 'Jl. Copy Center No. 1 Karawang']
        );

        /* ===== PURCHASE ORDERS (50) ===== */
        foreach (range(1, 50) as $i) {
            PurchaseOrder::create([
                'supplier_id'   => $supplier->id,
                'invoice_no'    => 'INV' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'purchase_date' => now()->subDays(rand(0, 30)),
                'payment_method'=> Arr::random(['cash','transfer']),
                'amount_paid'   => $faker->numberBetween(100_000, 500_000),
                'total'         => $faker->numberBetween(100_000, 500_000),
                'notes'         => "PO dummy ke-$i",
            ]);
        }
    }
}
