<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Loyalitas - {{ $customer->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full flex flex-col justify-center items-center p-4 antialiased text-slate-800 bg-gradient-to-b from-slate-950 via-slate-900 to-amber-950 py-10">
    <div class="max-w-md w-full space-y-6">
        <!-- Notifikasi Sukses Klaim -->
        @if(session('success'))
            <div class="bg-emerald-500/20 border border-emerald-400 text-emerald-100 px-4 py-3 rounded-2xl text-xs font-semibold text-center backdrop-blur-md">
                🎉 {{ session('success') }}
            </div>
        @endif

        <!-- KARTU MEMBER DIGITAL VIRTUAL (STAMP CARD) -->
        <div class="rounded-3xl p-6 text-white shadow-2xl relative overflow-hidden border border-white/10
            @if($customer->tier === 'VIP') bg-gradient-to-tr from-purple-900 via-indigo-950 to-slate-900
            @elseif($customer->tier === 'Gold') bg-gradient-to-tr from-amber-700 via-amber-900 to-yellow-950
            @elseif($customer->tier === 'Silver') bg-gradient-to-tr from-slate-700 via-slate-800 to-slate-950
            @else bg-gradient-to-tr from-amber-900 via-stone-900 to-slate-950
            @endif">

            <!-- Watermark / Pattern Hiasan -->
            <div class="absolute -right-6 -bottom-6 text-8xl opacity-10 pointer-events-none">
                🧋
            </div>

            <div class="flex justify-between items-start mb-6">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-amber-200/80 block">
                        {{ $store['name'] ?? 'Kopi & Teh Nusantara' }}
                    </span>
                    <h2 class="text-xl font-black mt-0.5">{{ $customer->name }}</h2>
                    <p class="text-xs text-slate-300 font-mono">{{ $customer->phone }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 backdrop-blur-md border border-white/20 text-white">
                    ⭐ {{ $customer->tier }}
                </span>
            </div>

            <!-- GRID 10 STAMP LOYALITAS MINUMAN -->
            <div class="bg-black/30 backdrop-blur-md p-4 rounded-2xl border border-white/10 space-y-3">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-amber-300">🎯 Kartu Stamp Kedai</span>
                    <span class="font-bold text-slate-300">{{ $currentStamps }}/{{ $stampTarget }} Stamp</span>
                </div>

                <!-- 10 Lingkaran Stamp (2 baris x 5 kolom) -->
                <div class="grid grid-cols-5 gap-2.5 py-1">
                    @for($i = 1; $i <= $stampTarget; $i++)
                        @php $isCollected = $i <= $currentStamps; @endphp
                        <div class="aspect-square rounded-2xl flex items-center justify-center transition-all
                            {{ $isCollected
                                ? 'bg-gradient-to-tr from-amber-400 to-amber-300 text-amber-950 font-black shadow-md shadow-amber-400/20 scale-105'
                                : 'bg-white/5 border border-dashed border-white/20 text-slate-500 font-bold text-xs' }}">
                            @if($isCollected)
                                <span class="text-base">🧋</span>
                            @else
                                <span class="text-[11px]">{{ $i }}</span>
                            @endif
                        </div>
                    @endfor
                </div>

                <p class="text-[11px] text-center text-amber-200/90 font-medium pt-1">
                    @if($currentStamps === 0 && $customer->total_points > 0)
                        🎉 Target stamp tercapai! Tunjukkan ke kasir untuk menukar 1 minuman gratis.
                    @else
                        Kumpulkan <strong>{{ $stampTarget - $currentStamps }} stamp lagi</strong> untuk 1 minuman gratis!
                    @endif
                </p>
            </div>

            <!-- Footer Kartu -->
            <div class="mt-4 flex justify-between items-center text-xs text-slate-300 border-t border-white/10 pt-3">
                <div>
                    <span class="text-[10px] text-slate-400 block">Total Minuman Gratis Didapat:</span>
                    <strong class="text-amber-300 text-sm">{{ $freeDrinksEarned }} Cup 🎁</strong>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 block">Akumulasi Seluruh Poin:</span>
                    <strong class="text-white text-sm">{{ $customer->total_points }} Poin</strong>
                </div>
            </div>
        </div>

        <!-- Tombol Klaim Struk Lain & Riwayat Transaksi -->
        <div class="space-y-4">
            <a href="{{ route('customer.claim') }}"
               class="block w-full py-3 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-2xl text-center text-xs font-bold transition-all">
                + Klaim Struk Lainnya
            </a>

            <!-- Riwayat Transaksi Member -->
            <div class="bg-white rounded-3xl p-5 shadow-xl border border-slate-100 space-y-3">
                <h3 class="font-bold text-slate-900 text-xs border-b border-slate-100 pb-2">
                    Riwayat Kunjungan / Belanja
                </h3>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1 text-xs">
                    @forelse($customer->transactions as $trx)
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                            <div class="flex justify-between font-bold">
                                <span class="font-mono text-slate-800">{{ $trx->invoice_code }}</span>
                                <span class="text-amber-700">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-[10px] text-slate-400">{{ $trx->created_at->format('d/m/Y H:i') }} • {{ strtoupper($trx->payment_method) }}</p>
                        </div>
                    @empty
                        <p class="text-slate-400 text-center py-4 text-xs">Belum ada transaksi tercatat.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-[10px] text-slate-500">
            <p>{{ $store['name'] ?? 'Kedai Minuman UMKM' }} • Program Loyalitas</p>
        </div>
    </div>
</body>
</html>
