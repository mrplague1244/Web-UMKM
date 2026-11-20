@extends('layouts.seller')

@section('title', 'Kasir POS Minuman')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <!-- Kolom Kiri: Katalog Menu Minuman (8 Kolom) -->
    <div class="lg:col-span-7 xl:col-span-8 space-y-4">
        <!-- Kategori Filter Tabs -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-2 scrollbar-none">
            <a href="{{ route('seller.pos', ['category' => 'all']) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition-all shadow-sm {{ $selectedCategory === 'all' ? 'bg-amber-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                🍹 Semua Menu
            </a>
            @foreach($categories as $catKey => $catLabel)
                <a href="{{ route('seller.pos', ['category' => $catKey]) }}"
                   class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition-all shadow-sm {{ $selectedCategory === $catKey ? 'bg-amber-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                    @if($catKey === 'Coffee') ☕
                    @elseif($catKey === 'Non-Coffee') 🥛
                    @elseif($catKey === 'Tea Series') 🍃
                    @else 🧋
                    @endif
                    {{ $catKey }}
                </a>
            @endforeach
        </div>

        <!-- Grid Menu Minuman -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($products as $product)
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                    <div>
                        <!-- Badge Kategori & Status Stok -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase
                                {{ $product->category === 'Coffee' ? 'bg-amber-100 text-amber-800' :
                                   ($product->category === 'Non-Coffee' ? 'bg-indigo-100 text-indigo-800' :
                                   ($product->category === 'Tea Series' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800')) }}">
                                {{ $product->category }}
                            </span>

                            @if($product->recipes->isNotEmpty())
                                <span class="text-[10px] text-slate-400 font-medium" title="Resep otomatis memotong bahan baku">
                                    Resep Aktif ⚡
                                </span>
                            @endif
                        </div>

                        <!-- Nama & Deskripsi Minuman -->
                        <h3 class="font-bold text-slate-900 text-base group-hover:text-amber-700 transition-colors">
                            {{ $product->name }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                            {{ $product->description ?: 'Minuman segar racikan khas kedai.' }}
                        </p>

                        <!-- Komposisi Bahan Singkat (SCM Recipe Indicator) -->
                        @if($product->recipes->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach($product->recipes as $r)
                                    <span class="inline-block px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px]">
                                        {{ $r->material?->name ?? 'Bahan' }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Harga & Tombol Tambah -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Harga</span>
                            <span class="text-base font-extrabold text-slate-900">
                                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                            </span>
                        </div>
                        <button type="button"
                                onclick="openVariantModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }})"
                                class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center space-x-1">
                            <span>+ Pesan</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-8 text-center border border-slate-200">
                    <p class="text-slate-500 text-sm">Belum ada menu minuman pada kategori ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Kolom Kanan: Keranjang Pesanan Kasir (4 Kolom) -->
    <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-2xl p-5 border border-slate-200 shadow-sm sticky top-20">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center space-x-2">
                <span class="text-xl">🧾</span>
                <h2 class="text-base font-bold text-slate-900">Pesanan Aktif</h2>
            </div>
            <button type="button" onclick="clearCart()" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">
                Reset
            </button>
        </div>

        <form action="{{ route('seller.pos.checkout') }}" method="POST" id="checkoutForm" onsubmit="return validateCheckout()">
            @csrf

            <!-- Container Item Keranjang -->
            <div id="cartItemsContainer" class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                <!-- Javascript will render items here -->
                <div id="emptyCartMessage" class="py-12 text-center text-slate-400 text-xs">
                    <span class="text-3xl block mb-2">🥤</span>
                    Belum ada minuman di keranjang.<br>Pilih menu di sisi kiri untuk memesan.
                </div>
            </div>

            <!-- Kalkulasi Biaya -->
            <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Jumlah Cup</span>
                    <span id="totalCupsText" class="font-bold text-slate-800">0 cup</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Potensi Poin Member</span>
                    <span id="potentialPointsText" class="font-bold text-emerald-600">+0 Poin</span>
                </div>
                <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Tagihan</span>
                    <span id="totalAmountText">Rp 0</span>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="mt-4 pt-3 border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-700 mb-2">Metode Pembayaran</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="cash" checked class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 text-xs font-bold transition-all">
                            💵 Tunai
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="qris" class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 text-xs font-bold transition-all">
                            📱 QRIS
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="transfer" class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 text-xs font-bold transition-all">
                            🏦 Transfer
                        </div>
                    </label>
                </div>
            </div>

            <!-- Kalkulator Uang Tunai & Kembalian -->
            <div id="cashCalculatorSection" class="mt-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <label class="font-bold text-slate-700">Uang Diterima:</label>
                    <input type="number" id="cashReceivedInput" placeholder="0"
                           class="w-28 px-2 py-1 text-right font-bold text-slate-900 border border-slate-300 rounded-lg text-xs"
                           oninput="calculateChange()">
                </div>
                <!-- Tombol Cepat Uang Pas / Pecahan -->
                <div class="flex space-x-1 mt-2">
                    <button type="button" onclick="setCashExact()" class="flex-1 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-[10px] font-bold rounded">
                        Uang Pas
                    </button>
                    <button type="button" onclick="setCash(20000)" class="flex-1 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-[10px] font-bold rounded">
                        20rb
                    </button>
                    <button type="button" onclick="setCash(50000)" class="flex-1 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-[10px] font-bold rounded">
                        50rb
                    </button>
                    <button type="button" onclick="setCash(100000)" class="flex-1 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-[10px] font-bold rounded">
                        100rb
                    </button>
                </div>
                <div class="flex justify-between items-center text-xs mt-2 pt-2 border-t border-slate-200">
                    <span class="text-slate-500 font-medium">Kembalian:</span>
                    <span id="cashChangeText" class="font-extrabold text-emerald-700">Rp 0</span>
                </div>
            </div>

            <!-- Opsional: Nomor HP Pelanggan (CRM Direct Link) -->
            <div class="mt-3">
                <label class="block text-[11px] font-medium text-slate-600 mb-1">
                    No. WhatsApp Member (Opsional):
                </label>
                <input type="text" name="customer_phone" placeholder="08xxxxxxxxxx"
                       class="w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500">
            </div>

            <!-- Tombol Selesaikan Transaksi -->
            <button type="submit" id="btnCheckout" disabled
                    class="w-full mt-4 py-3 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98] text-white font-extrabold text-sm rounded-xl shadow-md transition-all flex items-center justify-center space-x-2">
                <span>Bayar & Cetak Struk</span>
                <span>→</span>
            </button>
        </form>
    </div>
</div>

<!-- MODAL PILIH VARIAN MINUMAN (Sugar, Ice, Topping) -->
<div id="variantModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <div>
                <h3 id="modalProductName" class="text-base font-bold text-slate-900">Nama Minuman</h3>
                <p id="modalProductPrice" class="text-xs font-semibold text-amber-700">Rp 0</p>
            </div>
            <button type="button" onclick="closeVariantModal()" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
        </div>

        <input type="hidden" id="modalProductId">
        <input type="hidden" id="modalBasePrice">

        <!-- Opsi Sugar Level -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Tingkat Manis (Sugar Level)</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach($variants['sugar_levels'] ?? [] as $sKey => $sLabel)
                    <label class="cursor-pointer">
                        <input type="radio" name="modal_sugar" value="{{ $sKey }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-900 text-xs font-medium">
                            {{ $sKey }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Opsi Ice Level -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Tingkat Dingin (Ice Level)</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach($variants['ice_levels'] ?? [] as $iKey => $iLabel)
                    <label class="cursor-pointer">
                        <input type="radio" name="modal_ice" value="{{ $iKey }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-900 text-xs font-medium">
                            {{ $iKey }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Opsi Topping -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Ekstra Topping (Opsional)</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="modal_topping" value="" checked class="peer sr-only">
                    <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-900 text-xs font-medium">
                        Tanpa Topping
                    </div>
                </label>
                @foreach($variants['toppings'] ?? [] as $tName => $tPrice)
                    <label class="cursor-pointer">
                        <input type="radio" name="modal_topping" value="{{ $tName }}" data-price="{{ $tPrice }}" class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-900 text-xs font-medium">
                            {{ $tName }} (+{{ number_format($tPrice, 0, ',', '.') }})
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Catatan Tambahan -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Khusus</label>
            <input type="text" id="modalNotes" placeholder="Contoh: Pisah es, jangan terlalu manis"
                   class="w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-amber-500">
        </div>

        <!-- Tombol Masukkan Keranjang -->
        <div class="pt-2 flex space-x-2">
            <button type="button" onclick="closeVariantModal()" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                Batal
            </button>
            <button type="button" onclick="confirmAddToCart()" class="flex-1 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md">
                + Tambah ke Pesanan
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let cart = [];
    let currentModalProduct = null;

    function openVariantModal(productId, productName, productPrice) {
        currentModalProduct = { id: productId, name: productName, price: productPrice };
        document.getElementById('modalProductId').value = productId;
        document.getElementById('modalBasePrice').value = productPrice;
        document.getElementById('modalProductName').innerText = productName;
        document.getElementById('modalProductPrice').innerText = 'Rp ' + Number(productPrice).toLocaleString('id-ID');
        document.getElementById('modalNotes').value = '';

        // Reset radio buttons to default
        document.querySelector('input[name="modal_sugar"]:first-of-type').checked = true;
        document.querySelector('input[name="modal_ice"]:first-of-type').checked = true;
        document.querySelector('input[name="modal_topping"][value=""]').checked = true;

        document.getElementById('variantModal').classList.remove('hidden');
    }

    function closeVariantModal() {
        document.getElementById('variantModal').classList.add('hidden');
    }

    function confirmAddToCart() {
        const productId = document.getElementById('modalProductId').value;
        const productName = document.getElementById('modalProductName').innerText;
        const basePrice = Number(document.getElementById('modalBasePrice').value);

        const sugar = document.querySelector('input[name="modal_sugar"]:checked')?.value || 'Normal (100%)';
        const ice = document.querySelector('input[name="modal_ice"]:checked')?.value || 'Normal Ice';
        const toppingRadio = document.querySelector('input[name="modal_topping"]:checked');
        const topping = toppingRadio?.value || '';
        const toppingPrice = Number(toppingRadio?.dataset?.price || 0);
        const notes = document.getElementById('modalNotes').value.trim();

        const unitPrice = basePrice + toppingPrice;

        // Cek jika item dengan varian yang sama sudah ada di keranjang
        const existingIndex = cart.findIndex(item =>
            item.productId == productId &&
            item.sugar === sugar &&
            item.ice === ice &&
            item.topping === topping &&
            item.notes === notes
        );

        if (existingIndex > -1) {
            cart[existingIndex].quantity += 1;
        } else {
            cart.push({
                productId: productId,
                name: productName,
                unitPrice: unitPrice,
                quantity: 1,
                sugar: sugar,
                ice: ice,
                topping: topping,
                notes: notes
            });
        }

        closeVariantModal();
        renderCart();
    }

    function changeQty(index, delta) {
        if (!cart[index]) return;
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItemsContainer');
        const emptyMsg = document.getElementById('emptyCartMessage');
        const btnCheckout = document.getElementById('btnCheckout');

        // Hapus elemen item lama kecuali empty message
        container.innerHTML = '';

        if (cart.length === 0) {
            container.appendChild(emptyMsg);
            emptyMsg.classList.remove('hidden');
            btnCheckout.disabled = true;
            document.getElementById('totalCupsText').innerText = '0 cup';
            document.getElementById('totalAmountText').innerText = 'Rp 0';
            document.getElementById('potentialPointsText').innerText = '+0 Poin';
            calculateChange();
            return;
        }

        let totalAmount = 0;
        let totalCups = 0;

        cart.forEach((item, index) => {
            const subtotal = item.unitPrice * item.quantity;
            totalAmount += subtotal;
            totalCups += item.quantity;

            const div = document.createElement('div');
            div.className = 'p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1.5';
            div.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <strong class="text-slate-900 font-bold block">${item.name}</strong>
                        <span class="text-[11px] text-slate-500">Rp ${Number(item.unitPrice).toLocaleString('id-ID')}</span>
                    </div>
                    <span class="font-extrabold text-slate-900">Rp ${Number(subtotal).toLocaleString('id-ID')}</span>
                </div>
                <div class="text-[10px] text-slate-500 flex flex-wrap gap-1">
                    <span class="bg-amber-100 text-amber-800 px-1 rounded">${item.ice}</span>
                    <span class="bg-blue-100 text-blue-800 px-1 rounded">${item.sugar}</span>
                    ${item.topping ? `<span class="bg-purple-100 text-purple-800 px-1 rounded">+${item.topping}</span>` : ''}
                    ${item.notes ? `<span class="italic text-slate-400">"${item.notes}"</span>` : ''}
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-slate-200">
                    <div class="flex items-center space-x-1.5">
                        <button type="button" onclick="changeQty(${index}, -1)" class="w-6 h-6 bg-white border border-slate-300 rounded font-bold hover:bg-slate-100 flex items-center justify-center">-</button>
                        <span class="font-bold text-xs px-1">${item.quantity}</span>
                        <button type="button" onclick="changeQty(${index}, 1)" class="w-6 h-6 bg-white border border-slate-300 rounded font-bold hover:bg-slate-100 flex items-center justify-center">+</button>
                    </div>
                    <button type="button" onclick="changeQty(${index}, -999)" class="text-rose-500 hover:text-rose-700 text-[11px] font-medium">Hapus</button>
                </div>

                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="items[${index}][product_id]" value="${item.productId}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                <input type="hidden" name="items[${index}][ice_level]" value="${item.ice}">
                <input type="hidden" name="items[${index}][sugar_level]" value="${item.sugar}">
                <input type="hidden" name="items[${index}][topping]" value="${item.topping}">
                <input type="hidden" name="items[${index}][notes]" value="${item.notes}">
            `;
            container.appendChild(div);
        });

        document.getElementById('totalCupsText').innerText = totalCups + ' cup';
        document.getElementById('totalAmountText').innerText = 'Rp ' + Number(totalAmount).toLocaleString('id-ID');

        const points = Math.floor(totalAmount / 10000);
        document.getElementById('potentialPointsText').innerText = '+' + points + ' Poin';

        btnCheckout.disabled = false;
        calculateChange();
    }

    function calculateChange() {
        const totalText = document.getElementById('totalAmountText').innerText.replace(/[^0-9]/g, '');
        const total = Number(totalText) || 0;
        const cashReceived = Number(document.getElementById('cashReceivedInput').value) || 0;
        const change = cashReceived - total;

        const changeEl = document.getElementById('cashChangeText');
        if (change >= 0) {
            changeEl.innerText = 'Rp ' + Number(change).toLocaleString('id-ID');
            changeEl.className = 'font-extrabold text-emerald-700';
        } else {
            changeEl.innerText = 'Kurang Rp ' + Number(Math.abs(change)).toLocaleString('id-ID');
            changeEl.className = 'font-extrabold text-rose-600';
        }
    }

    function setCash(amount) {
        document.getElementById('cashReceivedInput').value = amount;
        calculateChange();
    }

    function setCashExact() {
        const totalText = document.getElementById('totalAmountText').innerText.replace(/[^0-9]/g, '');
        document.getElementById('cashReceivedInput').value = Number(totalText) || 0;
        calculateChange();
    }

    function validateCheckout() {
        if (cart.length === 0) {
            alert('Silakan pilih minimal 1 menu minuman terlebih dahulu!');
            return false;
        }
        return true;
    }
</script>
@endpush
@endsection
