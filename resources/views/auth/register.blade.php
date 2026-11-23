<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - {{ config('beverage.store.name', 'Sistem UMKM Minuman') }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center p-4 font-sans text-slate-800 antialiased selection:bg-amber-200">
    <div class="max-w-md w-full space-y-6">
        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-amber-700 to-amber-500 flex items-center justify-center text-white shadow-lg text-3xl">
                🧋
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Pendaftaran Akun Pengelola
            </h1>
            <p class="text-xs text-slate-500">
                Buat akun baru untuk mengelola sistem operasional kedai minuman Anda.
            </p>
        </div>

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <p>✕ {{ $err }}</p>
                @endforeach
            </div>
        @endif

        <!-- Card Form Register -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-6 sm:p-8 space-y-5">
            <form action="{{ route('register') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label for="name" class="block font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" id="name" required autofocus
                           value="{{ old('name') }}"
                           placeholder="Contoh: Budi Pratama"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-xs font-medium">
                </div>

                <div>
                    <label for="email" class="block font-bold text-slate-700 mb-1">Alamat Email *</label>
                    <input type="email" name="email" id="email" required
                           value="{{ old('email') }}"
                           placeholder="nama@umkm.com"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-xs font-medium">
                </div>

                <div>
                    <label for="role" class="block font-bold text-slate-700 mb-1">Peran / Jabatan *</label>
                    <select name="role" id="role" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-xs font-medium">
                        <option value="owner">Pemilik Kedai (Owner - Akses Penuh)</option>
                        <option value="cashier">Kasir / Barista Toko</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block font-bold text-slate-700 mb-1">Kata Sandi *</label>
                        <input type="password" name="password" id="password" required
                               placeholder="Min. 6 karakter"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-xs font-medium">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block font-bold text-slate-700 mb-1">Ulangi Sandi *</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               placeholder="Ketik ulang"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-xs font-medium">
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold rounded-xl shadow-md transition-all text-sm flex items-center justify-center space-x-2">
                    <span>Daftarkan Akun & Mulai →</span>
                </button>
            </form>
        </div>

        <div class="text-center text-xs text-slate-500">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-bold text-amber-700 hover:underline">
                Masuk di Sini
            </a>
        </div>
    </div>
</body>
</html>
