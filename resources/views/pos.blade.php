@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-120px)] flex flex-col md:flex-row gap-6">
    
    <!-- BAGIAN KIRI: DAFTAR PRODUK -->
    <section class="flex-1 flex flex-col bg-gray-900 border border-gray-800 rounded-[2.5rem] overflow-hidden shadow-2xl">
        <header class="p-6 border-b border-gray-800 bg-gray-800/30 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Barokah <span class="text-cyan-400">POS</span></h2>
                <p class="text-xs text-gray-500 uppercase tracking-widest">Inventory Real-time</p>
            </div>
            <div class="relative w-64">
                <input type="text" id="searchProduct" onkeyup="filterProducts()" placeholder="Cari barang..." 
                       class="w-full bg-black border border-gray-700 p-3 pl-10 rounded-xl text-sm focus:border-cyan-500 outline-none text-white transition">
                <span class="absolute left-3 top-3 text-gray-600">🔍</span>
            </div>
        </header>

        <!-- Grid Produk -->
        <div class="flex-1 overflow-y-auto p-6 scrollbar-hide">
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="productGrid">
                @foreach($items as $item)
                <div class="product-card group bg-black/40 border border-gray-800 p-4 rounded-3xl hover:border-cyan-500/50 transition cursor-pointer relative overflow-hidden"
                     onclick="addToCart({{ $item->id }}, '{{ $item->nama_barang }}', {{ $item->harga1 }}, {{ $item->stok }})">
                    <div class="absolute top-0 right-0 p-2">
                        <span class="bg-gray-800 text-[10px] text-gray-400 px-2 py-1 rounded-bl-xl border-l border-b border-gray-700">{{ $item->kode_barang }}</span>
                    </div>
                    <div class="mb-3 mt-2">
                        <h4 class="text-gray-200 font-bold leading-tight group-hover:text-cyan-400 transition">{{ $item->nama_barang }}</h4>
                        <p class="text-[10px] text-gray-600 mt-1 uppercase">{{ $item->kategori }}</p>
                    </div>
                    <div class="flex justify-between items-end">
                        <span class="text-green-400 font-mono font-bold">Rp{{ number_format($item->harga1, 0, ',', '.') }}</span>
                        <span class="text-[10px] {{ $item->stok < 10 ? 'text-red-500' : 'text-gray-600' }}">Stok: {{ $item->stok }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- BAGIAN KANAN: KERANJANG -->
    <section class="w-full md:w-[400px] flex flex-col bg-gray-900 border border-gray-800 rounded-[2.5rem] overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-gray-800 bg-gray-800/30">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span>🛒</span> Keranjang Belanja
            </h3>
        </div>

        <!-- Daftar Item Belanja -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3" id="cartItems">
            <!-- Item muncul via JS -->
            <div class="text-center py-10 text-gray-600 italic text-sm">Belum ada barang dipilih</div>
        </div>

        <!-- Ringkasan & Pembayaran -->
        <div class="p-6 bg-black/60 border-t border-gray-800 space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-gray-400 uppercase text-xs font-bold tracking-widest">Total Tagihan</span>
                <span id="totalPrice" class="text-3xl font-mono font-bold text-cyan-400">Rp 0</span>
            </div>
            
            <div class="space-y-2">
                <label class="text-[10px] text-gray-500 font-bold uppercase">Uang Dibayar</label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-gray-500 font-bold">Rp</span>
                    <input type="number" id="cashInput" oninput="calculateChange()" placeholder="0" 
                           class="w-full bg-gray-800 border border-gray-700 p-3 pl-12 rounded-2xl text-xl font-mono text-white outline-none focus:border-green-500">
                </div>
            </div>

            <div class="flex justify-between items-center py-2 border-y border-gray-800/50">
                <span class="text-gray-400 text-sm">Kembali</span>
                <span id="changeAmount" class="text-xl font-mono font-bold text-orange-400">Rp 0</span>
            </div>

            <button onclick="processCheckout()" 
                    class="w-full bg-cyan-600 hover:bg-cyan-500 text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-cyan-900/40 transition flex justify-center items-center gap-2">
                <span>🖨️</span> PROSES & CETAK
            </button>
        </div>
    </section>
</div>

<!-- Modal Struk (Hidden by default) -->
<div id="receipt" class="hidden fixed inset-0 bg-white p-8 text-black font-mono text-sm max-w-[300px] mx-auto overflow-y-auto z-[100]"></div>

<script>
let cart = [];
let total = 0;

function addToCart(id, name, price, stock) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        if (existing.qty < stock) {
            existing.qty++;
        } else {
            alert('Stok tidak mencukupi!');
            return;
        }
    } else {
        cart.push({ id, name, price, qty: 1 });
    }
    renderCart();
}

