<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    // Halaman Inventaris Utama
    public function index(Request $request) {
        $search = $request->input('search');
        $items = Barang::when($search, function ($query) use ($search) {
            return $query->where('nama_barang', 'like', "%{$search}%")
                         ->orWhere('kode_barang', 'like', "%{$search}%");
        })->latest()->paginate(10);

        return view('inventaris', compact('items'));
    }

    // Fungsi POS Baru (Untuk menampilkan halaman kasir)
    public function pos() {
        // Mengambil semua barang untuk pilihan di kasir
        $items = Barang::where('stok', '>', 0)->get(); 
        return view('pos', compact('items'));
    }

    public function store(Request $request) {
        Barang::create($request->all());
        return back()->with('success', 'Barang berhasil ditambah!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'required',
            'harga1'      => 'required|numeric',
            'stok'        => 'required|numeric|min:0',
        ]);

        $item = Barang::findOrFail($id);
        
        $item->update([
            'nama_barang' => $request->nama_barang,
            'harga1'      => $request->harga1,
            'stok'        => $request->stok,
            'kategori'    => $request->kategori,
            'kode_barang' => $request->kode_barang,
        ]);

        return back()->with('success', 'Stok dan data barang berhasil diperbarui!');
    }

    public function destroy($id) {
        Barang::destroy($id);
        return back()->with('success', 'Barang dihapus!');
    }

    // Update Fungsi Transaksi: Mendukung Jual Cepat & POS
   public function transaksi(Request $request) {
    // 1. Ambil data cart dari request (dikirim via AJAX dari halaman POS)
    $cart = $request->input('cart'); 

    if (!$cart || count($cart) == 0) {
        return response()->json(['message' => 'Keranjang kosong!'], 400);
    }

    try {
        DB::transaction(function () use ($cart) {
            foreach ($cart as $item) {
                $barang = Barang::findOrFail($item['id']);

                // 2. Cek apakah stok cukup
                if ($barang->stok < $item['qty']) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi!");
                }

                // 3. Kurangi stok barang
                $barang->decrement('stok', $item['qty']);

                // 4. Catat ke tabel penjualan agar Laporan otomatis terupdate
                Penjualan::create([
                    'barang_id' => $barang->id,
                    'jumlah' => $item['qty'],
                    'total_harga' => $barang->harga1 * $item['qty'],
                    'tanggal_jual' => now(), // Mengambil waktu sekarang
                ]);
            }
        });

        return response()->json(['message' => 'Transaksi Berhasil & Stok Terupdate!']);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }


        // Cek apakah ini transaksi dari POS (Array Barang)
        if ($request->has('cart')) {
            DB::transaction(function () use ($request) {
                foreach ($request->cart as $item) {
                    $barang = Barang::findOrFail($item['id']);
                    $barang->decrement('stok', $item['qty']);
                    
                    Penjualan::create([
                        'barang_id' => $barang->id,
                        'jumlah' => $item['qty'],
                        'total_harga' => $barang->harga1 * $item['qty'],
                        'tanggal_jual' => now()->format('Y-m-d'),
                    ]);
                }
            });
            return response()->json(['message' => 'Transaksi POS Berhasil']);
        }
        
        return back()->with('error', 'Data transaksi tidak valid');
    }
public function laporan(Request $request) {
    $view = $request->get('view', 'monthly');

    try {
        if ($view == 'daily') {
            // Laporan 7 hari terakhir untuk SQLite
            $data = Penjualan::select(
                DB::raw('date(tanggal_jual) as label'), 
                DB::raw('SUM(total_harga) as total')
            )
            ->where('tanggal_jual', '>=', now()->subDays(7))
            ->groupBy('label')
            ->orderBy('label', 'ASC')
            ->get();
        } else {
            // Laporan bulanan untuk SQLite
            // strftime('%m') mengambil angka bulan (01-12)
            $data = Penjualan::select(
                DB::raw("strftime('%m', tanggal_jual) as urutan"),
                DB::raw("
                    CASE strftime('%m', tanggal_jual)
                        WHEN '01' THEN 'Januari' WHEN '02' THEN 'Februari'
                        WHEN '03' THEN 'Maret' WHEN '04' THEN 'April'
                        WHEN '05' THEN 'Mei' WHEN '06' THEN 'Juni'
                        WHEN '07' THEN 'Juli' WHEN '08' THEN 'Agustus'
                        WHEN '09' THEN 'September' WHEN '10' THEN 'Oktober'
                        WHEN '11' THEN 'November' WHEN '12' THEN 'Desember'
                    END as label
                "),
                DB::raw('SUM(total_harga) as total')
            )
            ->whereYear('tanggal_jual', date('Y'))
            ->groupBy('urutan', 'label')
            ->orderBy('urutan', 'ASC')
            ->get();
        }
    } catch (\Exception $e) {
        $data = collect();
    }

    return view('laporan', compact('data', 'view'));
}


    public function destroyBulk(Request $request) {
        $ids = $request->ids;
        if ($ids) {
            Barang::whereIn('id', $ids)->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Tidak ada data'], 400);
    }
}