<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Sistem UMKM Minuman') - {{ config('beverage.store.name', 'Kedai Kopi & Teh') }}</title>
    <!-- PWA & Mobile Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#d97706">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fdf8f6',
                            100: '#f2e8e5',
                            200: '#eaddd7',
                            500: '#8b5e3c', // Warm coffee brown
                            600: '#6f4528',
                            700: '#54331c',
                            800: '#432814',
                            900: '#2b180a',
                        },
                        tea: {
                            500: '#2d6a4f', // Fresh matcha green
                            600: '#1b4332',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Mobile Touch & Print Optimizations */
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-amber-200">
    <!-- Top Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-14 sm:h-16">
            <!-- Brand & Store Name -->
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-amber-600 flex items-center justify-center text-white shadow-md flex-shrink-0">
                    <span class="text-lg sm:text-xl">🧋</span>
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 leading-tight truncate max-w-[190px] sm:max-w-none">
                        {{ config('beverage.store.name', 'Kopi & Teh Nusantara') }}
                    </h1>
                    <p class="text-[10px] sm:text-xs text-slate-500 font-medium hidden sm:block">
                        {{ config('beverage.store.tagline', 'ERP - SCM - CRM Kedai Minuman') }}
                    </p>
                </div>
            </div>

            <!-- Desktop & Tablet Navigation Links (Hidden on Mobile) -->
            <nav class="hidden sm:flex items-center space-x-1 sm:space-x-2">
                <a href="{{ route('seller.pos') }}"
                   class="px-3 py-2 rounded-lg text-sm font-semibold transition-all flex items-center space-x-1.5 {{ request()->routeIs('seller.pos*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>🛒</span>
                    <span>Kasir POS</span>
                </a>

                <a href="{{ route('seller.scm') }}"
                   class="px-3 py-2 rounded-lg text-sm font-semibold transition-all flex items-center space-x-1.5 {{ request()->routeIs('seller.scm*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>📦</span>
                    <span>SCM & Bahan</span>
                </a>

                <a href="{{ route('seller.erp') }}"
                   class="px-3 py-2 rounded-lg text-sm font-semibold transition-all flex items-center space-x-1.5 {{ request()->routeIs('seller.erp*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>💰</span>
                    <span>ERP Keuangan</span>
                </a>

                <a href="{{ route('seller.analytics') }}"
                   class="px-3 py-2 rounded-lg text-sm font-semibold transition-all flex items-center space-x-1.5 {{ request()->routeIs('seller.analytics*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>📊</span>
                    <span>Analisis</span>
                </a>

                <a href="{{ route('seller.crm') }}"
                   class="px-3 py-2 rounded-lg text-sm font-semibold transition-all flex items-center space-x-1.5 {{ request()->routeIs('seller.crm*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>👥</span>
                    <span>CRM Loyalitas</span>
                </a>

                <a href="{{ route('menu') }}" target="_blank"
                   class="inline-flex px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 hover:bg-amber-100 text-amber-900 items-center space-x-1 transition-all border border-amber-200" title="Buka Menu Digital Pelanggan">
                    <span>📖</span>
                    <span>Menu ↗</span>
                </a>

                <a href="{{ route('seller.table_qr') }}" target="_blank"
                   class="inline-flex px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 items-center space-x-1 transition-all border border-slate-300" title="Cetak Kartu QR Meja Dine-In">
                    <span>🪧</span>
                    <span>QR Meja ↗</span>
                </a>

                <a href="{{ route('customer.claim') }}" target="_blank"
                   class="inline-flex px-3 py-2 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 items-center space-x-1 transition-all border border-slate-300">
                    <span>📱</span>
                    <span>Portal Pelanggan ↗</span>
                </a>

                @auth
                    <!-- User Profile & Logout -->
                    <div class="flex items-center space-x-2 pl-2 border-l border-slate-200">
                        <div class="text-right hidden md:block">
                            <span class="block text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()->name }}</span>
                            <span class="block text-[10px] text-amber-700 font-semibold uppercase tracking-wider">{{ auth()->user()->role ?? 'Staff' }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all flex items-center space-x-1" title="Keluar / Logout">
                                <span>🚪 Keluar</span>
                            </button>
                        </form>
                    </div>
                @endauth
            </nav>

            <!-- Mobile Top Right Badge / Link -->
            <div class="sm:hidden flex items-center space-x-2">
                <a href="{{ route('customer.claim') }}" target="_blank"
                   class="px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-900 border border-amber-200 flex items-center space-x-1">
                    <span>📱</span>
                    <span>Klaim ↗</span>
                </a>
                @auth
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="Keluar">
                            🚪
                        </button>
                    </form>
                @endauth
            </div>
        </div>

        <!-- Low Stock Alert Banner (Jika Ada) -->
        @php
            $lowStockAlerts = \App\Models\Material::where('is_active', true)->whereRaw('stock <= min_stock')->get();
        @endphp
        @if($lowStockAlerts->isNotEmpty())
            <div class="bg-amber-50 border-t border-amber-200 px-3 py-1.5 sm:px-4 sm:py-2">
                <div class="max-w-7xl mx-auto flex items-center justify-between text-[11px] sm:text-xs text-amber-800">
                    <div class="flex items-center space-x-1.5 truncate pr-2">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900 animate-pulse flex-shrink-0">
                            ⚠️ Stok Menipis
                        </span>
                        <span class="truncate">
                            <strong>{{ $lowStockAlerts->pluck('name')->implode(', ') }}</strong>
                        </span>
                    </div>
                    <a href="{{ route('seller.scm') }}#tab-restock" class="font-bold underline hover:text-amber-950 flex-shrink-0">
                        Kulakan →
                    </a>
                </div>
            </div>
        @endif
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 w-full mt-3 sm:mt-4 no-print">
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl shadow-sm flex items-center justify-between text-xs sm:text-sm">
                <div class="flex items-center space-x-2">
                    <span class="text-emerald-500 font-bold">✓</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold ml-2">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-300 text-rose-800 px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl shadow-sm flex items-center justify-between text-xs sm:text-sm">
                <div class="flex items-center space-x-2">
                    <span class="text-rose-500 font-bold">✕</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold ml-2">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-300 text-rose-800 px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl shadow-sm">
                <p class="font-bold text-xs sm:text-sm mb-1">Terdapat kesalahan:</p>
                <ul class="list-disc list-inside text-[11px] sm:text-xs space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Area with Bottom Padding for Mobile Nav Bar -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6 pb-24 sm:pb-8">
        @yield('content')
    </main>

    <!-- Footer (Hidden on Mobile to save space) -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto no-print text-center text-xs text-slate-500 hidden sm:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between">
            <p>© {{ date('Y') }} {{ config('beverage.store.name') }} — Template Web App MVP UMKM Minuman (Laravel 13).</p>
            <p class="mt-1 sm:mt-0 text-slate-400">Modul Terintegrasi: ERP • SCM • CRM • POS</p>
        </div>
    </footer>

    <!-- ============================================================== -->
    <!-- MOBILE FIXED BOTTOM NAVIGATION BAR (Khusus Smartphone)         -->
    <!-- ============================================================== -->
    <nav class="sm:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 py-1 px-2 flex justify-around items-center shadow-lg no-print">
        <a href="{{ route('seller.pos') }}"
           class="flex flex-col items-center py-1 px-3 rounded-xl text-center transition-all {{ request()->routeIs('seller.pos*') ? 'text-amber-700 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
            <span class="text-lg {{ request()->routeIs('seller.pos*') ? 'scale-110' : '' }} transition-transform">🛒</span>
            <span class="text-[10px] mt-0.5">Kasir</span>
        </a>

        <a href="{{ route('seller.scm') }}"
           class="flex flex-col items-center py-1 px-3 rounded-xl text-center transition-all {{ request()->routeIs('seller.scm*') ? 'text-amber-700 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
            <span class="text-lg {{ request()->routeIs('seller.scm*') ? 'scale-110' : '' }} transition-transform">📦</span>
            <span class="text-[10px] mt-0.5">SCM</span>
        </a>

        <a href="{{ route('seller.erp') }}"
           class="flex flex-col items-center py-1 px-2 rounded-xl text-center transition-all {{ request()->routeIs('seller.erp*') ? 'text-amber-700 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
            <span class="text-lg {{ request()->routeIs('seller.erp*') ? 'scale-110' : '' }} transition-transform">💰</span>
            <span class="text-[10px] mt-0.5">ERP</span>
        </a>

        <a href="{{ route('seller.analytics') }}"
           class="flex flex-col items-center py-1 px-2 rounded-xl text-center transition-all {{ request()->routeIs('seller.analytics*') ? 'text-amber-700 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
            <span class="text-lg {{ request()->routeIs('seller.analytics*') ? 'scale-110' : '' }} transition-transform">📊</span>
            <span class="text-[10px] mt-0.5">Analisis</span>
        </a>

        <a href="{{ route('seller.crm') }}"
           class="flex flex-col items-center py-1 px-3 rounded-xl text-center transition-all {{ request()->routeIs('seller.crm*') ? 'text-amber-700 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
            <span class="text-lg {{ request()->routeIs('seller.crm*') ? 'scale-110' : '' }} transition-transform">👥</span>
            <span class="text-[10px] mt-0.5">CRM</span>
        </a>

        <a href="{{ route('customer.claim') }}" target="_blank"
           class="flex flex-col items-center py-1 px-3 rounded-xl text-center transition-all text-slate-500 hover:text-slate-900 font-medium">
            <span class="text-lg">📱</span>
            <span class="text-[10px] mt-0.5">Klaim ↗</span>
        </a>
    </nav>

    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
