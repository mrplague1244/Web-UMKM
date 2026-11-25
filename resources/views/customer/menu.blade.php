<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Menu Minuman - {{ $store['name'] ?? 'Kedai Kopi & Teh Nusantara' }}</title>
    
    <!-- PWA & Mobile Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#d97706">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased bg-slate-50">

    <!-- TOP HEADER BRAND & MEJA INFO -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-700 to-amber-500 flex items-center justify-center text-white shadow-md text-xl flex-shrink-0">
                    🧋
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-extrabold text-slate-900 leading-tight">
                        {{ $store['name'] ?? 'Kedai Kopi & Teh Nusantara' }}
                    </h1>
                    <p class="text-[10px] text-slate-500 font-medium">
                        {{ $store['tagline'] ?? 'Racikan Kopi & Teh Autentik' }}
                    </p>
                </div>
            </div>

            <!-- Badge Nomor Meja / Dine-in -->
            <div class="text-right">
                @if($tableNo)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-xs animate-bounce">
                        📍 Meja {{ $tableNo }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        🥤 Dine-In & Takeaway
                    </span>
                @endif
            </div>
        </div>

        <!-- SEARCH BAR & CATEGORY STRIP -->
        <div class="max-w-2xl mx-auto px-4 pb-2.5 space-y-2">
            <!-- Search Input -->
            <form action="{{ route('menu') }}" method="GET" class="relative">
                @if($tableNo)
                    <input type="hidden" name="table" value="{{ $tableNo }}">
                @endif
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari menu favorit (kopi, matcha, boba)..."
                       class="w-full pl-8 pr-4 py-1.5 text-xs bg-slate-100 border border-slate-200 rounded-xl focus:bg-white focus:ring-1 focus:ring-amber-500 transition-all font-medium">
                <span class="absolute left-2.5 top-2 text-slate-400 text-xs">🔍</span>
                @if($search)
                    <a href="{{ route('menu', array_filter(['table' => $tableNo, 'category' => $categoryFilter !== 'Semua' ? $categoryFilter : null])) }}"
                       class="absolute right-2.5 top-1.5 text-slate-400 hover:text-slate-700 text-xs font-bold">✕</a>
                @endif
            </form>

            <!-- Horizontal Scrollable Category Filter -->
            <div class="flex items-center space-x-1.5 overflow-x-auto no-scrollbar py-0.5">
                @foreach($allCategories as $cat)
                    <a href="{{ route('menu', array_filter(['table' => $tableNo, 'category' => $cat, 'q' => $search])) }}"
                       class="px-3 py-1 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ ($categoryFilter ?? 'Semua') === $cat ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
        </div>
    </header>

    <!-- MAIN MENU LIST CONTENT -->
    <main class="flex-1 max-w-2xl mx-auto w-full px-4 py-4 pb-28 space-y-4">
        
        <!-- PROMO & KLAIM POIN CALLOUT -->
        <div class="bg-gradient-to-r from-amber-600 to-amber-700 rounded-2xl p-3.5 text-white shadow-sm flex items-center justify-between">
            <div class="space-y-0.5 pr-2">
                <span class="text-[10px] uppercase font-black text-amber-200 tracking-wider">🌟 Program Loyalitas Kedai</span>
                <p class="text-xs font-bold">Kumpulkan 10 Stamp = Gratis 1 Cup Minuman!</p>
                <p class="text-[10px] text-amber-100">Setiap belanja Rp 10.000 mendapatkan 1 stamp digital.</p>
            </div>
            <a href="{{ route('customer.claim') }}" class="px-3 py-1.5 bg-white text-amber-900 rounded-xl text-xs font-black shadow-xs hover:bg-amber-50 transition-all flex-shrink-0">
                Klaim Poin →
            </a>
        </div>

        <!-- JUMLAH MENU DITEMUKAN -->
        <div class="flex justify-between items-center text-xs text-slate-500 px-1">
            <span>Menampilkan <strong>{{ $products->count() }}</strong> pilihan menu</span>
            @if($categoryFilter && $categoryFilter !== 'Semua')
                <span class="text-[11px] bg-slate-200/70 text-slate-700 px-2 py-0.5 rounded-lg font-bold">
                    Kategori: {{ $categoryFilter }}
                </span>
            @endif
        </div>

        <!-- GRID DAFTAR PRODUK MINUMAN -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @forelse($products as $product)
                <div onclick="openVariantModal({{ json_encode($product) }})"
                     class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-xs hover:border-amber-400 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between space-y-2 group">
                    <div class="flex items-start justify-between gap-2">
                        <div class="space-y-1">
                            <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200">
                                {{ $product->category }}
                            </span>
                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-amber-800 transition-colors leading-snug">
                                {{ $product->name }}
                            </h3>
                            @if($product->description)
                                <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ $product->description }}
                                </p>
                            @endif
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition-transform">
                            {{ str_contains(strtolower($product->category), 'coffee') ? '☕' : (str_contains(strtolower($product->category), 'tea') ? '🍵' : '🥤') }}
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-sm font-black text-amber-900">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </span>
                        <span class="text-[11px] font-bold text-amber-700 bg-amber-50 group-hover:bg-amber-600 group-hover:text-white px-2.5 py-1 rounded-xl transition-all flex items-center space-x-1">
                            <span>Varian & Opsi</span>
                            <span>→</span>
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-400 space-y-2">
                    <span class="text-3xl block">🔍</span>
                    <p class="text-xs">Tidak ada menu minuman yang sesuai dengan pencarian Anda.</p>
                    <a href="{{ route('menu') }}" class="inline-block text-xs font-bold text-amber-700 underline">Lihat Semua Menu</a>
                </div>
            @endforelse
        </div>
    </main>

    <!-- BOTTOM FLOATING ACTION BAR -->
    <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2.5 px-4 shadow-lg">
        <div class="max-w-2xl mx-auto flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center space-x-2">
                <span class="text-lg">📢</span>
                <div>
                    <span class="block font-bold text-slate-800 text-[11px]">Cara Memesan:</span>
                    <span class="block text-[10px] text-slate-500">Sebutkan pesanan Anda ke kasir atau kirim via WA.</span>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @if(!empty($store['phone']))
                    @php
                        $cleanPhone = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $store['phone']));
                        $waText = urlencode("Halo {$store['name']}, saya " . ($tableNo ? "di Meja {$tableNo}" : "ingin memesan") . ". Mau tanya rekomendasi menu...");
                    @endphp
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank"
                       class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold flex items-center space-x-1 shadow-xs active:scale-95 transition-all">
                        <span>💬 Chat WA</span>
                    </a>
                @endif
                <button id="btnInstallPwa" style="display: none;" class="px-3 py-1.5 bg-slate-900 text-white rounded-xl font-bold flex items-center space-x-1 shadow-xs">
                    <span>📲 Pasang App</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL & KUSTOMISASI VARIAN MINUMAN -->
    <div id="modalVariant" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[85vh] overflow-y-auto">
            <div class="flex justify-between items-start border-b border-slate-100 pb-2.5">
                <div>
                    <span id="modalProductCategory" class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-amber-50 text-amber-800">Category</span>
                    <h3 id="modalProductName" class="text-base font-extrabold text-slate-900 mt-1">Nama Minuman</h3>
                    <p id="modalProductPrice" class="text-sm font-black text-amber-800 mt-0.5">Rp 0</p>
                </div>
                <button onclick="closeModal('modalVariant')" class="text-slate-400 hover:text-slate-700 font-bold p-1 text-lg">✕</button>
            </div>

            <!-- Pilihan Varian Rasa (Tingkat Gula & Es) -->
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">🍬 Tingkat Kemanisan (Sugar Level):</label>
                    <div class="grid grid-cols-3 gap-1.5 text-center">
                        <div class="p-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-700">Normal (100%)</div>
                        <div class="p-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-700">Less Sugar (50%)</div>
                        <div class="p-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-700">No Sugar (0%)</div>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">🧊 Tingkat Es (Ice Level):</label>
                    <div class="grid grid-cols-3 gap-1.5 text-center">
                        <div class="p-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-700">Normal Ice</div>
                        <div class="p-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-700">Less Ice</div>
                        <div class="p-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-700">No Ice</div>
                    </div>
                </div>

                <!-- Pilihan Topping -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">🧋 Ekstra Topping Tersedia:</label>
                    <div class="space-y-1.5">
                        @foreach(($variants['toppings'] ?? ['Boba Tapioka' => 3000, 'Grass Jelly' => 3000, 'Cheese Foam' => 5000]) as $topName => $topPrice)
                            <div class="p-2 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-between">
                                <span class="font-medium text-slate-800">+ {{ $topName }}</span>
                                <span class="font-bold text-amber-800">+Rp {{ number_format($topPrice, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tombol Pesan Langsung via WhatsApp -->
            <div class="pt-2">
                <a id="btnOrderWa" href="#" target="_blank"
                   class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl shadow-md text-center block text-xs transition-all active:scale-95">
                    💬 Pesan Menu Ini via WhatsApp
                </a>
                <p class="text-[10px] text-center text-slate-400 mt-2">
                    Atau langsung sebutkan pilihan menu ini kepada kasir kami di meja kasir.
                </p>
            </div>
        </div>
    </div>

    <!-- PWA Service Worker Registration & Script -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then((reg) => {
                    console.log('ServiceWorker registered:', reg.scope);
                }).catch((err) => {
                    console.log('ServiceWorker registration failed:', err);
                });
            });
        }

        // PWA Install Prompt Button
        let deferredPrompt;
        const installBtn = document.getElementById('btnInstallPwa');
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (installBtn) installBtn.style.display = 'inline-flex';
        });

        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    deferredPrompt = null;
                    installBtn.style.display = 'none';
                }
            });
        }

        // Modal Logic
        const storePhone = "{{ !empty($store['phone']) ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $store['phone'])) : '' }}";
        const tableNumber = "{{ $tableNo ?? '' }}";
        const storeName = "{{ $store['name'] ?? 'Kedai Minuman' }}";

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function openVariantModal(product) {
            document.getElementById('modalProductName').innerText = product.name;
            document.getElementById('modalProductCategory').innerText = product.category;
            document.getElementById('modalProductPrice').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(product.price);

            // Generate WA order text
            let msg = `Halo ${storeName}, saya ` + (tableNumber ? `di Meja ${tableNumber}` : '') + ` mau pesan: *${product.name}* (Rp ${new Intl.NumberFormat('id-ID').format(product.price)}).`;
            document.getElementById('btnOrderWa').href = `https://wa.me/${storePhone}?text=` + encodeURIComponent(msg);

            openModal('modalVariant');
        }
    </script>
</body>
</html>
