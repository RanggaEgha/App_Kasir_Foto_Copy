<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    BarangController,
    JasaController,
    TransaksiController,
    SupplierController,
    PurchaseOrderController
};

/* ───── Dashboard ───── */
Route::redirect('/', '/dashboard');
Route::get   ('/dashboard',        [DashboardController::class,'index'])->name('dashboard');
Route::get   ('/dashboard/pdf',    [DashboardController::class,'pdf'  ])->name('dashboard.pdf');
Route::get   ('/dashboard/excel',  [DashboardController::class,'excel'])->name('dashboard.excel');

/* ───── Barang & Jasa (resource) ───── */
Route::resource('barang', BarangController::class);
Route::resource('jasa',   JasaController::class);
Route::get ('/barang/{id}/units',  [BarangController::class,'editUnits'])->name('barang.units.edit');
Route::put ('/barang/{id}/units',  [BarangController::class,'updateUnits'])->name('barang.units.update');

/* ───── Transaksi (resource + PDF) ───── */
Route::resource('transaksi', TransaksiController::class);
Route::get('/transaksi/{id}/pdf', [TransaksiController::class,'pdf'])->name('transaksi.pdf');



/* ───── Supplier (tanpa auth) ───── */
Route::resource('suppliers', SupplierController::class);
Route::resource('purchases', PurchaseOrderController::class);

