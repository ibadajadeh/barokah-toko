@extends('layouts.app')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-white tracking-tight">Inventaris <span class="text-cyan-400">&</span> Stok</h1>
        <p class="text-gray-500 text-sm">Kelola 18.000+ data barang Barokah Toserba</p>
    </div>
    <div class="flex gap-3">
        <!-- Tombol Hapus Terpilih (Otomatis Muncul via JS) -->
        <button id="bulk-delete-btn" class="hidden bg-red-600/20 text-red-500 border border-red-500/50 px-5 py-2.5 rounded-xl hover:bg-red-600 hover:text-white transition font-bold text-sm">
            🗑️ Hapus (<span id="selected-count">0</span>)
        </button>

        <button onclick="openModalForm('tambah')" class="bg-gray-800 border border-gray-700 px-5 py-2.5 rounded-xl hover:bg-gray-700 transition font-medium text-white">
            + Barang Baru
        </button>
        
        <a href="{{ route('pos.index') }}" class="bg-cyan-600 px-6 py-2.5 rounded-xl font-bold hover:bg-cyan-500 transition shadow-lg shadow-cyan-900/20 text-white flex items-center gap-2">
            ⚡ POS KASIR
        </a>
    </div>
</div>

<!-- Alert Notifikasi -->
@if(session('success'))
    <div class="bg-green-900/30 border border-green-500/50 text-green-400 p-4 rounded-2xl mb-6 flex items-center shadow-sm">
        <span class="mr-2">✅</span> {{ session('success') }}
    </div>
@endif

<!-- Search Bar Utama (Sudah Kembali) -->
<div class="mb-6">
    <form action="{{ route('barang.index') }}" method="GET" class="relative">
        <input type="text" name="search" placeholder="Cari Kode atau Nama Barang..." value="{{ request('search') }}" 
               class="w-full bg-gray-900 border border-gray-800 p-4 pl-12 rounded-2xl focus:border-cyan-500 outline-none transition text-white">
        <span class="absolute left-4 top-4 text-gray-600">🔍</span>
    </form>
</div>

<!-- Tabel Inventaris -->
<div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-2xl">
    <table class="w-full text-left border-collapse">
        <thead class="bg-gray-800/50 text-cyan-400 text-[11px] uppercase tracking-widest font-bold">
            <tr>
                <th class="p-5 border-b border-gray-800 w-12 text-center">
                    <input type="checkbox" id="check-all" class="w-4 h-4 rounded border-gray-700 bg-black text-cyan-500 focus:ring-cyan-600 focus:ring-offset-gray-900">
                </th>
                <th class="p-5 border-b border-gray-800">Nama Barang</th>
                <th class="p-5 border-b border-gray-800 text-center">Status Stok</th>
                <th class="p-5 border-b border-gray-800 text-right">Harga Jual</th>
                <th class="p-5 border-b border-gray-800 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($items as $item)
            <tr class="hover:bg-gray-800/40 transition group">
                <td class="p-5 text-center">
                    <input type="checkbox" class="item-checkbox w-4 h-4 rounded border-gray-700 bg-black text-cyan-500 focus:ring-cyan-600 focus:ring-offset-gray-900" value="{{ $item->id }}">
                </td>
                <td class="p-5">
                    <div class="font-semibold text-gray-200">{{ $item->nama_barang }}</div>
                    <div class="text-[10px] text-gray-600 font-mono uppercase">{{ $item->kode_barang }} | {{ $item->kategori }}</div>
                </td>
                <td class="p-5 text-center">
                    @if($item->stok <= 0)
                        <span class="bg-red-950/50 text-red-500 px-3 py-1 rounded-full text-[11px] font-bold border border-red-900">Habis</span>
                    @elseif($item->stok <= 10)
                        <span class="bg-orange-950/50 text-orange-500 px-3 py-1 rounded-full text-[11px] font-bold border border-orange-900">Sisa {{ $item->stok }}</span>
                    @else
                        <span class="text-gray-400 text-sm font-medium">{{ $item->stok }} <span class="text-[10px] text-gray-600 ml-1">Unit</span></span>
                    @endif
                </td>
                <td class="p-5 text-right font-mono text-green-400 font-bold">
                    <span class="text-[10px] text-gray-600 mr-1">Rp</span>{{ number_format($item->harga1, 0, ',', '.') }}
                </td>
                <td class="p-5">
                    <div class="flex justify-center gap-2">
                        <button onclick="openModalJual({{ $item->id }}, '{{ $item->nama_barang }}', {{ $item->harga1 }})" 
                                class="bg-green-600/10 text-green-500 px-3 py-1.5 rounded-lg hover:bg-green-600 hover:text-white transition text-xs font-bold">
                            ⚡ Jual
                        </button>
                        <button onclick="openModalForm('edit', {{ $item }})" 
                                class="bg-gray-800 text-gray-400 px-3 py-1.5 rounded-lg hover:bg-gray-700 hover:text-white transition text-xs">
                            Edit
                        </button>
                        <form action="{{ route('barang.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus barang ini?')">
                            @csrf @method('DELETE')
                            <button class="bg-gray-800 text-red-500/50 px-3 py-1.5 rounded-lg hover:bg-red-600 hover:text-white transition text-xs">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-10 text-center text-gray-500 italic">Data barang tidak ditemukan...</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $items->links() }}
</div>

<!-- Scripts -->
<script>
    // Logic Checkbox & Bulk Delete
    const checkAll = document.getElementById('check-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const selectedCountLabel = document.getElementById('selected-count');

    function updateBulkButton() {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        const checkedCount = checkedItems.length;
        
        if (checkedCount > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            selectedCountLabel.innerText = checkedCount;
        } else {
            bulkDeleteBtn.classList.add('hidden');
        }
    }

    checkAll.addEventListener('change', function() {
        itemCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkButton();
    });

    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked) checkAll.checked = false;
            const allChecked = Array.from(itemCheckboxes).every(i => i.checked);
            if (allChecked) checkAll.checked = true;
            updateBulkButton();
        });
    });

    bulkDeleteBtn.addEventListener('click', function() {
        let selected = [];
        document.querySelectorAll('.item-checkbox:checked').forEach(cb => selected.push(cb.value));

        if (confirm('Yakin ingin menghapus ' + selected.length + ' data terpilih?')) {
            fetch("{{ route('barang.bulkDelete') }}", {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ ids: selected })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal menghapus data.');
                }
            })
            .catch(err => alert('Terjadi kesalahan pada server.'));
        }
    });

    // Modal Handlers (Lanjutkan fungsi openModalForm, closeModalForm, dll Anda di sini)
</script>
@endsection 