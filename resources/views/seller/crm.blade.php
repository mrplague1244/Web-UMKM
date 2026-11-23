@extends('layouts.seller')

@section('title', 'CRM & Program Loyalitas Pelanggan')

@section('content')
<div class="space-y-5">
    <!-- Header CRM -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">👥 Customer Relationship Management (CRM)</h1>
            <p class="text-[11px] sm:text-xs text-slate-500">Database member, program kartu stamp loyalitas, dan pemasaran langsung via WhatsApp.</p>
        </div>
        <button onclick="openModal('modalCustomer')" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center space-x-1 self-start sm:self-auto active:scale-95">
            <span>+ Tambah Member Baru</span>
        </button>
    </div>

    <!-- KARTU METRIK MEMBER & LOYALITAS (2x2 di Smartphone, 4 Kolom di Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Pelanggan</span>
            <span class="text-lg sm:text-2xl font-black text-slate-900 block mt-1">{{ $totalCustomers }} Orang</span>
            <span class="text-[10px] sm:text-[11px] text-slate-500 mt-1.5 block border-t border-slate-100 pt-1">Member terdaftar</span>
        </div>

        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Poin / Stamp Beredar</span>
            <span class="text-lg sm:text-2xl font-black text-amber-700 block mt-1">{{ number_format($totalPointsIssued, 0, ',', '.') }}</span>
            <span class="text-[10px] sm:text-[11px] text-slate-500 mt-1.5 block border-t border-slate-100 pt-1">Potensi reward gratis</span>
        </div>

        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">VIP & Gold</span>
            <span class="text-lg sm:text-2xl font-black text-indigo-700 block mt-1">{{ $vipCount + $goldCount }} Member</span>
            <span class="text-[10px] sm:text-[11px] text-slate-500 mt-1.5 block border-t border-slate-100 pt-1">Pelanggan setia</span>
        </div>

        <div class="bg-gradient-to-tr from-emerald-600 to-teal-800 rounded-2xl p-3 sm:p-4 text-white shadow-md flex flex-col justify-between">
            <span class="text-[10px] sm:text-[11px] font-bold text-emerald-200 uppercase tracking-wider block">Target Hadiah</span>
            <span class="text-base sm:text-xl font-black text-white block mt-1">10 Stamp = Free 1 Cup</span>
            <span class="text-[10px] sm:text-[11px] text-emerald-200 mt-1.5 block border-t border-emerald-500/50 pt-1">Rp 10.000 = 1 Poin</span>
        </div>
    </div>

    <!-- TAMPILAN MOBILE CARD VIEW (Khusus Layar Smartphone) -->
    <div class="block sm:hidden space-y-3">
        <h3 class="font-bold text-slate-900 text-xs px-1">Daftar Member (Mobile Card View)</h3>
        @forelse($customers as $c)
            @php
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
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-2.5">
                <div class="flex justify-between items-start">
                    <div>
                        <a href="{{ route('seller.crm.detail', $c->id) }}" class="font-bold text-slate-900 text-sm hover:text-amber-700 underline block">
                            {{ $c->name }}
                        </a>
                        <span class="text-xs font-mono text-slate-500">{{ $c->phone }}</span>
                    </div>
                    <div class="text-right">
                        @if($c->tier === 'VIP')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-900 border border-purple-200">⭐ VIP</span>
                        @elseif($c->tier === 'Gold')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-200">🥇 Gold</span>
                        @elseif($c->tier === 'Silver')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-200 text-slate-800 border border-slate-300">🥈 Silver</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-900 border border-orange-200">🥉 Bronze</span>
                        @endif
                    </div>
                </div>

                <div class="flex justify-between items-center text-xs py-1.5 border-y border-slate-100 text-slate-600">
                    <span>Total Belanja: <strong>{{ $c->transactions_count }}x Kunjungan</strong></span>
                    <span>Stamp: <strong class="text-amber-700">{{ $c->total_points }} Poin</strong></span>
                </div>

                <!-- Tombol Aksi WhatsApp Mobile Ramah Sentuhan -->
                <div class="grid grid-cols-3 gap-1.5 pt-1">
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $msgThankYou }}" target="_blank"
                       class="py-2 bg-emerald-50 hover:bg-emerald-100 active:scale-95 text-emerald-800 border border-emerald-300 rounded-xl text-[11px] font-bold text-center transition-all">
                        💬 WA Sapa
                    </a>
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $msgPromo }}" target="_blank"
                       class="py-2 bg-amber-50 hover:bg-amber-100 active:scale-95 text-amber-800 border border-amber-300 rounded-xl text-[11px] font-bold text-center transition-all">
                        📢 Promo
                    </a>
                    <a href="{{ route('seller.crm.detail', $c->id) }}"
                       class="py-2 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 rounded-xl text-[11px] font-bold text-center transition-all">
                        Rincian →
                    </a>
                </div>
                <div class="flex justify-end items-center space-x-2 pt-1">
                    <button type="button" onclick="openEditCustomerModal({{ json_encode($c) }})" class="text-amber-600 hover:text-amber-800 text-[11px] font-bold flex items-center space-x-0.5">
                        <span>✏️ Edit</span>
                    </button>
                    <form action="{{ route('seller.crm.customer.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Hapus member {{ $c->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-500 hover:text-rose-700 text-[11px] font-bold flex items-center space-x-0.5">
                            <span>🗑️ Hapus</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white p-6 rounded-2xl border border-slate-200 text-center text-xs text-slate-400">
                Belum ada member terdaftar.
            </div>
        @endforelse
    </div>

    <!-- TABEL DATABASE MEMBER (Khusus Layar Tablet & Desktop) -->
    <div class="hidden sm:block bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
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
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $msgThankYou }}" target="_blank"
                                   class="inline-flex px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-lg text-[10px] font-bold transition-all">
                                    💬 WA Sapa
                                </a>
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $msgPromo }}" target="_blank"
                                   class="inline-flex px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-[10px] font-bold transition-all">
                                    📢 Promo
                                </a>
                                <a href="{{ route('seller.crm.detail', $c->id) }}"
                                   class="inline-flex px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[10px] font-bold transition-all">
                                    Rincian →
                                </a>
                                <button type="button" onclick="openEditCustomerModal({{ json_encode($c) }})" class="px-1.5 py-1 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg text-[10px] font-bold transition-all" title="Edit Member">
                                    ✏️
                                </button>
                                <form action="{{ route('seller.crm.customer.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Hapus member {{ $c->name }}?')" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-1.5 py-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg text-[10px] font-bold transition-all" title="Hapus Member">
                                        🗑️
                                    </button>
                                </form>
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

