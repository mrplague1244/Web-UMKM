@extends('layouts.seller')

@section('title', 'CRM & Program Loyalitas Pelanggan')

@section('content')
<div class="space-y-6">
    <!-- Header CRM -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">👥 Customer Relationship Management (CRM)</h1>
            <p class="text-xs text-slate-500">Database member, program kartu stamp loyalitas, dan pemasaran langsung via WhatsApp.</p>
        </div>
        <button onclick="openModal('modalCustomer')" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center space-x-1 self-start sm:self-auto">
            <span>+ Tambah Member Baru</span>
        </button>
    </div>

    <!-- KARTU METRIK MEMBER & LOYALITAS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Pelanggan Terdaftar</span>
            <span class="text-2xl font-black text-slate-900 block mt-1">{{ $totalCustomers }} Orang</span>
            <span class="text-[11px] text-slate-500 mt-2 block border-t border-slate-100 pt-1">Pelanggan setia kedai</span>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Poin / Stamp Beredar</span>
            <span class="text-2xl font-black text-amber-700 block mt-1">{{ number_format($totalPointsIssued, 0, ',', '.') }} Poin</span>
            <span class="text-[11px] text-slate-500 mt-2 block border-t border-slate-100 pt-1">Potensi klaim minuman gratis</span>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Member VIP & Gold</span>
            <span class="text-2xl font-black text-indigo-700 block mt-1">{{ $vipCount + $goldCount }} Orang</span>
            <span class="text-[11px] text-slate-500 mt-2 block border-t border-slate-100 pt-1">Pelanggan dengan frekuensi tertinggi</span>
        </div>

        <div class="bg-gradient-to-tr from-emerald-600 to-teal-800 rounded-2xl p-4 text-white shadow-md">
            <span class="text-[11px] font-bold text-emerald-200 uppercase tracking-wider block">Aturan Stamp Gratis</span>
            <span class="text-2xl font-black text-white block mt-1">10 Stamp = Free 1 Cup</span>
            <span class="text-[11px] text-emerald-200 mt-2 block border-t border-emerald-500/50 pt-1">Tiap Rp 10.000 = 1 Stamp</span>
        </div>
    </div>

    <!-- TABEL DATABASE MEMBER PELANGGAN -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Daftar Member & Loyalitas Kedai</h3>
            <span class="text-xs text-slate-500">Urut berdasarkan akumulasi poin tertinggi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Pelanggan</th>
                        <th class="py-3 px-4">No. WhatsApp</th>
                        <th class="py-3 px-4 text-center">Tingkatan (Tier)</th>
                        <th class="py-3 px-4 text-center">Total Belanja</th>
                        <th class="py-3 px-4 text-center">Total Poin / Stamp</th>
                        <th class="py-3 px-4 text-right">Aksi & Pemasaran WA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                        @php
                            // Format pesan promo WhatsApp
                            $cleanPhone = preg_replace('/[^0-9]/', '', $c->phone);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                            $msgThankYou = rawurlencode(str_replace(
                                ['{name}', '{store}', '{points}'],
                                [$c->name, config('beverage.store.name'), $c->total_points],
                                $waTemplates['thank_you'] ?? 'Terima kasih telah berbelanja!'
                            ));
                            $msgPromo = rawurlencode(str_replace(
                                ['{name}', '{store}'],
                                [$c->name, config('beverage.store.name')],
                                $waTemplates['promo_friday'] ?? 'Ada promo menarik untuk Anda!'
                            ));
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('seller.crm.detail', $c->id) }}" class="hover:text-amber-700 underline">
                                    {{ $c->name }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $c->phone }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($c->tier === 'VIP')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-900 border border-purple-200">
                                        ⭐ VIP
                                    </span>
                                @elseif($c->tier === 'Gold')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-200">
                                        🥇 Gold
                                    </span>
                                @elseif($c->tier === 'Silver')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-200 text-slate-800 border border-slate-300">
                                        🥈 Silver
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-900 border border-orange-200">
                                        🥉 Bronze
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">
                                {{ $c->transactions_count }}x Kunjungan
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-800 font-extrabold rounded-lg border border-amber-200">
                                    {{ $c->total_points }} Stamp
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-1">
                                <!-- Tombol 1-Click WhatsApp Sapa & Terima Kasih -->
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $msgThankYou }}" target="_blank"
                                   class="inline-flex px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-lg text-[10px] font-bold transition-all"
                                   title="Kirim Terima Kasih & Info Poin">
                                    💬 WA Sapa
                                </a>

                                <!-- Tombol 1-Click WhatsApp Promo -->
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $msgPromo }}" target="_blank"
                                   class="inline-flex px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-[10px] font-bold transition-all"
                                   title="Kirim Pesan Promo Minuman">
                                    📢 Promo
                                </a>

                                <a href="{{ route('seller.crm.detail', $c->id) }}"
                                   class="inline-flex px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[10px] font-bold transition-all">
                                    Rincian →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada pelanggan yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PELANGGAN MANUAL -->
<div id="modalCustomer" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">+ Tambah Member Baru Kedai</h3>
            <button onclick="closeModal('modalCustomer')" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
        </div>
        <form action="{{ route('seller.crm.customer') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Pelanggan *</label>
                <input type="text" name="name" required placeholder="Contoh: Rina Amelia"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp *</label>
                <input type="text" name="phone" required placeholder="08xxxxxxxxxx"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Email (Opsional)</label>
                <input type="email" name="email" placeholder="nama@gmail.com"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalCustomer')" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Member</button>
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
