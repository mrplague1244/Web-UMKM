@extends('layouts.seller')

@section('title', 'Kasir POS Minuman')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <!-- Kolom Kiri: Katalog Menu Minuman (8 Kolom) -->
    <div class="lg:col-span-7 xl:col-span-8 space-y-4">
        <!-- Kategori Filter Tabs (Horizontal Scrollable on Mobile) -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 sm:pb-2 no-scrollbar -mx-1 px-1">
            <a href="{{ route('seller.pos', ['category' => 'all']) }}"
               class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all shadow-sm flex items-center space-x-1 {{ $selectedCategory === 'all' ? 'bg-amber-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                <span>🍹</span>
                <span>Semua Menu</span>
            </a>
            @foreach($categories as $catKey => $catLabel)
                <a href="{{ route('seller.pos', ['category' => $catKey]) }}"
                   class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all shadow-sm flex items-center space-x-1 {{ $selectedCategory === $catKey ? 'bg-amber-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                    <span>
                        @if($catKey === 'Coffee') ☕
                        @elseif($catKey === 'Non-Coffee') 🥛
                        @elseif($catKey === 'Tea Series') 🍃
                        @else 🧋
                        @endif
                    </span>
                    <span>{{ $catKey }}</span>
                </a>
            @endforeach
        </div>

        <!-- Grid Menu Minuman (2 Kolom di Smartphone, 3 di Desktop) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4">
            @forelse($products as $product)
                <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                    <div>
                        <!-- Badge Kategori & Status Stok -->
                        <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                            <span class="px-1.5 sm:px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-bold tracking-wider uppercase
                                {{ $product->category === 'Coffee' ? 'bg-amber-100 text-amber-800' :
                                   ($product->category === 'Non-Coffee' ? 'bg-indigo-100 text-indigo-800' :
                                   ($product->category === 'Tea Series' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800')) }}">
                                {{ $product->category }}
                            </span>

                            @if($product->recipes->isNotEmpty())
                                <span class="text-[9px] sm:text-[10px] text-slate-400 font-medium hidden sm:inline" title="Resep otomatis memotong bahan baku">
                                    Resep ⚡
                                </span>
                            @endif
                        </div>

                        <!-- Nama & Deskripsi Minuman -->
                        <h3 class="font-bold text-slate-900 text-xs sm:text-base group-hover:text-amber-700 transition-colors line-clamp-2 leading-tight">
                            {{ $product->name }}
                        </h3>
                        <p class="text-[10px] sm:text-xs text-slate-500 mt-1 line-clamp-1 sm:line-clamp-2 hidden sm:block">
                            {{ $product->description ?: 'Minuman segar racikan khas kedai.' }}
                        </p>

                        <!-- Komposisi Bahan Singkat (SCM Recipe Indicator) -->
                        @if($product->recipes->isNotEmpty())
                            <div class="mt-1.5 hidden sm:flex flex-wrap gap-1">
                                @foreach($product->recipes->take(3) as $r)
                                    <span class="inline-block px-1 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px]">
                                        {{ $r->material?->name ?? 'Bahan' }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Harga & Tombol Tambah -->
                    <div class="mt-2.5 sm:mt-4 pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[9px] sm:text-xs text-slate-400 block font-medium">Harga</span>
                            <span class="text-xs sm:text-base font-extrabold text-slate-900 leading-tight">
                                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                            </span>
                        </div>
                        <button type="button"
                                onclick="openVariantModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }})"
                                class="px-2.5 sm:px-3 py-1 sm:py-1.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-sm transition-all flex items-center space-x-1">
                            <span>+ Pesan</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-8 text-center border border-slate-200">
                    <p class="text-slate-500 text-xs sm:text-sm">Belum ada menu minuman pada kategori ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Kolom Kanan: Keranjang Pesanan Kasir (Desktop & Tablet) -->
    <div class="hidden lg:block lg:col-span-5 xl:col-span-4 bg-white rounded-2xl p-5 border border-slate-200 shadow-sm sticky top-20">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center space-x-2">
                <span class="text-xl">🧾</span>
                <h2 class="text-base font-bold text-slate-900">Pesanan Aktif</h2>
            </div>
            <button type="button" onclick="clearCart()" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">
                Reset
            </button>
        </div>

        <form action="{{ route('seller.pos.checkout') }}" method="POST" id="checkoutFormDesktop" onsubmit="return validateCheckout('Desktop')">
            @csrf

            <!-- Container Item Keranjang Desktop -->
            <div id="cartItemsContainerDesktop" class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                <div id="emptyCartMessageDesktop" class="py-12 text-center text-slate-400 text-xs">
                    <span class="text-3xl block mb-2">🥤</span>
                    Belum ada minuman di keranjang.<br>Pilih menu di sisi kiri untuk memesan.
                </div>
            </div>

            <!-- Kalkulasi Biaya -->
            <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Jumlah Cup</span>
                    <span id="totalCupsTextDesktop" class="font-bold text-slate-800">0 cup</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Potensi Poin Member</span>
                    <span id="potentialPointsTextDesktop" class="font-bold text-emerald-600">+0 Poin</span>
                </div>
                <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Tagihan</span>
                    <span id="totalAmountTextDesktop">Rp 0</span>
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
            <div class="mt-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <label class="font-bold text-slate-700">Uang Diterima:</label>
                    <input type="number" id="cashReceivedInputDesktop" placeholder="0"
                           class="w-28 px-2 py-1 text-right font-bold text-slate-900 border border-slate-300 rounded-lg text-xs"
                           oninput="calculateChange('Desktop')">
                </div>
                <div class="flex space-x-1 mt-2">
                    <button type="button" onclick="setCashExact('Desktop')" class="flex-1 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-[10px] font-bold rounded">
                        Uang Pas
                    </button>
                    <button type="button" onclick="setCash(20000, 'Desktop')" class="flex-1 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-[10px] font-bold rounded">
                        20rb
                    </button>
                    <button type="button" onclick="setCash(50000, 'Desktop')" class="flex-1 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-[10px] font-bold rounded">
                        50rb
                    </button>
                    <button type="button" onclick="setCash(100000, 'Desktop')" class="flex-1 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-[10px] font-bold rounded">
                        100rb
                    </button>
                </div>
                <div class="flex justify-between items-center text-xs mt-2 pt-2 border-t border-slate-200">
                    <span class="text-slate-500 font-medium">Kembalian:</span>
                    <span id="cashChangeTextDesktop" class="font-extrabold text-emerald-700">Rp 0</span>
                </div>
            </div>

            <!-- Opsional: Nomor HP Pelanggan -->
            <div class="mt-3">
                <label class="block text-[11px] font-medium text-slate-600 mb-1">
                    No. WhatsApp Member (Opsional):
                </label>
                <input type="text" name="customer_phone" placeholder="08xxxxxxxxxx"
                       class="w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500">
            </div>

            <!-- Tombol Selesaikan Transaksi -->
            <button type="submit" id="btnCheckoutDesktop" disabled
                    class="w-full mt-4 py-3 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98] text-white font-extrabold text-sm rounded-xl shadow-md transition-all flex items-center justify-center space-x-2">
                <span>Bayar & Cetak Struk</span>
                <span>→</span>
            </button>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- FLOATING CART BOTTOM BAR (KHUSUS SMARTPHONE)                    -->
<!-- ============================================================== -->
<div id="mobileFloatingCartBar"
     class="lg:hidden fixed bottom-14 inset-x-3 z-30 hidden bg-gradient-to-r from-amber-800 to-amber-950 text-white rounded-2xl p-3 shadow-2xl border border-amber-600/30 flex items-center justify-between transition-all">
    <div class="flex items-center space-x-2.5">
        <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-lg">
            🥤
        </div>
        <div>
            <span id="floatingCartSummary" class="text-xs font-bold text-amber-200 block">0 Cup</span>
            <span id="floatingCartTotal" class="text-sm font-black text-white">Rp 0</span>
        </div>
    </div>
    <button type="button" onclick="openMobileCartDrawer()"
            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-amber-950 font-black text-xs rounded-xl shadow-md flex items-center space-x-1.5 active:scale-95 transition-all">
        <span>Lihat Pesanan & Bayar</span>
        <span>→</span>
    </button>
</div>

<!-- ============================================================== -->
<!-- MOBILE CART DRAWER (BOTTOM SHEET KHUSUS SMARTPHONE)            -->
<!-- ============================================================== -->
<div id="mobileCartDrawer" class="lg:hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex flex-col justify-end">
    <div class="bg-white rounded-t-3xl max-h-[85vh] flex flex-col p-5 shadow-2xl border-t border-slate-200 animate-in slide-in-from-bottom">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <span class="text-xl">🧾</span>
                <h3 class="font-bold text-slate-900 text-sm">Pesanan Kasir (Mobile)</h3>
            </div>
            <button type="button" onclick="closeMobileCartDrawer()" class="text-slate-400 hover:text-slate-700 font-bold text-lg p-1">✕</button>
        </div>

        <form action="{{ route('seller.pos.checkout') }}" method="POST" id="checkoutFormMobile" onsubmit="return validateCheckout('Mobile')" class="flex flex-col flex-1 overflow-y-auto pt-3">
            @csrf

            <!-- Container Item Keranjang Mobile -->
            <div id="cartItemsContainerMobile" class="space-y-2.5 max-h-[30vh] overflow-y-auto pr-1">
                <!-- Javascript will render items here -->
            </div>

            <!-- Kalkulasi Biaya Mobile -->
            <div class="mt-3 pt-2.5 border-t border-slate-100 space-y-1 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Jumlah Cup</span>
                    <span id="totalCupsTextMobile" class="font-bold text-slate-800">0 cup</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Potensi Poin Member</span>
                    <span id="potentialPointsTextMobile" class="font-bold text-emerald-600">+0 Poin</span>
                </div>
                <div class="flex justify-between text-sm font-extrabold text-slate-900 pt-1.5 border-t border-slate-200">
                    <span>Total Tagihan</span>
                    <span id="totalAmountTextMobile">Rp 0</span>
                </div>
            </div>

            <!-- Metode Pembayaran Mobile -->
            <div class="mt-3">
                <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Metode Pembayaran</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="cash" checked class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 text-[11px] font-bold">
                            💵 Tunai
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="qris" class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 text-[11px] font-bold">
                            📱 QRIS
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="transfer" class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 text-[11px] font-bold">
                            🏦 Transfer
                        </div>
                    </label>
                </div>
            </div>

            <!-- Kalkulator Uang Tunai Mobile -->
            <div class="mt-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <label class="font-bold text-slate-700 text-[11px]">Uang Tunai Diterima:</label>
                    <input type="number" id="cashReceivedInputMobile" placeholder="0"
                           class="w-24 px-2 py-1 text-right font-bold text-slate-900 border border-slate-300 rounded-lg text-xs"
                           oninput="calculateChange('Mobile')">
                </div>
                <div class="flex space-x-1">
                    <button type="button" onclick="setCashExact('Mobile')" class="flex-1 py-1 bg-white border border-slate-300 text-[10px] font-bold rounded">Uang Pas</button>
                    <button type="button" onclick="setCash(20000, 'Mobile')" class="flex-1 py-1 bg-white border border-slate-300 text-[10px] font-bold rounded">20rb</button>
                    <button type="button" onclick="setCash(50000, 'Mobile')" class="flex-1 py-1 bg-white border border-slate-300 text-[10px] font-bold rounded">50rb</button>
                    <button type="button" onclick="setCash(100000, 'Mobile')" class="flex-1 py-1 bg-white border border-slate-300 text-[10px] font-bold rounded">100rb</button>
                </div>
                <div class="flex justify-between items-center text-xs mt-1.5 pt-1.5 border-t border-slate-200">
                    <span class="text-slate-500 font-medium text-[11px]">Kembalian:</span>
                    <span id="cashChangeTextMobile" class="font-extrabold text-emerald-700 text-xs">Rp 0</span>
                </div>
            </div>

            <!-- Opsional No WhatsApp -->
            <div class="mt-2">
                <input type="text" name="customer_phone" placeholder="No. WhatsApp Member (Opsional)"
                       class="w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs">
            </div>

            <button type="submit" id="btnCheckoutMobile"
                    class="w-full mt-3 py-3 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-black text-xs rounded-xl shadow-md">
                Selesaikan Pembayaran & Cetak Struk →
            </button>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL PILIH VARIAN MINUMAN (MOBILE BOTTOM SHEET & DESKTOP)     -->
<!-- ============================================================== -->
<div id="variantModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-3.5 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <div>
                <h3 id="modalProductName" class="text-sm sm:text-base font-bold text-slate-900">Nama Minuman</h3>
                <p id="modalProductPrice" class="text-xs font-semibold text-amber-700">Rp 0</p>
            </div>
            <button type="button" onclick="closeVariantModal()" class="text-slate-400 hover:text-slate-700 font-bold text-lg p-1">✕</button>
        </div>

        <input type="hidden" id="modalProductId">
        <input type="hidden" id="modalBasePrice">

        <!-- Opsi Sugar Level -->
        <div>
            <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Tingkat Manis (Sugar Level)</label>
            <div class="grid grid-cols-3 gap-1.5">
                @foreach($variants['sugar_levels'] ?? [] as $sKey => $sLabel)
                    <label class="cursor-pointer">
                        <input type="radio" name="modal_sugar" value="{{ $sKey }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-900 text-[11px] font-bold transition-all">
                            {{ $sKey }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Opsi Ice Level -->
        <div>
            <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Tingkat Dingin (Ice Level)</label>
            <div class="grid grid-cols-3 gap-1.5">
                @foreach($variants['ice_levels'] ?? [] as $iKey => $iLabel)
                    <label class="cursor-pointer">
                        <input type="radio" name="modal_ice" value="{{ $iKey }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-900 text-[11px] font-bold transition-all">
                            {{ $iKey }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Opsi Topping -->
        <div>
            <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Ekstra Topping (Opsional)</label>
            <div class="grid grid-cols-2 gap-1.5">
                <label class="cursor-pointer">
                    <input type="radio" name="modal_topping" value="" checked class="peer sr-only">
                    <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-900 text-[11px] font-medium">
                        Tanpa Topping
                    </div>
                </label>
                @foreach($variants['toppings'] ?? [] as $tName => $tPrice)
                    <label class="cursor-pointer">
                        <input type="radio" name="modal_topping" value="{{ $tName }}" data-price="{{ $tPrice }}" class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-900 text-[11px] font-medium">
                            {{ $tName }} (+{{ number_format($tPrice, 0, ',', '.') }})
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Catatan Tambahan -->
        <div>
            <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Khusus</label>
            <input type="text" id="modalNotes" placeholder="Contoh: Pisah es, jangan terlalu manis"
                   class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-amber-500">
        </div>

        <!-- Tombol Masukkan Keranjang -->
        <div class="pt-2 flex space-x-2">
            <button type="button" onclick="closeVariantModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                Batal
            </button>
            <button type="button" onclick="confirmAddToCart()" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md">
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

        document.querySelector('input[name="modal_sugar"]:first-of-type').checked = true;
        document.querySelector('input[name="modal_ice"]:first-of-type').checked = true;
        document.querySelector('input[name="modal_topping"][value=""]').checked = true;

        document.getElementById('variantModal').classList.remove('hidden');
    }

    function closeVariantModal() {
        document.getElementById('variantModal').classList.add('hidden');
    }

    function openMobileCartDrawer() {
        document.getElementById('mobileCartDrawer').classList.remove('hidden');
    }

    function closeMobileCartDrawer() {
        document.getElementById('mobileCartDrawer').classList.add('hidden');
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
        closeMobileCartDrawer();
    }

    function renderCart() {
        renderCartTarget('Desktop');
        renderCartTarget('Mobile');

        // Floating Cart Bar di Layar HP
        const floatingBar = document.getElementById('mobileFloatingCartBar');
        if (cart.length > 0) {
            floatingBar.classList.remove('hidden');
            let totalCups = cart.reduce((acc, it) => acc + it.quantity, 0);
            let totalAmount = cart.reduce((acc, it) => acc + (it.unitPrice * it.quantity), 0);

            document.getElementById('floatingCartSummary').innerText = `${totalCups} Cup Minuman`;
            document.getElementById('floatingCartTotal').innerText = 'Rp ' + Number(totalAmount).toLocaleString('id-ID');
        } else {
            floatingBar.classList.add('hidden');
            closeMobileCartDrawer();
        }
    }

    function renderCartTarget(target) {
        const container = document.getElementById('cartItemsContainer' + target);
        const emptyMsg = document.getElementById('emptyCartMessage' + target);
        const btnCheckout = document.getElementById('btnCheckout' + target);

        if (!container) return;

        container.innerHTML = '';

        if (cart.length === 0) {
            if (emptyMsg) {
                container.appendChild(emptyMsg);
                emptyMsg.classList.remove('hidden');
            }
            if (btnCheckout) btnCheckout.disabled = true;
            document.getElementById('totalCupsText' + target).innerText = '0 cup';
            document.getElementById('totalAmountText' + target).innerText = 'Rp 0';
            document.getElementById('potentialPointsText' + target).innerText = '+0 Poin';
            calculateChange(target);
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

                <input type="hidden" name="items[${index}][product_id]" value="${item.productId}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                <input type="hidden" name="items[${index}][ice_level]" value="${item.ice}">
                <input type="hidden" name="items[${index}][sugar_level]" value="${item.sugar}">
                <input type="hidden" name="items[${index}][topping]" value="${item.topping}">
                <input type="hidden" name="items[${index}][notes]" value="${item.notes}">
            `;
            container.appendChild(div);
        });

        document.getElementById('totalCupsText' + target).innerText = totalCups + ' cup';
        document.getElementById('totalAmountText' + target).innerText = 'Rp ' + Number(totalAmount).toLocaleString('id-ID');

        const points = Math.floor(totalAmount / 10000);
        document.getElementById('potentialPointsText' + target).innerText = '+' + points + ' Poin';

        if (btnCheckout) btnCheckout.disabled = false;
        calculateChange(target);
    }

    function calculateChange(target) {
        const totalText = document.getElementById('totalAmountText' + target).innerText.replace(/[^0-9]/g, '');
        const total = Number(totalText) || 0;
        const cashInput = document.getElementById('cashReceivedInput' + target);
        const cashReceived = Number(cashInput?.value || 0);
        const change = cashReceived - total;

        const changeEl = document.getElementById('cashChangeText' + target);
        if (!changeEl) return;

        if (change >= 0) {
            changeEl.innerText = 'Rp ' + Number(change).toLocaleString('id-ID');
            changeEl.className = 'font-extrabold text-emerald-700 text-xs';
        } else {
            changeEl.innerText = 'Kurang Rp ' + Number(Math.abs(change)).toLocaleString('id-ID');
            changeEl.className = 'font-extrabold text-rose-600 text-xs';
        }
    }

    function setCash(amount, target) {
        const input = document.getElementById('cashReceivedInput' + target);
        if (input) input.value = amount;
        calculateChange(target);
    }

    function setCashExact(target) {
        const totalText = document.getElementById('totalAmountText' + target).innerText.replace(/[^0-9]/g, '');
        const input = document.getElementById('cashReceivedInput' + target);
        if (input) input.value = Number(totalText) || 0;
        calculateChange(target);
    }

    function validateCheckout(target) {
        if (cart.length === 0) {
            alert('Silakan pilih minimal 1 menu minuman terlebih dahulu!');
            return false;
        }
        return true;
    }
</script>
@endpush
@endsection
