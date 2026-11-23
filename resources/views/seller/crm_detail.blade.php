@extends('layouts.seller')

@section('title', 'Profil Member - ' . $customer->name)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Tombol Kembali & Aksi Member -->
    <div class="flex flex-wrap items-center justify-between gap-2">
        <a href="{{ route('seller.crm') }}" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition-all">
            ← Kembali ke Daftar Member
        </a>
        <div class="flex items-center space-x-2">
            <button onclick="openModal('modalAdjustPoints')" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center space-x-1">
                <span>⭐ Sesuaikan Poin / Hadiah</span>
            </button>
            <a href="{{ route('customer.loyalty', $customer->phone) }}" target="_blank"
               class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all">
                Kartu Loyalitas ↗
            </a>
            <form action="{{ route('seller.crm.customer.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus member {{ $customer->name }} secara permanen?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-xl transition-all">
                    🗑️ Hapus Member
                </button>
            </form>
        </div>
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
                        <div class="flex items-center space-x-2">
                            <span class="font-black px-2 py-1 rounded-lg text-xs {{ $log->points > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $log->points > 0 ? '+' : '' }}{{ $log->points }} Poin
                            </span>
                            <form action="{{ route('seller.crm.loyalty.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat poin ini? Saldo poin member akan disesuaikan.')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-300 hover:text-rose-500 rounded text-xs" title="Hapus Riwayat Poin">✕</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada catatan mutasi poin.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- MODAL SESUAIKAN POIN (Mobile Bottom Sheet & Desktop) -->
<div id="modalAdjustPoints" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-3.5 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <h3 class="text-sm font-bold text-slate-900">⭐ Sesuaikan Poin Member</h3>
            <button onclick="closeModal('modalAdjustPoints')" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
        </div>
        <form action="{{ route('seller.crm.customer.points', $customer->id) }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Aksi Penyesuaian *</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="earn" checked class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 font-bold">
                            + Tambah Poin
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="redeem" class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 font-bold">
                            - Tukar Reward
                        </div>
                    </label>
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Jumlah Poin / Stamp *</label>
                <input type="number" name="points" min="1" value="1" required placeholder="Contoh: 10"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500 font-bold">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Keterangan / Alasan *</label>
                <input type="text" name="description" required placeholder="Contoh: Penukaran 1 Free Cup Kopi Susu"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalAdjustPoints')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Perubahan Poin</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endpush
@endsection