<!-- MODAL TAMBAH PELANGGAN (Mobile Bottom Sheet & Desktop) -->
<div id="modalCustomer" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-3.5 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <h3 class="text-sm font-bold text-slate-900">+ Tambah Member Baru Kedai</h3>
            <button onclick="closeModal('modalCustomer')" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
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
                <button type="button" onclick="closeModal('modalCustomer')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Member</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT PELANGGAN (Mobile Bottom Sheet & Desktop) -->
<div id="modalEditCustomer" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-3.5 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <h3 class="text-sm font-bold text-slate-900">✏️ Edit Data Member Kedai</h3>
            <button onclick="closeModal('modalEditCustomer')" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
        </div>
        <form id="formEditCustomer" action="" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Pelanggan *</label>
                <input type="text" name="name" id="editCustomerName" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp *</label>
                <input type="text" name="phone" id="editCustomerPhone" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Email (Opsional)</label>
                <input type="email" name="email" id="editCustomerEmail"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalEditCustomer')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Perubahan</button>
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
    function openEditCustomerModal(customer) {
        document.getElementById('formEditCustomer').action = '/seller/crm/customer/' + customer.id;
        document.getElementById('editCustomerName').value = customer.name;
        document.getElementById('editCustomerPhone').value = customer.phone;
        document.getElementById('editCustomerEmail').value = customer.email || '';
        openModal('modalEditCustomer');
    }
</script>
@endpush
@endsection
