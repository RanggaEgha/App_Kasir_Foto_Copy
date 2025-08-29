<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
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

        /* ── Blade directives: uang Rupiah ───────────────────── */
        Blade::directive('rupiah', function ($expression) {
            return "<?php echo 'Rp'.number_format((float)($expression ?? 0), 0, ',', '.'); ?>";
        });
        Blade::directive('idr', function ($expression) {
            return "<?php echo number_format((float)($expression ?? 0), 0, ',', '.'); ?>";
        });
    }
}
