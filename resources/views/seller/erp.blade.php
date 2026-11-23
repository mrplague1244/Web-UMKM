@extends('layouts.seller')

@section('title', 'ERP & Laporan Keuangan Kedai')

@section('content')
<div class="space-y-5">
    <!-- Header ERP -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">💰 Enterprise Resource Planning (ERP)</h1>
            <p class="text-[11px] sm:text-xs text-slate-500">Monitoring arus kas, omzet penjualan, biaya operasional, dan laba rugi kedai minuman.</p>
        </div>
        <button onclick="openModal('modalCashFlow')" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center space-x-1.5 self-start sm:self-auto active:scale-95">
            <span>+ Catat Biaya Operasional</span>
        </button>
    </div>

    <!-- KARTU METRIK KEUANGAN (2x2 di Smartphone, 4 Kolom di Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Saldo Kas Aktif -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Saldo Kas Riil</span>
            <span class="text-base sm:text-2xl font-black text-slate-900 block mt-1 leading-tight">
                Rp {{ number_format($activeCashBalance, 0, ',', '.') }}
            </span>
            <div class="mt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between text-[10px] sm:text-[11px] text-slate-500 border-t border-slate-100 pt-1.5">
                <span>In: {{ number_format($totalCashIn / 1000, 0) }}k</span>
                <span>Out: {{ number_format($totalCashOut / 1000, 0) }}k</span>
            </div>
        </div>

        <!-- Omzet Bulan Ini -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Omzet Bulan Ini</span>
            <span class="text-base sm:text-2xl font-black text-amber-700 block mt-1 leading-tight">
                Rp {{ number_format($monthlySales, 0, ',', '.') }}
            </span>
            <div class="mt-2 text-[10px] sm:text-[11px] text-slate-500 border-t border-slate-100 pt-1.5 flex justify-between">
                <span>Hari Ini:</span>
                <span class="font-bold text-slate-800">Rp {{ number_format($todaySales, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Biaya Kulakan & Operasional -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Beban</span>
            <span class="text-base sm:text-2xl font-black text-rose-700 block mt-1 leading-tight">
                Rp {{ number_format($monthlyRestock + $monthlyOperational, 0, ',', '.') }}
            </span>
            <div class="mt-2 text-[10px] sm:text-[11px] text-slate-500 border-t border-slate-100 pt-1.5 flex justify-between">
                <span>Kulakan: {{ number_format($monthlyRestock / 1000, 0) }}k</span>
                <span>Ops: {{ number_format($monthlyOperational / 1000, 0) }}k</span>
            </div>
        </div>

        <!-- Estimasi Laba Bersih -->
        <div class="bg-gradient-to-tr from-amber-700 to-amber-900 rounded-2xl p-3 sm:p-4 text-white shadow-md flex flex-col justify-between">
            <span class="text-[10px] sm:text-[11px] font-bold text-amber-200 uppercase tracking-wider block">Laba Bersih</span>
            <span class="text-base sm:text-2xl font-black text-white block mt-1 leading-tight">
                Rp {{ number_format($estimatedNetProfit, 0, ',', '.') }}
            </span>
            <div class="mt-2 text-[10px] sm:text-[11px] text-amber-200 border-t border-amber-600/50 pt-1.5 flex justify-between">
                <span>Margin:</span>
                <span class="font-bold">
                    {{ $monthlySales > 0 ? round(($estimatedNetProfit / $monthlySales) * 100, 1) : 0 }}%
                </span>
            </div>
        </div>
    </div>

    <!-- DUA KOLOM: BUKU KAS & PENJUALAN TERBARU -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
        <!-- Kolom Kiri: Buku Kas Arus Kas (8 Kolom) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-3.5 sm:p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Buku Kas (Arus Kas Masuk & Keluar)</h3>
                    <p class="text-[10px] sm:text-[11px] text-slate-500">Mencatat otomatis penjualan kasir, kulakan SCM, dan biaya operasional.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4">Tanggal</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4">Tipe</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4">Kategori</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4">Keterangan</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4 text-right">Nominal</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($cashFlows as $cf)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 font-mono text-slate-500 text-[11px]">{{ $cf->transaction_date->format('d/m/Y') }}</td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4">
                                    @if($cf->type === 'in')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            + Masuk
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                            - Keluar
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 font-semibold text-slate-700 capitalize text-[11px]">{{ $cf->category }}</td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 text-slate-800 text-[11px] truncate max-w-xs">{{ $cf->description }}</td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 text-right font-bold text-xs {{ $cf->type === 'in' ? 'text-emerald-700' : 'text-rose-600' }}">
                                    {{ $cf->type === 'in' ? '+' : '-' }} Rp {{ number_format($cf->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 text-center">
                                    <form action="{{ route('seller.erp.cashflow.destroy', $cf->id) }}" method="POST" onsubmit="return confirm('Hapus catatan arus kas ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition-all" title="Hapus Catatan">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Belum ada catatan arus kas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan: Transaksi Kasir POS Terkini (4 Kolom) -->
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3.5">
            <h3 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-2">
                Transaksi Kasir Terakhir
            </h3>
            <div class="space-y-2.5">
                @forelse($recentTransactions as $rt)
                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl space-y-1.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="font-mono font-bold text-slate-800 text-[11px]">{{ $rt->invoice_code }}</span>
                            <span class="font-extrabold text-amber-700">Rp {{ number_format($rt->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-500">
                            <span>{{ $rt->created_at->format('d/m H:i') }} • {{ strtoupper($rt->payment_method) }}</span>
                            <span class="{{ $rt->claim_status === 'claimed' ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">
                                {{ $rt->claim_status === 'claimed' ? '✓ Poin Diklaim' : 'Belum Diklaim' }}
                            </span>
                        </div>
                        <div class="text-[10px] text-slate-600 pt-1 border-t border-slate-200 flex justify-between">
                            <span>Laba Kotor:</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($rt->gross_profit, 0, ',', '.') }}</span>
                        </div>
                        <div class="pt-1.5 border-t border-slate-100 flex justify-between items-center text-[10px]">
                            <span class="text-slate-400">Kasir: {{ $rt->cashier_name ?: 'Toko' }}</span>
                            <form action="{{ route('seller.erp.transaction.destroy', $rt->id) }}" method="POST" onsubmit="return confirm('Batalkan transaksi {{ $rt->invoice_code }}? Stok bahan dan kas penjualan akan disesuaikan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold px-2 py-0.5 bg-rose-50 hover:bg-rose-100 rounded border border-rose-200 transition-all flex items-center space-x-1">
                                    <span>🗑️ Batalkan</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada transaksi penjualan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- MODAL CATAT ARUS KAS (Mobile Bottom Sheet & Desktop) -->
<div id="modalCashFlow" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-3.5 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <h3 class="text-sm font-bold text-slate-900">+ Catat Pengeluaran / Kas Masuk</h3>
            <button onclick="closeModal('modalCashFlow')" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
        </div>
        <form action="{{ route('seller.erp.cashflow') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Jenis Arus Kas *</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="out" checked class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-rose-600 peer-checked:bg-rose-50 peer-checked:text-rose-800 font-bold">
                            - Kas Keluar (Beban)
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="in" class="peer sr-only">
                        <div class="p-2 text-center rounded-xl border border-slate-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 font-bold">
                            + Kas Masuk (Lainnya)
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Kategori Pengeluaran *</label>
                <select name="category" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                    <option value="operational">Operasional Harian (Es Batu, Galon, Plastik)</option>
                    <option value="electricity">Listrik & Gas</option>
                    <option value="rent">Sewa Tempat / Gerobak</option>
                    <option value="salary">Gaji Karyawan / Uang Makan</option>
                    <option value="other">Lain-lain</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nominal (Rp) *</label>
                <input type="number" name="amount" required placeholder="Contoh: 35000"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500 font-bold">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Keterangan Pengeluaran *</label>
                <input type="text" name="description" required placeholder="Contoh: Beli 2 balok es batu kristal"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Tanggal Transaksi *</label>
                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>

            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalCashFlow')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Catatan</button>
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
