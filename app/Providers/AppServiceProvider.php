<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

// Registrasi observer stok
use App\Models\BarangUnitPrice;
use App\Observers\BarangUnitPriceObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /* ── Locale & Timezone global ─────────────────────────── */
        Carbon::setLocale('id');                 // Bahasa Indonesia
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');

        /* ── Kompatibilitas MySQL/MariaDB lama ────────────────── */
        // Hindari error "Specified key was too long" di beberapa environment
        Schema::defaultStringLength(191);

        /* ── Model Observers ───────────────────────────────────── */
        // Penting: supaya perubahan stok via Eloquent ->save() memicu notifikasi
        BarangUnitPrice::observe(BarangUnitPriceObserver::class);
    }
}
