<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ config('beverage.store.name', 'Sistem UMKM Minuman') }}</title>
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
                            500: '#8b5e3c',
                            600: '#6f4528',
                            700: '#54331c',
                            800: '#432814',
                            900: '#2b180a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center p-4 font-sans text-slate-800 antialiased selection:bg-amber-200">
    <div class="max-w-md w-full space-y-6">
        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-amber-700 to-amber-500 flex items-center justify-center text-white shadow-lg text-3xl">
                🧋
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                {{ config('beverage.store.name', 'Kedai Kopi & Teh Nusantara') }}
            </h1>
            <p class="text-xs text-slate-500">
                Masuk ke sistem operasional kasir POS, SCM bahan, dan keuangan kedai.
            </p>
        </div>

        <!-- Flash Alert -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-xs flex items-center justify-between">
                <span>✓ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="font-bold text-emerald-600">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <p>✕ {{ $err }}</p>
                @endforeach
            </div>
        @endif

        <!-- Card Form Login -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-6 sm:p-8 space-y-5">
            <form action="{{ route('login') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label for="email" class="block font-bold text-slate-700 mb-1">Alamat Email *</label>
                    <input type="email" name="email" id="email" required autofocus
                           value="{{ old('email') }}"
                           placeholder="nama@umkm.com"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-xs font-medium">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label for="password" class="block font-bold text-slate-700">Kata Sandi *</label>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-xs font-medium pr-10">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <span id="eyeIcon">👁️</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                        <span class="text-xs text-slate-600 select-none">Ingat saya</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold rounded-xl shadow-md transition-all text-sm flex items-center justify-center space-x-2">
                    <span>Masuk ke Dashboard →</span>
                </button>
            </form>

            <!-- Box Demo Credentials (1-Click Auto Fill) -->
            <div class="pt-4 border-t border-slate-100 space-y-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block text-center">
                    Akun Demo Siap Pakai (Klik untuk Isi Otomatis)
                </span>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillDemo('owner@umkm.com', 'password')"
                            class="p-2 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl text-left transition-all">
                        <span class="font-bold text-[11px] text-amber-900 block">👑 Owner / Pemilik</span>
                        <span class="text-[10px] text-amber-700 block">owner@umkm.com</span>
                    </button>
                    <button type="button" onclick="fillDemo('kasir@umkm.com', 'password')"
                            class="p-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-left transition-all">
                        <span class="font-bold text-[11px] text-slate-800 block">🛒 Kasir Toko</span>
                        <span class="text-[10px] text-slate-600 block">kasir@umkm.com</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Link Register & Portal Pelanggan -->
        <div class="text-center space-y-2 text-xs text-slate-500">
            <p>
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-bold text-amber-700 hover:underline">
                    Daftar Akun Baru
                </a>
            </p>
            <p>
                Pelanggan kedai ingin klaim poin?
                <a href="{{ route('customer.claim') }}" target="_blank" class="font-bold text-emerald-700 hover:underline">
                    Buka Portal Pelanggan ↗
                </a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eye = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.innerText = '🙈';
            } else {
                pwd.type = 'password';
                eye.innerText = '👁️';
            }
        }

        function fillDemo(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
