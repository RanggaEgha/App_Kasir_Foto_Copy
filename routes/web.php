<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    BarangController,
    JasaController,
    HistoryTransaksiController,
    SupplierController,
    PurchaseOrderController,
    ShiftController,
    PembayaranController,
    UserManagementController
};

/*
|--------------------------------------------------------------------------
| Redirect root → home (role-aware)
|--------------------------------------------------------------------------
| - Guest → login (ditangani di /home)
| - Admin → /dashboard
| - Kasir → /pembayaran (halaman POS)
*/
Route::redirect('/', '/home');

Route::get('/home', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    // Jaga flash message (mis. 'welcome') agar tidak hilang saat redirect kedua
    session()->reflash();

    return auth()->user()->role === 'admin'
        ? redirect()->route('dashboard')
        : redirect()->route('pembayaran.create');
})->name('home');


/*
|--------------------------------------------------------------------------
| KASIR & ADMIN (halaman yang boleh diketahui kasir)
| Stealth: pakai middleware 'role:kasir,admin' → user di luar role ini akan 404
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:kasir,admin'])->group(function () {

    // Master Barang (FULL CRUD) + Units
    Route::resource('barang', BarangController::class);
    Route::get('/barang/{id}/units', [BarangController::class, 'editUnits'])->name('barang.units.edit');
    Route::put('/barang/{id}/units', [BarangController::class, 'updateUnits'])->name('barang.units.update');

    // Master Jasa (FULL CRUD)
    Route::resource('jasa', JasaController::class);

    // Shift Kasir
    Route::prefix('shift')->name('shift.')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::post('/open', [ShiftController::class, 'open'])->name('open');
        Route::post('/close/{shift}', [ShiftController::class, 'close'])->name('close');
    });

    // Pembayaran (POS)
    Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
        Route::get('/', [PembayaranController::class, 'create'])->name('create');              // Halaman POS
        Route::post('/store', [PembayaranController::class, 'store'])->name('store');          // Simpan transaksi
        Route::post('/pay/{transaksi}', [PembayaranController::class, 'pay'])->name('pay');    // Tambah pembayaran
        Route::post('/void/{transaksi}', [PembayaranController::class, 'void'])->name('void'); // Batalkan transaksi
    });

    // History Transaksi (read-only + actions)
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/', [HistoryTransaksiController::class, 'index'])->name('index');
        Route::get('/{transaksi}', [HistoryTransaksiController::class, 'show'])->name('show');
        Route::get('/{transaksi}/pdf', [HistoryTransaksiController::class, 'pdf'])->name('pdf');
        Route::post('/{transaksi}/post', [HistoryTransaksiController::class, 'post'])->name('post');
    });
});


/*
|--------------------------------------------------------------------------
| ADMIN-ONLY (kasir tidak boleh “tahu” → 404)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/pdf', [DashboardController::class, 'pdf'])->name('dashboard.pdf');
    Route::get('/dashboard/excel', [DashboardController::class, 'excel'])->name('dashboard.excel');

    // Supplier & Purchase
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchases', PurchaseOrderController::class);

    // Manajemen User (admin-only)
    Route::resource('users', UserManagementController::class);
});


/*
|--------------------------------------------------------------------------
| Auth routes (Laravel Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';


/*
|--------------------------------------------------------------------------
| Fallback 404 (jaga-jaga)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    abort(404);
});
