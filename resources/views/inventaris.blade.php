@extends('layouts.app')

@section('content')
<div class="!block !w-full text-white p-2 clear-both min-w-0 overflow-hidden" style="display: block !important; width: 100% !important;">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 w-full">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Inventaris <span class="text-cyan-400">&</span> Stok</h1>
            <p class="text-gray-500 text-sm">Kelola 18.000+ data barang Barokah Toserba</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button id="bulk-delete-btn" class="hidden bg-red-600/20 text-red-500 border border-red-500/50 px-5 py-2.5 rounded-xl hover:bg-red-600 hover:text-white transition font-bold text-sm">
                🗑️ Hapus (<span id="selected-count">0</span>)
            </button>
            <button onclick="openModalForm('tambah')" class="bg-gray-800 border border-gray-700 px-5 py-2.5 rounded-xl hover:bg-gray-700 transition font-medium text-white text-sm">
                + Barang Baru
            </button>
            <a href="{{ route('pos.index') }}" class="bg-cyan-600 px-6 py-2.5 rounded-xl font-bold hover:bg-cyan-500 transition shadow-lg shadow-cyan-900/20 text-white flex items-center gap-2 text-sm">
                ⚡ POS KASIR
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-900/30 border border-green-500/50 text-green-400 p-4 rounded-2xl mb-6 flex items-center shadow-sm w-full">
            <span class="mr-2">✅</span> {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 w-full">
        <form action="{{ route('barang.index') }}" method="GET" class="relative w-full">
            <input type="text" name="search" placeholder="Cari Kode atau Nama Barang..." value="{{ request('search') }}" 
                   class="w-full bg-gray-900 border border-gray-800 p-4 pl-12 rounded-2xl focus:border-cyan-500 outline-none transition text-white">
            <span class="absolute left-4 top-4 text-gray-600">🔍</span>
        </form>
    </div>

    <div class="w-full bg-gray-900 border border-gray-800 rounded-3xl shadow-2xl block overflow-hidden mb-6">
        <div class="w-full overflow-x-auto scrollbar-thin">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead class="bg-gray-800/50 text-cyan-400 text-[11px] uppercase tracking-widest font-bold">
                    <tr>
                        <th class="p-5 border-b border-gray-800 w-16 text-center">
                            <input type="checkbox" id="check-all" class="w-4 h-4 rounded border-gray-700 bg-black text-cyan-500 focus:ring-cyan-600 focus:ring-offset-gray-900">
                        </th>
                        <th class="p-5 border-b border-gray-800">Nama Barang</th>
                        <th class="p-5 border-b border-gray-800 text-center w-40">Status Stok</th>
                        <th class="p-5 border-b border-gray-800 text-right w-44">Harga Jual</th>
                        <th class="p-5 border-b border-gray-800 text-center w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($items as $item)
                    <tr class="hover:bg-gray-800/40 transition group">
                        <td class="p-5 text-center">
                            <input type="checkbox" class="item-checkbox w-4 h-4 rounded border-gray-700 bg-black text-cyan-500 focus:ring-cyan-600 focus:ring-offset-gray-900" value="{{ $item->id }}">
                        </td>
                        <td class="p-5">
                            <div class="font-semibold text-gray-200 block break-words max-w-[300px] sm:max-w-none">{{ $item->nama_barang }}</div>
                            <div class="text-[10px] text-gray-600 font-mono uppercase mt-0.5">{{ $item->kode_barang }} | {{ $item->kategori ?? 'UMUM' }}</div>
                        </td>
                        <td class="p-5 text-center">
                            @if($item->stok <= 0)
                                <span class="bg-red-950/50 text-red-500 px-3 py-1 rounded-full text-[11px] font-bold border border-red-900 block w-max mx-auto">Habis</span>
                            @elseif($item->stok <= 10)
                                <span class="bg-orange-950/50 text-orange-500 px-3 py-1 rounded-full text-[11px] font-bold border border-orange-900 block w-max mx-auto">Sisa {{ $item->stok }}</span>
                            @else
                                <span class="text-gray-400 text-sm font-medium block">{{ $item->stok }} <span class="text-[10px] text-gray-600 ml-1">Unit</span></span>
                            @endif
                        </td>
                        <td class="p-5 text-right font-mono text-green-400 font-bold">
                            <span class="text-[10px] text-gray-600 mr-1">Rp</span>{{ number_format($item->harga1, 0, ',', '.') }}
                        </td>
                        <td class="p-5">
                            <div class="flex justify-center gap-2">
                                <button onclick='openModalForm("edit", @json($item))' 
                                        class="bg-gray-800 text-gray-400 px-3 py-1.5 rounded-lg hover:bg-gray-700 hover:text-white transition text-xs font-medium">
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
    </div>

    <div class="mt-6 w-full block clear-both text-sm pb-12">
        {{ $items->links() }}
    </div>

</div>

<div id="modalForm" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex justify-center items-center z-50 p-4">
    <div class="bg-gray-950 border border-gray-800 w-full max-w-lg rounded-[2rem] overflow-hidden shadow-2xl">
        <header class="p-6 border-b border-gray-800 bg-gray-900/50 flex justify-between items-center">
            <h3 id="modalTitle" class="text-xl font-bold text-white">Tambah Barang Baru</h3>
            <button onclick="closeModalForm()" class="text-gray-500 hover:text-white text-xl font-bold transition">&times;</button>
        </header>
        
        <form id="mainForm" method="POST" onsubmit="disableSubmitButton()">
            @csrf
            <div id="methodContainer"></div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 font-bold uppercase mb-2">Kode Barang</label>
                        <input type="text" id="inputKode" name="kode_barang" required
                               class="w-full bg-gray-900 border border-gray-800 p-3 rounded-xl text-sm focus:border-cyan-500 outline-none text-white transition">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 font-bold uppercase mb-2">Kategori</label>
                        <input type="text" id="inputKategori" name="kategori" placeholder="Makanan, Alat Rumah, dll"
                               class="w-full bg-gray-900 border border-gray-800 p-3 rounded-xl text-sm focus:border-cyan-500 outline-none text-white transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 font-bold uppercase mb-2">Nama Barang</label>
                    <input type="text" id="inputNama" name="nama_barang" required
                           class="w-full bg-gray-900 border border-gray-800 p-3 rounded-xl text-sm focus:border-cyan-500 outline-none text-white transition">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 font-bold uppercase mb-2">Stok Awal</label>
                        <input type="number" id="inputStok" name="stok" min="0" required
                               class="w-full bg-gray-900 border border-gray-800 p-3 rounded-xl text-sm focus:border-cyan-500 outline-none text-white transition">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 font-bold uppercase mb-2">Harga Jual (Rp)</label>
                        <input type="number" id="inputHarga" name="harga1" min="0" required
                               class="w-full bg-gray-900 border border-gray-800 p-3 rounded-xl text-sm focus:border-cyan-500 outline-none text-white transition">
                    </div>
                </div>
            </div>

            <footer class="p-6 border-t border-gray-800 bg-gray-900/50 flex justify-end gap-3">
                <button type="button" onclick="closeModalForm()" class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-gray-400 rounded-xl text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" id="submitBtn" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-cyan-900/30">
                    Simpan Data
                </button>
            </footer>
        </form>
    </div>
</div>

<script>
    // LOGIK CHECKBOX & BULK DELETE
    const checkAll = document.getElementById('check-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const selectedCountLabel = document.getElementById('selected-count');

    function updateBulkButton() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        if (checkedCount > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            selectedCountLabel.innerText = checkedCount;
        } else {
            bulkDeleteBtn.classList.add('hidden');
        }
    }

    if(checkAll) {
        checkAll.addEventListener('change', function() {
            itemCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });
    }

    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked && checkAll) checkAll.checked = false;
            if (Array.from(itemCheckboxes).every(i => i.checked) && checkAll) checkAll.checked = true;
            updateBulkButton();
        });
    });

    bulkDeleteBtn.addEventListener('click', function() {
        let selected = [];
        document.querySelectorAll('.item-checkbox:checked').forEach(cb => selected.push(cb.value));

        if (confirm('Yakin ingin menghapus ' + selected.length + ' data terpilih?')) {
            fetch("{{ route('barang.bulkDelete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ ids: selected })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert('Gagal memproses.');
            })
            .catch(err => alert('Error koneksi database.'));
        }
    });

    // LOGIKA MODAL FORM DINAMIS
    const modalForm = document.getElementById('modalForm');
    const mainForm = document.getElementById('mainForm');
    const modalTitle = document.getElementById('modalTitle');
    const methodContainer = document.getElementById('methodContainer');
    const submitBtn = document.getElementById('submitBtn');

    function openModalForm(mode, data = null) {
        modalForm.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        submitBtn.disabled = false;
        submitBtn.innerText = "Simpan Data";
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');

        if (mode === 'tambah') {
            modalTitle.innerText = "Tambah Barang Baru";
            mainForm.action = "{{ route('barang.store') }}";
            methodContainer.innerHTML = "";
            
            document.getElementById('inputKode').value = "";
            document.getElementById('inputKategori').value = "";
            document.getElementById('inputNama').value = "";
            document.getElementById('inputStok').value = "";
            document.getElementById('inputHarga').value = "";
        } else if (mode === 'edit' && data) {
            modalTitle.innerText = "Edit Informasi Barang";
            mainForm.action = `/barang/${data.id}`; 
            methodContainer.innerHTML = `@method('PUT')`;

            document.getElementById('inputKode').value = data.kode_barang;
            document.getElementById('inputKategori').value = data.kategori ?? "";
            document.getElementById('inputNama').value = data.nama_barang;
            document.getElementById('inputStok').value = data.stok;
            document.getElementById('inputHarga').value = data.harga1;
        }
    }

    function closeModalForm() {
        modalForm.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function disableSubmitButton() {
        submitBtn.disabled = true;
        submitBtn.innerText = "Sedang Memproses...";
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        return true;
    }
</script>
@endsection