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
                <span class="text-emerald-700 font-bold uppercase">LUNAS</span>
            </div>
        </div>

        <!-- AREA KLAIM POIN LOYALITAS PELANGGAN (QR / TOKEN) -->
        <div class="text-center pt-2 pb-2 bg-amber-50/70 p-4 rounded-xl border border-amber-200">
            <span class="inline-block px-2 py-0.5 bg-amber-200 text-amber-900 font-bold text-[10px] rounded mb-1">
                🎁 REWARD LOYALTY PELANGGAN
            </span>
            <p class="text-[11px] text-slate-700 font-medium">
                Dapatkan <strong>{{ $transaction->potential_points }} Poin Stamp</strong> dari pesanan ini!
            </p>

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
