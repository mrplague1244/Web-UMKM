<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu QR Meja - {{ $store['name'] ?? 'Kedai Minuman UMKM' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 font-sans min-h-screen">

    <!-- FLOATING TOP BAR -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center space-x-1.5">
                <span>🖨️</span>
                <span>Cetak Kartu QR Meja (Table Standee)</span>
            </h2>
            <p class="text-[11px] text-slate-500">Cetak lembar kartu meja ini, gunting, dan letakkan di atas meja kedai untuk memudahkan pelanggan melihat menu digital.</p>
        </div>
        <div class="flex items-center space-x-2">
            <!-- Jumlah Meja -->
            <form action="{{ route('seller.table_qr') }}" method="GET" class="flex items-center space-x-1">
                <span class="text-xs font-semibold text-slate-500">Jumlah:</span>
                <select name="tables" onchange="this.form.submit()" class="text-xs px-2 py-1.5 border border-slate-300 rounded-lg font-bold">
                    <option value="5" {{ $tableCount == 5 ? 'selected' : '' }}>5 Meja</option>
                    <option value="10" {{ $tableCount == 10 ? 'selected' : '' }}>10 Meja</option>
                    <option value="15" {{ $tableCount == 15 ? 'selected' : '' }}>15 Meja</option>
                    <option value="20" {{ $tableCount == 20 ? 'selected' : '' }}>20 Meja</option>
                </select>
            </form>
            <button onclick="window.print()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-md transition-all">
                🖨️ Cetak / PDF
            </button>
            <button onclick="window.close()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">
                ✕ Tutup
            </button>
        </div>
    </div>

    <!-- PRINT GRID CARDS (2 Kolom di A4) -->
    <div class="max-w-4xl mx-auto grid grid-cols-1 sm:grid-cols-2 gap-6">
        @for($i = 1; $i <= $tableCount; $i++)
            @php
                $tableUrl = $baseUrl . '?table=' . $i;
                $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($tableUrl);
            @endphp
            <div class="bg-white rounded-3xl p-6 border-2 border-dashed border-slate-300 shadow-xs flex flex-col items-center text-center space-y-3 relative overflow-hidden">
                <!-- Watermark Nomor Meja -->
                <div class="w-full flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center space-x-1.5 text-left">
                        <span class="text-lg">🧋</span>
                        <div>
                            <span class="block text-xs font-black text-slate-900 leading-none">{{ $store['name'] ?? 'Kedai Kopi & Teh' }}</span>
                            <span class="text-[9px] text-slate-400 font-medium">Menu Digital & Self-Order</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-amber-600 text-white font-black text-xs rounded-xl shadow-xs">
                        MEJA {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <!-- Callout Header -->
                <div class="space-y-0.5 pt-1">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">SCAN UNTUK LIHAT MENU</h3>
                    <p class="text-[10px] text-slate-500">Arahkan kamera ponsel Anda ke kode QR di bawah ini</p>
                </div>

                <!-- QR CODE IMAGE -->
                <div class="p-3 bg-white border-2 border-amber-500 rounded-2xl shadow-sm flex items-center justify-center">
                    <img src="{{ $qrApiUrl }}" alt="QR Code Meja {{ $i }}" class="w-36 h-36 object-contain">
                </div>

                <!-- Petunjuk Pemesanan -->
                <div class="text-[10px] text-slate-500 space-y-0.5 pt-1">
                    <p class="font-bold text-slate-700">1. Scan QR & Pilih Minuman</p>
                    <p class="text-slate-400">2. Sebutkan nomor meja ke kasir saat pesan</p>
                    <p class="text-[9px] text-amber-700 font-bold pt-1">✨ Kumpulkan 10 Stamp = Gratis 1 Cup Minuman!</p>
                </div>

                <!-- Gunting Marker -->
                <div class="no-print w-full text-[9px] text-slate-300 pt-2 border-t border-slate-100 flex items-center justify-between">
                    <span>✂️ Gunting sesuai garis tepi</span>
                    <span>URL: {{ $tableUrl }}</span>
                </div>
            </div>
        @endfor
    </div>

</body>
</html>
