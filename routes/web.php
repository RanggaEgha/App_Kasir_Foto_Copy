<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Controllers\{
    DashboardController,
    BarangController,
    JasaController,
    HistoryTransaksiController,
    SupplierController,
    PurchaseOrderController,
    ShiftController,
    PembayaranController,
    UserManagementController,
    AuditLogController
};
use App\Models\{AuditLog, User};

/*
|--------------------------------------------------------------------------
| Redirect root → home (role-aware)
| - Guest → login (ditangani di /home)
| - Admin → /dashboard
| - Kasir → /pembayaran (halaman POS)
|--------------------------------------------------------------------------
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
| KASIR & ADMIN
| Halaman yang boleh diketahui kasir (stealth pakai middleware role)
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
        // Rute spesifik dulu agar tidak ditelan '/{transaksi}'
        Route::get('/{transaksi}/receipt', [HistoryTransaksiController::class, 'receipt'])->name('receipt'); // struk print
        Route::get('/{transaksi}/pdf',     [HistoryTransaksiController::class, 'pdf'])->name('pdf');
        Route::post('/{transaksi}/post',   [HistoryTransaksiController::class, 'post'])->name('post');

        Route::get('/',            [HistoryTransaksiController::class, 'index'])->name('index');
        Route::get('/{transaksi}', [HistoryTransaksiController::class, 'show'])->name('show');
    });

    // Aktivitas saya (kasir/admin melihat log miliknya sendiri)
    Route::get('/aktivitas-saya', function () {
        $logs = AuditLog::where('actor_id', auth()->id())
            ->latest('id')
            ->paginate(20);

        return view('audit_logs.my', compact('logs'));
    })->name('audit.mine');
});


/*
|--------------------------------------------------------------------------
| ADMIN-ONLY (kasir tidak boleh “tahu” → 404)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard',       [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/pdf',   [DashboardController::class, 'pdf'])->name('dashboard.pdf');
    Route::get('/dashboard/excel', [DashboardController::class, 'excel'])->name('dashboard.excel');

    // Supplier & Purchase
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchases', PurchaseOrderController::class);

    // Manajemen User (admin-only)
    Route::resource('users', UserManagementController::class);

    // Audit Trail (admin-only)
    Route::get('/audit-logs',            [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit.show');

    // Notifikasi (admin) — tandai dibaca (per item)
    Route::post('/notifications/{id}/read', function (string $id) {
        $n = DatabaseNotification::findOrFail($id);
        // Amankan: hanya boleh menandai notifikasi milik dirinya
        abort_unless(
            $n->notifiable_type === User::class && $n->notifiable_id === auth()->id(),
            403
        );
        $n->markAsRead();
        return back();
    })->name('notifications.read');

    // Tandai semua notifikasi sebagai dibaca (hanya milik user login)
    Route::post('/notifications/read-all', function () {
        auth()->user()?->unreadNotifications()?->update(['read_at' => now()]);
        return back();
    })->name('notifications.read_all');

    // Bersihkan semua notifikasi (hapus) milik user login
    Route::delete('/notifications/clear', function () {
        DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', auth()->id())
            ->delete();

        return back();
    })->name('notifications.clear');
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
