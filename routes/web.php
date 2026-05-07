<?php

use App\Http\Controllers\BarangController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Barokah Toserba
|--------------------------------------------------------------------------
*/

// Halaman Utama: Menampilkan Daftar Inventaris
// Ditambahkan ->name('barang.index') agar tidak "undefined" saat dipanggil di Blade
Route::get('/', [BarangController::class, 'index'])->name('barang.index');

// Resource Routes untuk Kelola Barang
Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
Route::put('/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');

// Halaman POS (Point of Sale)
Route::get('/pos', [BarangController::class, 'pos'])->name('pos.index');

// Route untuk Transaksi (Pengurangan Stok)
Route::post('/transaksi', [BarangController::class, 'transaksi'])->name('transaksi.store');

// Halaman Laporan
Route::get('/laporan', [BarangController::class, 'laporan'])->name('laporan.index');

Route::delete('/barang/bulk-delete', [BarangController::class, 'destroyBulk'])->name('barang.bulkDelete');