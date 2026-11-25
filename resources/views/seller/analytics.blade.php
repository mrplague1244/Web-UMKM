@extends('layouts.seller')

@section('title', 'Analisis Bisnis & Grafik Penjualan')

@section('content')
<div class="space-y-5">
    <!-- HEADER ANALISIS BISNIS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center space-x-2">
                <span>📊</span>
                <span>Analisis Bisnis & Visualisasi Data</span>
            </h1>
            <p class="text-[11px] sm:text-xs text-slate-500">
                Wawasan performa omzet, tren harian, jam sibuk kedai, menu favorit, dan efisiensi bahan baku.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            <!-- Filter Timeframe Selector -->
            <div class="bg-white p-1 rounded-xl border border-slate-200 shadow-sm flex items-center space-x-1 text-xs">
                <a href="{{ route('seller.analytics', ['range' => '7d']) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all {{ $range === '7d' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    7 Hari
                </a>
                <a href="{{ route('seller.analytics', ['range' => '30d']) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all {{ $range === '30d' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    30 Hari
                </a>
                <a href="{{ route('seller.analytics', ['range' => 'this_month']) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all {{ $range === 'this_month' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Bulan Ini
                </a>
            </div>

            <!-- Tombol Catat Bahan Rusak -->
            <button onclick="openModal('modalWaste')" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center space-x-1.5 active:scale-95">
                <span>⚠️ Catat Bahan Rusak</span>
            </button>
        </div>
    </div>

    <!-- KARTU INDIKATOR KINERJA UTAMA (KPI CARDS) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Omzet Penjualan -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Omzet Penjualan</span>
                <span class="text-xs p-1 bg-amber-50 text-amber-700 rounded-lg">💰</span>
            </div>
            <span class="text-base sm:text-2xl font-black text-amber-800 block mt-1 leading-tight">
                Rp {{ number_format($totalSales, 0, ',', '.') }}
            </span>
            <div class="mt-2 text-[10px] sm:text-[11px] text-slate-500 border-t border-slate-100 pt-1.5 flex justify-between">
                <span>Laba Kotor:</span>
                <span class="font-bold text-emerald-700">Rp {{ number_format($totalGrossProfit, 0, ',', '.') }} ({{ $profitMargin }}%)</span>
            </div>
        </div>

        <!-- Total Minuman Terjual -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Volume Penjualan</span>
                <span class="text-xs p-1 bg-teal-50 text-teal-700 rounded-lg">🥤</span>
            </div>
            <span class="text-base sm:text-2xl font-black text-slate-900 block mt-1 leading-tight">
                {{ number_format($totalCupsSold, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Cup</span>
            </span>
            <div class="mt-2 text-[10px] sm:text-[11px] text-slate-500 border-t border-slate-100 pt-1.5 flex justify-between">
                <span>Total Transaksi:</span>
                <span class="font-bold text-slate-800">{{ $totalTransactions }} Pesanan</span>
            </div>
        </div>

        <!-- Rata-rata Pesanan (AOV) -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Order (AOV)</span>
                <span class="text-xs p-1 bg-indigo-50 text-indigo-700 rounded-lg">🧾</span>
            </div>
            <span class="text-base sm:text-2xl font-black text-indigo-900 block mt-1 leading-tight">
                Rp {{ number_format($averageOrderValue, 0, ',', '.') }}
            </span>
            <div class="mt-2 text-[10px] sm:text-[11px] text-slate-500 border-t border-slate-100 pt-1.5 flex justify-between">
                <span>Nilai per Struk:</span>
                <span class="font-bold text-slate-700">~{{ $totalTransactions > 0 ? round($totalCupsSold / $totalTransactions, 1) : 0 }} cup / order</span>
            </div>
        </div>

        <!-- Jam Paling Sibuk -->
        <div class="bg-gradient-to-tr from-amber-700 to-amber-900 text-white rounded-2xl p-3.5 sm:p-4 shadow-md flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-[11px] font-bold text-amber-200 uppercase tracking-wider">Jam Tersibuk Kedai</span>
                <span class="text-xs p-1 bg-white/20 rounded-lg">⚡</span>
            </div>
            <span class="text-base sm:text-xl font-black text-white block mt-1 leading-tight">
                {{ $busiestHour ?: 'Belum Terdeteksi' }}
            </span>
            <div class="mt-2 text-[10px] sm:text-[11px] text-amber-200 border-t border-amber-600/50 pt-1.5 flex justify-between">
                <span>Puncak Transaksi:</span>
                <span class="font-extrabold text-amber-100">{{ $maxHourlyTx }} Pesanan</span>
            </div>
        </div>
    </div>

    <!-- BARIS 1 GRAFIK: TREN HARIAN & DISTRIBUSI JAM SIBUK -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
        <!-- Grafik Garis Tren Penjualan Harian (7 Kolom) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">📈 Tren Omzet & Beban Harian</h3>
                    <p class="text-[10px] sm:text-[11px] text-slate-500">Perbandingan pemasukan penjualan kasir vs pengeluaran kulakan/operasional.</p>
                </div>
                <span class="text-[11px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200">
                    {{ $rangeLabel }}
                </span>
            </div>
            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="chartDailyTrend"></canvas>
            </div>
        </div>

        <!-- Grafik Batang Jam Sibuk Penjualan (5 Kolom) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3 flex flex-col justify-between">
            <div class="border-b border-slate-100 pb-2.5">
                <h3 class="font-bold text-slate-900 text-sm">⏰ Analisis Jam Ramai (Peak Hours)</h3>
                <p class="text-[10px] sm:text-[11px] text-slate-500">Distribusi jumlah transaksi per jam operasional (08:00 - 22:00).</p>
            </div>
            <div class="relative h-56 sm:h-64 w-full">
                <canvas id="chartHourlySales"></canvas>
            </div>
            <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 flex items-start space-x-2">
                <span class="text-sm">💡</span>
                <div>
                    <strong class="block font-bold">Rekomendasi Barista:</strong>
                    @if($busiestHour)
                        Kedai paling padat pada <strong>{{ $busiestHour }}</strong>. Pastikan persediaan es batu kristal, susu, dan bahan cup sudah siap sebelum jam ini.
                    @else
                        Belum cukup data transaksi untuk merekomendasikan jam puncak.
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- BARIS 2 GRAFIK: TOP 5 BEST SELLER & METODE PEMBAYARAN -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
        <!-- Top 5 Menu Terlaris (7 Kolom) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3.5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">🏆 5 Menu Paling Laris (Best Sellers)</h3>
                    <p class="text-[10px] sm:text-[11px] text-slate-500">Menu minuman dengan kontribusi kuantitas cup dan omzet tertinggi.</p>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Peringkat</span>
            </div>

            <!-- List Menu Terlaris -->
            <div class="space-y-2.5">
                @forelse($topProducts as $index => $item)
                    @php
                        $percentage = $totalSales > 0 ? round(($item->total_revenue / $totalSales) * 100, 1) : 0;
                    @endphp
                    <div class="p-2.5 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-100 transition-colors">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2">
                                <span class="w-5 h-5 rounded-full {{ $index === 0 ? 'bg-amber-500 text-white' : ($index === 1 ? 'bg-slate-400 text-white' : ($index === 2 ? 'bg-amber-700 text-white' : 'bg-slate-200 text-slate-700')) }} text-[10px] font-extrabold flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                <span class="font-bold text-slate-800">{{ $item->product_name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-extrabold text-amber-800">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-slate-400 ml-1">({{ $item->total_qty }} cup)</span>
                            </div>
                        </div>
                        <!-- Progress Bar Kontribusi Omzet -->
                        <div class="mt-1.5 flex items-center space-x-2 text-[10px] text-slate-400">
                            <div class="flex-1 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-600 h-1.5 rounded-full" style="width: {{ min(100, $percentage * 2) }}%"></div>
                            </div>
                            <span class="w-10 text-right font-medium text-slate-600">{{ $percentage }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada transaksi minuman dalam periode ini.</p>
                @endforelse
            </div>

            <!-- Menu Lambat Terjual (Slow Moving Alert) -->
            @if(count($unsoldProducts) > 0)
                <div class="p-3 bg-rose-50/70 border border-rose-200 rounded-xl text-[11px] text-rose-900 space-y-1">
                    <span class="font-bold flex items-center space-x-1 text-rose-800">
                        <span>⚠️</span>
                        <span>Menu Belum Terjual Sama Sekali:</span>
                    </span>
                    <p class="text-slate-600">
                        {{ implode(', ', array_slice($unsoldProducts, 0, 4)) }}
                        @if(count($unsoldProducts) > 4) (+{{ count($unsoldProducts) - 4 }} menu lainnya) @endif
                    </p>
                    <p class="text-[10px] text-rose-700 italic">
                        Saran: Buat paket hemat bundling dengan menu terlaris atau berikan diskon promo khusus untuk menghabiskan stok bahan.
                    </p>
                </div>
            @endif
        </div>

        <!-- Proporsi Metode Pembayaran (5 Kolom) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3.5 flex flex-col justify-between">
            <div class="border-b border-slate-100 pb-2.5">
                <h3 class="font-bold text-slate-900 text-sm">💳 Metode Pembayaran</h3>
                <p class="text-[10px] sm:text-[11px] text-slate-500">Proporsi transaksi Tunai (Cash) vs Nontunai (QRIS & Transfer).</p>
            </div>

            <div class="relative h-48 sm:h-52 w-full flex items-center justify-center">
                <canvas id="chartPaymentMethods"></canvas>
            </div>

            <!-- Ringkasan Rasio Cashless -->
            <div class="space-y-2 pt-2 border-t border-slate-100 text-xs">
                <div class="flex justify-between items-center text-slate-600">
                    <span class="flex items-center space-x-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span>💵 Tunai (Cash)</span>
                    </span>
                    <span class="font-bold text-slate-800">
                        {{ $paymentMethods['cash']['count'] }} tx (Rp {{ number_format($paymentMethods['cash']['amount'], 0, ',', '.') }})
                    </span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span class="flex items-center space-x-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>📱 QRIS Nontunai</span>
                    </span>
                    <span class="font-bold text-slate-800">
                        {{ $paymentMethods['qris']['count'] }} tx (Rp {{ number_format($paymentMethods['qris']['amount'], 0, ',', '.') }})
                    </span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span class="flex items-center space-x-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        <span>🏦 Transfer Bank</span>
                    </span>
                    <span class="font-bold text-slate-800">
                        {{ $paymentMethods['transfer']['count'] }} tx (Rp {{ number_format($paymentMethods['transfer']['amount'], 0, ',', '.') }})
                    </span>
                </div>
                <div class="p-2 bg-slate-50 rounded-lg text-center font-bold text-slate-700 text-[11px]">
                    Adopsi Nontunai: <span class="text-amber-700">{{ $cashlessPercentage }}%</span> Pelanggan
                </div>
            </div>
        </div>
    </div>

    <!-- BAGIAN 3: PENCATATAN BAHAN RUSAK / TERBUANG (WASTE & SPOILAGE) -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-1.5">
                    <span>🗑️</span>
                    <span>Pencatatan Bahan Rusak / Terbuang (Waste & Spoilage)</span>
                </h3>
                <p class="text-[10px] sm:text-[11px] text-slate-500">
                    Audit bahan baku yang tumpah, basi, atau rusak agar stok fisik dan nilai buku kas selalu akurat.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-rose-700 font-bold bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                    Total Kerugian: Rp {{ number_format($totalWasteCost, 0, ',', '.') }}
                </span>
                <button onclick="openModal('modalWaste')" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-all">
                    + Catat Kerusakan
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-4">Tanggal & Waktu</th>
                        <th class="py-2.5 px-4">Nama Bahan</th>
                        <th class="py-2.5 px-4 text-center">Jumlah Rusak</th>
                        <th class="py-2.5 px-4">Alasan & Catatan</th>
                        <th class="py-2.5 px-4 text-right">Estimasi Kerugian (Rp)</th>
                        <th class="py-2.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($wasteLogs as $waste)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2.5 px-4 font-mono text-slate-500 text-[11px]">{{ $waste->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-800">{{ $waste->material ? $waste->material->name : 'Bahan Dihapus' }}</td>
                            <td class="py-2.5 px-4 text-center font-mono font-bold text-rose-600">
                                -{{ $waste->quantity }} {{ $waste->material ? $waste->material->unit : '' }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-700 max-w-xs truncate">{{ $waste->notes }}</td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold text-rose-700">
                                Rp {{ number_format($waste->quantity * $waste->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-4 text-center">
                                <form action="{{ route('seller.analytics.waste.destroy', $waste->id) }}" method="POST" onsubmit="return confirm('Batalkan catatan ini dan kembalikan stok bahan?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded transition-all" title="Batalkan & Kembalikan Stok">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Tidak ada catatan bahan rusak pada periode ini. Bagus sekali! 👍</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL CATAT BAHAN RUSAK (WASTE & SPOILAGE) -->
<div id="modalWaste" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-3.5 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <h3 class="text-sm font-bold text-slate-900">⚠️ Catat Bahan Rusak / Terbuang</h3>
            <button onclick="closeModal('modalWaste')" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
        </div>
        <form action="{{ route('seller.analytics.waste') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Bahan Baku *</label>
                <select name="material_id" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500 font-semibold">
                    <option value="">-- Pilih Bahan Baku --</option>
                    @foreach($materials as $mat)
                        <option value="{{ $mat->id }}">
                            {{ $mat->name }} (Sisa Stok: {{ $mat->stock }} {{ $mat->unit }} • HPP: Rp {{ number_format($mat->cost_per_unit, 0) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Jumlah Rusak / Terbuang *</label>
                <input type="number" step="0.01" name="quantity" required placeholder="Contoh: 250 (gram) atau 2 (cup)"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500 font-bold">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Penyebab Kerusakan *</label>
                <select name="reason" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500 font-medium">
                    <option value="Basi / Kedaluwarsa">Susu / Sirup Basi atau Kedaluwarsa</option>
                    <option value="Tumpah / Tercecer">Tumpah atau Tercecer saat Meracik</option>
                    <option value="Kemasan Pecah / Bocor">Kemasan Pecah / Bocor / Rusak saat Pengiriman</option>
                    <option value="Salah Racik / Gagal">Salah Takaran / Pesanan Batal oleh Kasir</option>
                    <option value="Lainnya">Lain-lain</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Keterangan Tambahan</label>
                <input type="text" name="notes" placeholder="Contoh: Susu UHT lupa dimasukkan kulkas semalaman"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>

            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalWaste')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-md">Simpan Kerusakan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // Format Rupiah untuk Chart Tooltip
    const formatRp = (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val);

    // 1. Grafik Tren Penjualan & Pengeluaran Harian
    const ctxTrend = document.getElementById('chartDailyTrend').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyLabels) !!},
            datasets: [
                {
                    label: 'Omzet Penjualan',
                    data: {!! json_encode($dailySales) !!},
                    borderColor: '#d97706', // amber-600
                    backgroundColor: 'rgba(217, 119, 6, 0.12)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#d97706',
                    pointRadius: 4,
                },
                {
                    label: 'Pengeluaran Beban',
                    data: {!! json_encode($dailyExpenses) !!},
                    borderColor: '#e11d48', // rose-600
                    backgroundColor: 'rgba(225, 29, 72, 0.05)',
                    fill: false,
                    tension: 0.35,
                    borderWidth: 2,
                    pointBackgroundColor: '#e11d48',
                    pointRadius: 3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 11, family: 'sans-serif', weight: 'bold' }, boxWidth: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatRp(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return (value / 1000) + 'k';
                        },
                        font: { size: 10 }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Grafik Jam Ramai (Peak Hours Bar Chart)
    const ctxHourly = document.getElementById('chartHourlySales').getContext('2d');
    const hourlyCounts = {!! json_encode($hourlyTransactionCounts) !!};
    const maxVal = Math.max(...hourlyCounts);
    
    // Warnai bar tertinggi dengan warna amber pekat
    const barColors = hourlyCounts.map(val => val === maxVal && val > 0 ? '#b45309' : '#f59e0b');

    new Chart(ctxHourly, {
        type: 'bar',
        data: {
            labels: {!! json_encode($hourlyLabels) !!},
            datasets: [{
                label: 'Pesanan',
                data: hourlyCounts,
                backgroundColor: barColors,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' Pesanan Transaksi';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 10 } },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { font: { size: 9 } },
                    grid: { display: false }
                }
            }
        }
    });

    // 3. Grafik Donut Metode Pembayaran
    const ctxPayment = document.getElementById('chartPaymentMethods').getContext('2d');
    new Chart(ctxPayment, {
        type: 'doughnut',
        data: {
            labels: ['Tunai', 'QRIS', 'Transfer'],
            datasets: [{
                data: [
                    {{ $paymentMethods['cash']['count'] }},
                    {{ $paymentMethods['qris']['count'] }},
                    {{ $paymentMethods['transfer']['count'] }}
                ],
                backgroundColor: ['#10b981', '#f59e0b', '#6366f1'],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11, weight: 'bold' }, boxWidth: 12 }
                }
            },
            cutout: '70%'
        }
    });
</script>
@endpush
@endsection
