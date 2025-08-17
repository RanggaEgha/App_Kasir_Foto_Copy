<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Pastikan kolom-kolom barunya sudah ada (migration sebelumnya)
        if (!Schema::hasTable('transaksis')) return;

        // 1) Hitung total_harga dari item jika kosong/null
        DB::statement("
            UPDATE transaksis t
            LEFT JOIN (
                SELECT transaksi_id, COALESCE(SUM(subtotal),0) AS sum_sub
                FROM transaksi_items
                GROUP BY transaksi_id
            ) x ON x.transaksi_id = t.id
            SET t.total_harga = COALESCE(t.total_harga, x.sum_sub)
            WHERE t.total_harga IS NULL OR t.total_harga = 0
        ");

        // 2) posted_at default = tanggal (kalau ada), else created_at
        DB::statement("
            UPDATE transaksis t
            SET t.posted_at = COALESCE(t.posted_at, t.tanggal, t.created_at)
        ");

        // 3) status default: kalau bukan void → posted
        DB::statement("
            UPDATE transaksis t
            SET t.status = 'posted'
            WHERE (t.status IS NULL OR t.status = '' OR t.status = 'draft')
        ");
        // Catatan: jika kamu sudah punya penanda void lama, sesuaikan update di atas.

        // 4) payment_status berdasarkan dibayar vs total_harga
        DB::statement("
            UPDATE transaksis t
            SET t.payment_status = CASE
                WHEN COALESCE(t.dibayar,0) >= COALESCE(t.total_harga,0) THEN 'paid'
                WHEN COALESCE(t.dibayar,0)  > 0                             THEN 'partial'
                ELSE 'unpaid'
            END
        ");

        // 5) kembalian = max(dibayar - total, 0)
        DB::statement("
            UPDATE transaksis t
            SET t.kembalian = GREATEST(COALESCE(t.dibayar,0) - COALESCE(t.total_harga,0), 0)
            WHERE t.kembalian IS NULL OR t.kembalian = 0
        ");

        // 6) shift_id NULL untuk semua histori lama (supaya tidak memengaruhi rekonsiliasi shift)
        if (Schema::hasColumn('transaksis','shift_id')) {
            DB::statement("UPDATE transaksis SET shift_id = NULL WHERE shift_id IS NULL OR shift_id = 0");
        }
    }

    public function down(): void
    {
        // Tidak roll back (hindari kehilangan data)
    }
};
