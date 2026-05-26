<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - BAROKAH POS
|--------------------------------------------------------------------------
*/

// --- RUTE AUTENTIKASI (BISA DIAKSES TANPA LOGIN) ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// --- RUTE UTAMA (WAJIB LOGIN) ---
Route::middleware('auth')->group(function () {
    
    // 1. HALAMAN UTAMA / INVENTARIS BARANG
    Route::get('/', [BarangController::class, 'index'])->name('barang.index');
    
    // 2. HALAMAN POINT OF SALE (POS) KASIR
    Route::get('/pos', [BarangController::class, 'pos'])->name('pos.index');
    
    // 3. HALAMAN LAPORAN TRANSAKSI
    Route::get('/laporan', [BarangController::class, 'laporan'])->name('laporan.index');
    
    // 4. FITUR MANIPULASI DATA BARANG (CRUD & IMPORT)
    // Note: bulk-delete ditaruh di atas route wildcard {id} biar gak tabrakan
    Route::post('/barang/bulk-delete', [BarangController::class, 'bulkDelete'])->name('barang.bulkDelete');
    Route::post('/import-csv', [BarangController::class, 'importCsv'])->name('barang.import');
    
    // ⬇️ TAMBAHAN RUTE UNTUK FORM TAMBAH & EDIT BARANG DI INVENTARIS ⬇️
    Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
    Route::put('/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
    
    // HAPUS DATA BARANG
    Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');
    
    // 5. FITUR TRANSAKSI KASIR POS
    Route::post('/transaksi', [BarangController::class, 'transaksi'])->name('barang.transaksi');
    
});