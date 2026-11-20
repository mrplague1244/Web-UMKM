<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem UMKM Minuman') - {{ config('beverage.store.name', 'Kedai Kopi & Teh') }}</title>
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
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased">
    <!-- Top Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <!-- Brand & Store Name -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-amber-600 flex items-center justify-center text-white shadow-md">
                    <span class="text-xl">🧋</span>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 leading-tight">
                        {{ config('beverage.store.name', 'Kopi & Teh Nusantara') }}
                    </h1>
                    <p class="text-xs text-slate-500 font-medium hidden sm:block">
                        {{ config('beverage.store.tagline', 'ERP - SCM - CRM Kedai Minuman') }}
                    </p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex items-center space-x-1 sm:space-x-2">
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

                <a href="{{ route('seller.crm') }}"
                   class="px-3 py-2 rounded-lg text-sm font-semibold transition-all flex items-center space-x-1.5 {{ request()->routeIs('seller.crm*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>👥</span>
                    <span>CRM Loyalitas</span>
                </a>

                <a href="{{ route('customer.claim') }}" target="_blank"
                   class="hidden md:inline-flex px-3 py-2 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 items-center space-x-1 transition-all border border-slate-300">
                    <span>📱</span>
                    <span>Portal Pelanggan ↗</span>
                </a>
            </nav>
        </div>

        <!-- Low Stock Alert Banner (Jika Ada) -->
        @php
            $lowStockAlerts = \App\Models\Material::where('is_active', true)->whereRaw('stock <= min_stock')->get();
        @endphp
        @if($lowStockAlerts->isNotEmpty())
            <div class="bg-amber-50 border-t border-amber-200 px-4 py-2">
                <div class="max-w-7xl mx-auto flex items-center justify-between text-xs text-amber-800">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-200 text-amber-900 animate-pulse">
                            ⚠️ Stok Menipis
                        </span>
                        <span>
                            Bahan berikut hampir habis:
                            <strong>{{ $lowStockAlerts->pluck('name')->implode(', ') }}</strong>.
                        </span>
                    </div>
                    <a href="{{ route('seller.scm') }}#tab-restock" class="font-bold underline hover:text-amber-950">
                        Kulakan Sekarang →
                    </a>
                </div>
            </div>
        @endif
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4 no-print">
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-emerald-500 font-bold">✓</span>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-sm font-bold">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-rose-500 font-bold">✕</span>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 text-sm font-bold">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl shadow-sm">
                <p class="font-bold text-sm mb-1">Terdapat kesalahan:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto no-print text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between">
            <p>© {{ date('Y') }} {{ config('beverage.store.name') }} — Template Web App MVP UMKM Minuman (Laravel 13).</p>
            <p class="mt-1 sm:mt-0 text-slate-400">Modul Terintegrasi: ERP • SCM • CRM • POS</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
