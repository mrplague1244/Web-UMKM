@extends('layouts.seller')

@section('title', 'Struk Transaksi - ' . $transaction->invoice_code)

@section('content')
<div class="max-w-md mx-auto space-y-4">
    <!-- Tombol Aksi di Layar Kasir (Mobile Friendly Grid) -->
    <div class="grid grid-cols-2 sm:flex sm:items-center sm:justify-between gap-2 no-print">
        <a href="{{ route('seller.pos') }}" class="px-3.5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition-all text-center flex items-center justify-center space-x-1 shadow-sm">
            <span>← Kasir POS</span>
        </a>

        @php
            $shareText = rawurlencode(
                "Halo! Berikut struk digital pesanan Anda di " . ($store['name'] ?? 'Kedai Minuman') . ":\n" .
                "No. Struk: " . $transaction->invoice_code . "\n" .
                "Total: Rp " . number_format($transaction->total_amount, 0, ',', '.') . "\n" .
                "Klaim " . $transaction->potential_points . " Poin Stamp Anda di link berikut:\n" .
                route('customer.claim', ['token' => $transaction->claim_token]) . "\n\n" .
                "Terima kasih atas kunjungannya! 😊"
            );
        @endphp

        <a href="https://wa.me/?text={{ $shareText }}" target="_blank"
           class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md transition-all text-center flex items-center justify-center space-x-1">
            <span>💬 Kirim ke WA</span>
        </a>

        <button onclick="window.print()" class="col-span-2 sm:col-span-1 px-3.5 py-2.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md transition-all text-center flex items-center justify-center space-x-1">
            <span>🖨️ Cetak Struk</span>
        </button>
    </div>

    <!-- Tampilan Struk Kasir Termal (Thermal Receipt 58mm/80mm) -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-md border border-slate-200 text-slate-800 font-mono text-xs space-y-4">
        <!-- Header Kedai -->
        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <h2 class="text-base font-bold tracking-tight text-slate-900">{{ $store['name'] ?? 'Kedai Kopi & Teh' }}</h2>
            <p class="text-[11px] text-slate-500">{{ $store['tagline'] ?? 'Minuman Autentik UMKM' }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">{{ $store['address'] ?? 'Jl. Pemuda No. 12' }} | Telp: {{ $store['phone'] ?? '-' }}</p>
        </div>

        <!-- Meta Transaksi -->
        <div class="space-y-1 text-[11px] pb-3 border-b border-dashed border-slate-300 text-slate-600">
            <div class="flex justify-between">
                <span>No. Struk:</span>
                <span class="font-bold text-slate-900">{{ $transaction->invoice_code }}</span>
            </div>
            <div class="flex justify-between">
                <span>Waktu:</span>
                <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span>{{ $transaction->cashier_name }}</span>
            </div>
            @if($transaction->customer)
                <div class="flex justify-between text-amber-800 font-semibold">
                    <span>Member:</span>
                    <span>{{ $transaction->customer->name }} ({{ $transaction->customer->tier }})</span>
                </div>
            @endif
        </div>

        <!-- Daftar Item Minuman -->
        <div class="space-y-2 pb-3 border-b border-dashed border-slate-300">
            @foreach($transaction->items as $item)
                <div>
                    <div class="flex justify-between font-bold text-slate-900">
                        <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-[10px] text-slate-500 pl-3 flex flex-wrap gap-1">
                        <span>• {{ $item->ice_level }}</span>
                        <span>• {{ $item->sugar_level }}</span>
                        @if($item->topping)
                            <span class="text-purple-700 font-semibold">• +{{ $item->topping }}</span>
                        @endif
                        @if($item->notes)
                            <span class="italic">• ({{ $item->notes }})</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Rincian Pembayaran -->
        <div class="space-y-1 text-[11px] pb-3 border-b border-dashed border-slate-300">
            <div class="flex justify-between font-extrabold text-sm text-slate-900 pt-1">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Pembayaran:</span>
                <span class="uppercase font-bold">{{ $transaction->payment_method }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Status:</span>
                @if($transaction->payment_status === 'paid')
                    <span class="text-emerald-700 font-bold uppercase">✅ LUNAS</span>
                @else
                    <span class="text-amber-600 font-bold uppercase">⏳ MENUNGGU BAYAR</span>
                @endif
            </div>
            @if($transaction->sumopod_fee > 0)
                <div class="flex justify-between text-slate-500">
                    <span>Biaya Gateway:</span>
                    <span>Rp {{ number_format($transaction->sumopod_fee, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($transaction->sumopod_expires_at && $transaction->payment_status !== 'paid')
                <div class="flex justify-between text-rose-600">
                    <span>Link Kadaluarsa:</span>
                    <span class="font-semibold">{{ $transaction->sumopod_expires_at->setTimezone('Asia/Jakarta')->format('H:i, d/m/Y') }}</span>
                </div>
            @endif
        </div>

        {{-- ===== BLOK SUMOPOD: hanya tampil untuk QRIS/Transfer yang belum lunas ===== --}}
        @if(in_array($transaction->payment_method, ['qris', 'transfer']) && $transaction->payment_status !== 'paid')
        <div class="no-print rounded-xl border-2 border-amber-400 bg-amber-50 p-4 space-y-3 text-center">
            <p class="text-xs font-bold text-amber-800 tracking-wide">
                📱 PEMBAYARAN {{ strtoupper($transaction->payment_method) }} — MENUNGGU KONFIRMASI
            </p>

            @if($transaction->sumopod_payment_url)
                {{-- QR Code dari URL Sumopod --}}
                <div class="flex justify-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($transaction->sumopod_payment_url) }}"
                         alt="QR Code Pembayaran"
                         class="w-44 h-44 rounded-lg border border-amber-200 shadow-sm">
                </div>

                <p class="text-[10px] text-slate-500">Scan QR Code atau klik link di bawah untuk membayar</p>

                <a href="{{ $transaction->sumopod_payment_url }}" target="_blank"
                   class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold text-xs rounded-xl shadow transition-all">
                    🔗 Buka Halaman Pembayaran Sumopod
                </a>

                {{-- Tombol Cek Status --}}
                <button onclick="checkPaymentStatus({{ $transaction->id }})"
                        id="btnCekStatus"
                        class="inline-flex items-center justify-center w-full py-2 px-4 bg-white border border-amber-400 hover:bg-amber-50 text-amber-800 font-bold text-xs rounded-xl transition-all">
                    🔄 Cek Status Pembayaran
                </button>
                <p id="statusCheckMsg" class="text-[10px] text-slate-400 hidden"></p>
            @else
                <p class="text-xs text-rose-600 font-semibold">⚠️ Link pembayaran tidak tersedia. Hubungi pengelola.</p>
            @endif
        </div>
        @endif
        {{-- ===== END BLOK SUMOPOD ===== --}}

        <!-- AREA KLAIM POIN LOYALITAS PELANGGAN (QR / TOKEN) -->
        <div class="text-center pt-2 pb-2 bg-amber-50/70 p-4 rounded-xl border border-amber-200">
            <span class="inline-block px-2 py-0.5 bg-amber-200 text-amber-900 font-bold text-[10px] rounded mb-1">
                🎁 REWARD LOYALTY PELANGGAN
            </span>
            <p class="text-[11px] text-slate-700 font-medium">
                Dapatkan <strong>{{ $transaction->potential_points }} Poin Stamp</strong> dari pesanan ini!
            </p>

            @if($transaction->payment_status !== 'paid')
                <p class="text-[10px] text-amber-700 font-semibold mt-1">
                    ⚠️ Poin akan diberikan otomatis setelah pembayaran dikonfirmasi.
                </p>
            @endif

            <!-- Box Kode Token Struk -->
            <div class="my-2.5 py-2 px-4 bg-white border-2 border-dashed border-amber-400 rounded-lg inline-block">
                <span class="text-[9px] text-slate-400 block tracking-wider font-sans">KODE KLAIM STRUK</span>
                <span class="text-xl font-black text-amber-900 tracking-widest font-mono">
                    {{ $transaction->claim_token }}
                </span>
            </div>

            <p class="text-[10px] text-slate-500">
                Klaim di: <strong class="text-amber-800 break-all">{{ route('customer.claim') }}</strong><br>
                Kumpulkan stamp untuk menukar 1 minuman gratis!
            </p>
        </div>

        <!-- Footer Ucapan -->
        <div class="text-center text-[10px] text-slate-400 pt-1">
            <p>Terima kasih atas kunjungannya!</p>
            <p class="italic">"Semoga harimu semakin segar dan bersemangat."</p>
        </div>
    </div>
</div>
@endsection

@if(in_array($transaction->payment_method, ['qris', 'transfer']) && $transaction->payment_status !== 'paid')
@push('scripts')
<script>
    // Auto-poll status pembayaran Sumopod setiap 5 detik
    let pollInterval = setInterval(() => checkPaymentStatus({{ $transaction->id }}, true), 5000);

    async function checkPaymentStatus(transactionId, isAuto = false) {
        const btn  = document.getElementById('btnCekStatus');
        const msg  = document.getElementById('statusCheckMsg');
        if (!isAuto && btn) btn.textContent = '🔄 Mengecek...';
        if (msg) msg.classList.remove('hidden');
        if (msg && !isAuto) msg.textContent = 'Menghubungi Sumopod...';

        try {
            const res  = await fetch(`/seller/pos/payment-status/{{ $transaction->id }}`);
            const data = await res.json();

            if (data.status === 'paid') {
                clearInterval(pollInterval);
                if (msg) msg.textContent = '✅ Pembayaran berhasil! Halaman akan diperbarui...';
                if (msg) msg.className = 'text-[10px] text-emerald-600 font-semibold';
                setTimeout(() => window.location.reload(), 1500);
            } else if (data.status === 'expired' || data.status === 'cancelled') {
                clearInterval(pollInterval);
                if (msg) msg.textContent = '❌ Pembayaran ' + data.status + '. Buat transaksi baru.';
                if (msg) msg.className = 'text-[10px] text-rose-600 font-semibold';
                if (btn) btn.disabled = true;
            } else {
                if (msg && !isAuto) msg.textContent = 'Status: Menunggu pembayaran dari pelanggan...';
                if (btn) btn.textContent = '🔄 Cek Status Pembayaran';
            }
        } catch (e) {
            if (msg && !isAuto) msg.textContent = 'Gagal menghubungi server.';
        }
        if (msg) msg.classList.remove('hidden');
    }
</script>
@endpush
@endif
