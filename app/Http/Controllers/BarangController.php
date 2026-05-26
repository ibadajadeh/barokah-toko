<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    /**
     * Halaman Inventaris Utama
     * Menampilkan 10 data per halaman dengan pencarian ILIKE (Postgres Aman)
     */
    public function index(Request $request) {
        $search = $request->input('search');
        
        $items = Barang::when($search, function ($query) use ($search) {
            return $query->where('nama_barang', 'ILIKE', "%{$search}%")
                         ->orWhere('kode_barang', 'ILIKE', "%{$search}%");
        })->latest()->paginate(10)->withQueryString();

        return view('inventaris', compact('items'));
    }

    /**
     * Fungsi POS AJAX (Untuk menampilkan halaman kasir anti-lag)
     * Mengambil data per 12 item sesuai request pagination JavaScript
     */
    public function pos(Request $request) {
        $query = Barang::query()->where('stok', '>', 0);

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('nama_barang', 'ILIKE', '%' . $request->search . '%')
                  ->orWhere('kode_barang', 'ILIKE', '%' . $request->search . '%');
            });
        }

        // Batasi 12 item per halaman kasir
        $items = $query->orderBy('nama_barang', 'asc')->paginate(100);

        // Jika dipanggil lewat AJAX Fetch di halaman kasir
        if ($request->ajax()) {
            return response()->json([
                'items'        => $items->items(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'from'         => $items->firstItem(),
                'to'           => $items->lastItem(),
                'total'        => $items->total(),
            ]);
        }

        return view('pos', compact('items'));
    }

    /**
     * Simpan Barang Baru (Mode Tambah)
     */
    public function store(Request $request) {
        $request->validate([
            'kode_barang' => 'required|unique:barangs,kode_barang',
            'nama_barang' => 'required',
            'stok'        => 'required|numeric|min:0',
            'harga1'      => 'required|numeric|min:0',
        ]);

        Barang::create([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'kategori'    => $request->kategori ?? 'UMUM',
            'stok'        => $request->stok,
            'harga1'      => $request->harga1,
        ]);

        return back()->with('success', 'Barang baru berhasil berkah ditambahkan!');
    }

    /**
     * Update Data Barang (Mode Edit)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'required',
            'harga1'      => 'required|numeric|min:0',
            'stok'        => 'required|numeric|min:0',
        ]);

        $item = Barang::findOrFail($id);
        
        $item->update([
            'nama_barang' => $request->nama_barang,
            'harga1'      => $request->harga1,
            'stok'        => $request->stok,
            'kategori'    => $request->kategori ?? 'UMUM',
            'kode_barang' => $request->kode_barang,
        ]);

        return back()->with('success', 'Stok dan data barang berhasil diperbarui!');
    }

    /**
     * Hapus Satu Barang
     */
    public function destroy($id) {
        $item = Barang::findOrFail($id);
        $item->delete();
        return back()->with('success', 'Barang berhasil dihapus!');
    }

    /**
     * Proses Transaksi Kasir POS (Stok Terpotong Aman & Catat Laporan)
     */
    public function transaksi(Request $request) {
        $cart = $request->input('cart'); 

        if (!$cart || count($cart) == 0) {
            return response()->json(['message' => 'Keranjang masih kosong!'], 400);
        }

        try {
            DB::transaction(function () use ($cart) {
                foreach ($cart as $item) {
                    $barang = Barang::findOrFail($item['id']);

                    // Proteksi ganda: Cek kecukupan stok fisik di gudang
                    if ($barang->stok < $item['qty']) {
                        throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi!");
                    }

                    // Kurangi stok barang asli
                    $barang->decrement('stok', $item['qty']);

                    // Catat riwayat ke tabel penjualan untuk Laporan
                    Penjualan::create([
                        'barang_id'    => $barang->id,
                        'jumlah'       => $item['qty'],
                        'total_harga'  => $barang->harga1 * $item['qty'],
                        'tanggal_jual' => now(),
                    ]);
                }
            });

            return response()->json(['message' => 'Transaksi Berhasil & Stok Terupdate!']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Laporan Manajemen Keuangan POS
     */
    public function laporan(Request $request)
    {
        $filterType = $request->input('filter_type', 'hari_ini'); 
        $selectedDate = $request->input('tanggal'); 
        $selectedMonth = $request->input('bulan', date('m')); 
        $selectedYear = $request->input('tahun', date('Y'));

        $queryPenjualan = DB::table('transaksis'); 

        if ($filterType === 'hari_ini') {
            $queryPenjualan->whereDate('created_at', now()->today());
            $labelWaktu = "Hari Ini (" . now()->format('d M Y') . ")";
        } elseif ($filterType === 'kustom_tanggal' && $selectedDate) {
            $queryPenjualan->whereDate('created_at', $selectedDate);
            $labelWaktu = "Tanggal " . date('d M Y', strtotime($selectedDate));
        } elseif ($filterType === 'bulanan') {
            $queryPenjualan->whereRaw("EXTRACT(MONTH FROM created_at) = ?", [$selectedMonth])
                           ->whereRaw("EXTRACT(YEAR FROM created_at) = ?", [$selectedYear]);
            $namaBulan = date('F', mktime(0, 0, 0, $selectedMonth, 10));
            $labelWaktu = "Bulan $namaBulan $selectedYear";
        } else {
            $queryPenjualan->whereDate('created_at', now()->today());
            $labelWaktu = "Hari Ini (" . now()->format('d M Y') . ")";
        }

        $totalPenjualan = $queryPenjualan->sum('total_harga') ?? 0; 
        $totalTransaksiCount = $queryPenjualan->count();
        $listPenjualan = $queryPenjualan->latest()->paginate(10)->withQueryString();

        $stokKritis = Barang::where('stok', '<=', 10)
                            ->orderBy('stok', 'asc')
                            ->get();

        return view('laporan', compact(
            'totalPenjualan', 'totalTransaksiCount', 'listPenjualan', 
            'stokKritis', 'labelWaktu', 'filterType', 'selectedDate', 
            'selectedMonth', 'selectedYear'
        ));
    }

    /**
     * Fitur Custom: Hapus Massal via Checkbox Terpilih (Bulk Delete)
     * Nama fungsi disinkronkan menjadi bulkDelete agar sesuai rute web.php
     */
    public function bulkDelete(Request $request) {
        $ids = $request->ids;
        if ($ids && is_array($ids)) {
            Barang::whereIn('id', $ids)->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Tidak ada data barang terpilih'], 400);
    }
}