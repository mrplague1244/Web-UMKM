<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - {{ $store['name'] ?? 'Kedai Minuman UMKM' }}</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .print-container {
                max-width: 100% !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 text-sm font-sans min-h-screen p-4 sm:p-8">

    <!-- FLOATING PRINT / CLOSE BAR (Hanya tampil di layar) -->
    <div class="no-print max-w-4xl mx-auto mb-4 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center space-x-2">
            <span class="text-xl">🖨️</span>
            <div>
                <h2 class="text-sm font-bold text-slate-900">Pratinjau Cetak Laporan Keuangan</h2>
                <p class="text-[11px] text-slate-500">Klik cetak untuk mencetak fisik atau memilih 'Save as PDF' di browser Anda.</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center space-x-1.5 active:scale-95">
                <span>🖨️ Cetak / Simpan PDF</span>
            </button>
            <button onclick="window.close()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition-all">
                ✕ Tutup
            </button>
        </div>
    </div>

    <!-- HALAMAN UTAMA LAPORAN RESMI (A4 Optimized) -->
    <div class="print-container max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-sm border border-slate-200 space-y-6">
        
        <!-- KOP LAPORAN -->
        <div class="border-b-2 border-slate-800 pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">🧋</span>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $store['name'] ?? 'Kedai Minuman UMKM' }}</h1>
                </div>
                <p class="text-xs text-slate-500 font-medium">{{ $store['tagline'] ?? 'Racikan Kopi & Teh Autentik' }}</p>
                <p class="text-[11px] text-slate-500">{{ $store['address'] ?? 'Alamat Toko' }} • Telp: {{ $store['phone'] ?? '-' }}</p>
            </div>
            <div class="text-left sm:text-right space-y-0.5">
                <span class="inline-block px-3 py-1 bg-amber-100 text-amber-900 font-extrabold rounded-full text-xs uppercase tracking-wider">
                    Laporan Arus Kas
                </span>
                <p class="text-xs font-bold text-slate-700 pt-1">
                    Periode: 
                    <span class="text-slate-900 font-black">
                        {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Awal' }}
                        s/d
                        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : date('d/m/Y') }}
                    </span>
                </p>
                <p class="text-[10px] text-slate-400">Dicetak: {{ date('d/m/Y H:i') }} WIB</p>
            </div>
        </div>

        <!-- RINGKASAN FINANSIAL (RINGKASAN METRIK PERIODE) -->
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Ringkasan Finansial Periode</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Kas Masuk</span>
                    <span class="text-base sm:text-lg font-black text-emerald-700 block mt-0.5">
                        Rp {{ number_format($totalIn, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Kas Keluar</span>
                    <span class="text-base sm:text-lg font-black text-rose-700 block mt-0.5">
                        Rp {{ number_format($totalOut, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Omzet Penjualan</span>
                    <span class="text-base sm:text-lg font-black text-amber-800 block mt-0.5">
                        Rp {{ number_format($sales, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3.5 bg-slate-900 text-white rounded-2xl">
                    <span class="text-[10px] font-bold text-amber-300 uppercase tracking-wider block">Laba Bersih Periode</span>
                    <span class="text-base sm:text-lg font-black text-white block mt-0.5">
                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN ARUS KAS -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Buku Kas Masuk & Keluar</h2>
                <span class="text-[11px] text-slate-500">Total: {{ $cashFlows->count() }} Transaksi</span>
            </div>
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-10">No</th>
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">Tipe</th>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-4">Keterangan</th>
                            <th class="py-2.5 px-3 text-right">Kas Masuk (Rp)</th>
                            <th class="py-2.5 px-3 text-right">Kas Keluar (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[11px]">
                        @forelse($cashFlows as $index => $cf)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-2 px-3 text-center text-slate-400 font-mono">{{ $index + 1 }}</td>
                                <td class="py-2 px-3 text-slate-600 font-mono">{{ $cf->transaction_date ? $cf->transaction_date->format('d/m/Y') : '-' }}</td>
                                <td class="py-2 px-3">
                                    @if($cf->type === 'in')
                                        <span class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-emerald-100 text-emerald-800">+ Masuk</span>
                                    @else
                                        <span class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-rose-100 text-rose-800">- Keluar</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 font-semibold text-slate-700 capitalize">{{ $cf->category }}</td>
                                <td class="py-2 px-4 text-slate-800 max-w-xs truncate">{{ $cf->description }}</td>
                                <td class="py-2 px-3 text-right font-mono font-bold text-emerald-700">
                                    {{ $cf->type === 'in' ? number_format($cf->amount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-bold text-rose-700">
                                    {{ $cf->type === 'out' ? number_format($cf->amount, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-400">Tidak ada catatan arus kas pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-300 text-xs">
                        <tr>
                            <td colspan="5" class="py-2.5 px-4 text-right uppercase tracking-wider text-slate-700">Total Kas:</td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-800">Rp {{ number_format($totalIn, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-right font-mono text-rose-800">Rp {{ number_format($totalOut, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="bg-slate-100">
                            <td colspan="5" class="py-2.5 px-4 text-right uppercase tracking-wider text-slate-900 font-black">Selisih Kas Bersih:</td>
                            <td colspan="2" class="py-2.5 px-4 text-right font-mono font-black text-sm {{ ($totalIn - $totalOut) >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">
                                Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- KOLOM TANDA TANGAN / PENGESAHAN -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-2 text-center text-xs text-slate-600">
            <div>
                <p class="font-medium text-slate-400 mb-14">Petugas Kasir / Admin</p>
                <p class="font-bold text-slate-900 underline underline-offset-4">
                    {{ Auth::check() ? Auth::user()->name : 'Petugas Toko' }}
                </p>
                <p class="text-[10px] text-slate-400">Staff Operasional</p>
            </div>
            <div>
                <p class="font-medium text-slate-400 mb-14">Pemilik Kedai (Owner)</p>
                <p class="font-bold text-slate-900 underline underline-offset-4">
                    (......................................)
                </p>
                <p class="text-[10px] text-slate-400">Penanggung Jawab Usaha</p>
            </div>
        </div>

    </div>

</body>
</html>
