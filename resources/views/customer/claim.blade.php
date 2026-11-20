<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klaim Poin Loyalitas - {{ $store['name'] ?? 'Kedai Minuman' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#8b5e3c',
                            600: '#6f4528',
                            700: '#54331c',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-full flex flex-col justify-center items-center p-4 antialiased text-slate-800 bg-gradient-to-b from-slate-950 via-slate-900 to-amber-950">
    <div class="max-w-md w-full space-y-6">
        <!-- Header Kedai -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-amber-600 to-amber-400 mx-auto flex items-center justify-center text-3xl shadow-xl shadow-amber-900/30">
                🧋
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">
                {{ $store['name'] ?? 'Kopi & Teh Nusantara' }}
            </h1>
            <p class="text-xs text-amber-200/80">
                Portal Mandiri Klaim Stamp Loyalitas Pelanggan
            </p>
        </div>

        <!-- Kartu Formulir Klaim -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100/10 space-y-5">
            <!-- Flash Alerts -->
            @if(session('error'))
                <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center space-x-2">
                    <span>✕</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="text-center pb-2 border-b border-slate-100">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 mb-2">
                    🎁 Kumpulkan 10 Stamp = 1 Minuman Gratis!
                </span>
                <p class="text-xs text-slate-500">
                    Masukkan 6-karakter kode klaim yang tertera pada bagian bawah struk kasir Anda.
                </p>
            </div>

            <form action="{{ route('customer.claim.process') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <!-- Input Kode Struk -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1.5 uppercase tracking-wider text-[11px]">
                        Kode Struk Kasir *
                    </label>
                    <input type="text" name="claim_token" required value="{{ old('claim_token', $tokenFromUrl) }}"
                           placeholder="CONTOH: 9X4K2P" maxlength="12"
                           class="w-full px-4 py-3 text-center text-lg font-mono font-black uppercase tracking-widest text-slate-900 bg-slate-50 border-2 border-dashed border-amber-400 rounded-2xl focus:outline-none focus:bg-white focus:border-amber-600 transition-all">
                </div>

                <!-- Input No HP / WhatsApp -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1.5 text-[11px]">
                        Nomor WhatsApp Member *
                    </label>
                    <input type="text" name="phone" required value="{{ old('phone') }}"
                           placeholder="08xxxxxxxxxx"
                           class="w-full px-4 py-2.5 text-xs text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:bg-white focus:ring-1 focus:ring-amber-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Digunakan untuk menyimpan poin stamp Anda.</span>
                </div>

                <!-- Input Nama Pelanggan -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1.5 text-[11px]">
                        Nama Anda *
                    </label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           placeholder="Nama Panggilan / Lengkap"
                           class="w-full px-4 py-2.5 text-xs text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:bg-white focus:ring-1 focus:ring-amber-500">
                </div>

                <!-- Tombol Submit -->
                <button type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white font-black text-sm rounded-2xl shadow-lg shadow-amber-600/30 transition-all active:scale-[0.98] flex items-center justify-center space-x-2">
                    <span>Klaim Poin Minuman Saya</span>
                    <span>✨</span>
                </button>
            </form>
        </div>

        <!-- Info Reward -->
        <div class="text-center text-slate-400 text-[11px] space-y-1">
            <p>Setiap kelipatan Rp 10.000 belanja = 1 Poin Stamp.</p>
            <p>Punya struk lain? Anda bisa mengklaim beberapa struk sekaligus.</p>
        </div>
    </div>
</body>
</html>