function renderCart() {
    const cartContainer = document.getElementById('cartItems');
    total = 0;
    
    if (cart.length === 0) {
        cartContainer.innerHTML = '<div class="text-center py-10 text-gray-600 italic text-sm">Belum ada barang dipilih</div>';
        document.getElementById('totalPrice').innerText = "Rp 0";
        return;
    }

    cartContainer.innerHTML = cart.map((item, index) => {
        total += item.price * item.qty;
        return `
            <div class="bg-gray-800/40 p-4 rounded-2xl border border-gray-700 flex justify-between items-center group">
                <div>
                    <h5 class="text-white font-bold text-sm">${item.name}</h5>
                    <p class="text-xs text-gray-500">Rp${new Intl.NumberFormat('id-ID').format(item.price)} x ${item.qty}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="changeQty(${index}, -1)" class="w-6 h-6 bg-gray-700 rounded-full text-white text-xs hover:bg-red-900 transition">-</button>
                    <span class="text-cyan-400 font-bold">${item.qty}</span>
                    <button onclick="changeQty(${index}, 1)" class="w-6 h-6 bg-gray-700 rounded-full text-white text-xs hover:bg-green-900 transition">+</button>
                </div>
            </div>
        `;
    }).join('');

    document.getElementById('totalPrice').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(total);
    calculateChange();
}

function changeQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty <= 0) cart.splice(index, 1);
    renderCart();
}

function calculateChange() {
    const cash = document.getElementById('cashInput').value || 0;
    const change = cash - total;
    document.getElementById('changeAmount').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(Math.max(0, change));
}

function filterProducts() {
    let input = document.getElementById('searchProduct').value.toLowerCase();
    let cards = document.getElementsByClassName('product-card');
    
    Array.from(cards).forEach(card => {
        let name = card.querySelector('h4').innerText.toLowerCase();
        card.style.display = name.includes(input) ? "block" : "none";
    });
}

function processCheckout() {
    if (cart.length === 0) return alert('Keranjang kosong!');
    const cash = document.getElementById('cashInput').value;
    if (cash < total) return alert('Uang tidak cukup!');

    // Sederhana: Cetak Struk
    let receiptHtml = `
        <div style="text-align:center">
            <h3>BAROKAH TOSERBA</h3>
            <p>Struk Belanja</p>
            <hr>
        </div>
        ${cart.map(i => `<p>${i.name} <br> ${i.qty} x ${i.price} = ${i.qty * i.price}</p>`).join('')}
        <hr>
        <p>TOTAL: Rp${new Intl.NumberFormat('id-ID').format(total)}</p>
        <p>BAYAR: Rp${new Intl.NumberFormat('id-ID').format(cash)}</p>
        <p>KEMBALI: Rp${new Intl.NumberFormat('id-ID').format(cash-total)}</p>
        <hr>
        <p style="text-align:center">Terima Kasih!</p>
    `;
    
    const printWindow = window.open('', '', 'width=300,height=600');
    printWindow.document.write('<html><body>' + receiptHtml + '</body></html>');
    printWindow.document.close();
    printWindow.print();
    
    // Reset
    cart = [];
    document.getElementById('cashInput').value = '';
    renderCart();
}
</script>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection