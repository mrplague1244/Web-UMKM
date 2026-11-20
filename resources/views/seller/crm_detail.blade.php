@extends('layouts.seller')

@section('title', 'Profil Member - ' . $customer->name)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Tombol Kembali -->
    <div class="flex items-center justify-between">
        <a href="{{ route('seller.crm') }}" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition-all">
            ← Kembali ke Daftar Member
        </a>
        <a href="{{ route('customer.loyalty', $customer->phone) }}" target="_blank"
           class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all">
            Buka Kartu Loyalitas Pelanggan ↗
        </a>
    </div>

    <!-- KARTU PROFIL MEMBER -->
    <div class="bg-gradient-to-r from-amber-800 to-amber-950 rounded-3xl p-6 text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-3xl">
                🧋
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-xl font-bold">{{ $customer->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-400 text-amber-950">
                        {{ $customer->tier }} Member
                    </span>
                </div>
                <p class="text-xs text-amber-200 mt-1">📞 {{ $customer->phone }} • Bergabung sejak {{ $customer->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <div class="bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20 text-center">
            <span class="text-[11px] font-bold text-amber-200 uppercase tracking-wider block">Saldo Poin Stamp</span>
            <span class="text-3xl font-black text-amber-300 block mt-0.5">{{ $customer->total_points }}</span>
            <span class="text-[10px] text-amber-100 block mt-1">Target 10 Stamp = 1 Free Cup</span>
        </div>
    </div>

    <!-- DUA KOLOM: RIWAYAT TRANSAKSI & RIWAYAT POIN -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kolom Kiri: Riwayat Transaksi Belanja -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h3 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-2">
                Riwayat Kunjungan / Belanja ({{ $customer->transactions->count() }})
            </h3>
            <div class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
                @forelse($customer->transactions as $trx)
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1">
                        <div class="flex justify-between items-center font-bold">
                            <span class="font-mono text-slate-900">{{ $trx->invoice_code }}</span>
                            <span class="text-amber-700">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500">{{ $trx->created_at->format('d/m/Y H:i') }} • {{ strtoupper($trx->payment_method) }}</p>
                        <div class="text-[10px] text-slate-600 pt-1 border-t border-slate-200">
                            @foreach($trx->items as $it)
                                <span class="inline-block bg-white px-1.5 py-0.5 rounded border border-slate-200 mr-1 mb-1">
                                    {{ $it->quantity }}x {{ $it->product_name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada riwayat transaksi.</p>
                @endforelse
            </div>
        </div>

        <!-- Kolom Kanan: Log Mutasi Poin Stamp -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h3 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-2">
                Log Mutasi Poin Loyalitas
            </h3>
            <div class="space-y-2 max-h-[400px] overflow-y-auto pr-1">
                @forelse($customer->loyaltyLogs as $log)
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs flex justify-between items-center">
                        <div>
                            <span class="font-bold text-slate-800 block">{{ $log->description }}</span>
                            <span class="text-[10px] text-slate-400">{{ $log->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <span class="font-black px-2 py-1 rounded-lg text-xs {{ $log->points > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $log->points > 0 ? '+' : '' }}{{ $log->points }} Poin
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada catatan mutasi poin.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
