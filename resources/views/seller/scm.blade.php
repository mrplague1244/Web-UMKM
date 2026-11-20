@extends('layouts.seller')

@section('title', 'SCM & Manajemen Rantai Pasok')

@section('content')
<div class="space-y-6">
    <!-- Header SCM -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">📦 Supply Chain Management (SCM)</h1>
            <p class="text-xs text-slate-500">Kontrol stok bahan baku, resep racikan minuman, dan pembelian kulakan dari pemasok.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="openModal('modalMaterial')" class="px-3 py-2 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 text-xs font-bold rounded-xl shadow-sm transition-all">
                + Tambah Bahan Baku
            </button>
            <button onclick="openModal('modalSupplier')" class="px-3 py-2 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 text-xs font-bold rounded-xl shadow-sm transition-all">
                + Tambah Pemasok
            </button>
            <button onclick="openModal('modalProduct')" class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all">
                + Tambah Menu & Resep
            </button>
        </div>
    </div>

    <!-- Navigation Tabs SCM -->
    <div class="flex border-b border-slate-200 space-x-4 text-xs font-bold">
        <button type="button" onclick="switchTab('materials')" id="tabBtn-materials"
                class="tab-btn pb-3 border-b-2 border-amber-600 text-amber-700 flex items-center space-x-1.5">
            <span>🌾 Bahan Baku & Kemasan ({{ $materials->count() }})</span>
        </button>
        <button type="button" onclick="switchTab('restock')" id="tabBtn-restock"
                class="tab-btn pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center space-x-1.5">
            <span>🛒 Form Kulakan / Restock</span>
        </button>
        <button type="button" onclick="switchTab('recipes')" id="tabBtn-recipes"
                class="tab-btn pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center space-x-1.5">
            <span>📋 Resep & Menu Minuman ({{ $products->count() }})</span>
        </button>
        <button type="button" onclick="switchTab('suppliers')" id="tabBtn-suppliers"
                class="tab-btn pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center space-x-1.5">
            <span>🏭 Pemasok ({{ $suppliers->count() }})</span>
        </button>
        <button type="button" onclick="switchTab('movements')" id="tabBtn-movements"
                class="tab-btn pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center space-x-1.5">
            <span>🔄 Riwayat Mutasi Stok</span>
        </button>
    </div>

    <!-- TAB 1: BAHAN BAKU & KEMASAN -->
    <div id="tabContent-materials" class="tab-pane space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Inventaris Bahan Baku & Kemasan Kedai</h3>
                <span class="text-xs text-slate-500">Total: {{ $materials->count() }} item bahan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Kode/SKU</th>
                            <th class="py-3 px-4">Nama Bahan</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-right">Stok Saat Ini</th>
                            <th class="py-3 px-4 text-right">Batas Min. Stok</th>
                            <th class="py-3 px-4 text-right">HPP / Unit</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($materials as $m)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $m->sku ?: '-' }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $m->name }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ $m->category }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    {{ number_format($m->stock, 0, ',', '.') }} {{ $m->unit }}
                                </td>
                                <td class="py-3 px-4 text-right text-slate-500">
                                    {{ number_format($m->min_stock, 0, ',', '.') }} {{ $m->unit }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    Rp {{ number_format($m->cost_per_unit, 0, ',', '.') }}/{{ $m->unit }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($m->stock <= 0)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Habis</span>
                                    @elseif($m->stock <= $m->min_stock)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">Menipis</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Aman</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: FORM KULAKAN / RESTOCK -->
    <div id="tabContent-restock" class="tab-pane hidden space-y-4">
        <div class="max-w-2xl bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="border-b border-slate-100 pb-3 mb-4">
                <h3 class="font-bold text-slate-900 text-base">Kulakan Bahan Baku dari Pemasok</h3>
                <p class="text-xs text-slate-500">Mencatat penambahan stok di SCM dan otomatis membukukan kas keluar di ERP.</p>
            </div>

            <form action="{{ route('seller.scm.restock') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pilih Bahan Baku *</label>
                        <select name="material_id" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                            <option value="">-- Pilih Bahan --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" data-cost="{{ $m->cost_per_unit }}" data-unit="{{ $m->unit }}">
                                    {{ $m->name }} (Sisa: {{ $m->stock }} {{ $m->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pilih Pemasok (Supplier)</label>
                        <select name="supplier_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                            <option value="">-- Supplier Umum / Beli Tunai --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->category }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jumlah / Kuantitas Masuk *</label>
                        <input type="number" step="any" name="quantity" id="restockQty" required placeholder="Contoh: 100"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500"
                               oninput="calculateRestockTotal()">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Harga Beli Satuan (Rp) *</label>
                        <input type="number" step="any" name="unit_cost" id="restockCost" required placeholder="Contoh: 450"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500"
                               oninput="calculateRestockTotal()">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Referensi / Nota (Opsional)</label>
                        <input type="text" name="reference_no" placeholder="Contoh: NOTA-123 / PO-01"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" placeholder="Contoh: Beli 2 dus cup sablon"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                    </div>
                </div>

                <!-- Estimasi Total Kas Keluar -->
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-amber-900 block">Total Pengeluaran Kas (ERP):</span>
                        <span class="text-[10px] text-amber-700">Akan otomatis tercatat di Buku Kas Keluar</span>
                    </div>
                    <span id="restockTotalText" class="text-base font-extrabold text-amber-950">Rp 0</span>
                </div>

                <button type="submit" class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md transition-all">
                    Konfirmasi Kulakan & Masukkan Stok →
                </button>
            </form>
        </div>
    </div>

    <!-- TAB 3: RESEP & MENU MINUMAN -->
    <div id="tabContent-recipes" class="tab-pane hidden space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($products as $p)
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                            {{ $p->category }}
                        </span>
                        <span class="text-xs font-bold text-slate-900">
                            Rp {{ number_format($p->selling_price, 0, ',', '.') }}
                        </span>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ $p->name }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $p->description ?: 'Menu minuman racikan.' }}</p>
                    </div>

                    <!-- Komposisi Resep (BOM) -->
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
                            Bahan Resep per Cup:
                        </span>
                        @if($p->recipes->isNotEmpty())
                            <ul class="space-y-1 text-xs">
                                @foreach($p->recipes as $rcp)
                                    <li class="flex justify-between text-slate-600">
                                        <span>• {{ $rcp->material?->name ?? 'Bahan' }}</span>
                                        <span class="font-bold text-slate-800">{{ $rcp->quantity }} {{ $rcp->material?->unit ?? '' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-xs text-slate-400 italic">Belum ada resep bahan baku.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 4: PEMASOK / SUPPLIERS -->
    <div id="tabContent-suppliers" class="tab-pane hidden space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($suppliers as $sup)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">
                            {{ $sup->category }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $sup->stock_movements_count }}x Transaksi</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ $sup->name }}</h4>
                        <p class="text-xs text-slate-600 mt-1">📞 {{ $sup->phone ?: '-' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">📍 {{ $sup->address ?: '-' }}</p>
                    </div>
                    @if($sup->notes)
                        <p class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100">
                            {{ $sup->notes }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 5: RIWAYAT MUTASI STOK -->
    <div id="tabContent-movements" class="tab-pane hidden space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Audit Trail Mutasi Stok Bahan Baku</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Bahan Baku</th>
                            <th class="py-3 px-4">Tipe</th>
                            <th class="py-3 px-4 text-right">Kuantitas</th>
                            <th class="py-3 px-4">Referensi / Pemasok</th>
                            <th class="py-3 px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentMovements as $mv)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 text-slate-500">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $mv->material?->name ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    @if($mv->type === 'in')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">+ Masuk</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">- Keluar</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    {{ number_format($mv->quantity, 0, ',', '.') }} {{ $mv->material?->unit }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $mv->supplier?->name ?: ($mv->reference_no ?: '-') }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">{{ $mv->notes ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH BAHAN BAKU -->
<div id="modalMaterial" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">+ Tambah Bahan Baku / Packaging Baru</h3>
            <button onclick="closeModal('modalMaterial')" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
        </div>
        <form action="{{ route('seller.scm.material') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Bahan *</label>
                <input type="text" name="name" required placeholder="Contoh: Biji Kopi Arabika"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
                    <select name="category" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                        <option value="Bahan Baku">Bahan Baku</option>
                        <option value="Packaging">Packaging</option>
                        <option value="Sirup & Pemanis">Sirup & Pemanis</option>
                        <option value="Topping">Topping</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Satuan Unit *</label>
                    <input type="text" name="unit" required placeholder="pcs, gram, ml"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Stok Awal</label>
                    <input type="number" step="any" name="stock" value="0" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Min. Stok</label>
                    <input type="number" step="any" name="min_stock" value="10" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">HPP / Unit</label>
                    <input type="number" step="any" name="cost_per_unit" value="0" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
            </div>
            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalMaterial')" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Bahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH PEMASOK -->
<div id="modalSupplier" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">+ Tambah Pemasok Baru</h3>
            <button onclick="closeModal('modalSupplier')" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
        </div>
        <form action="{{ route('seller.scm.supplier') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Perusahaan / Toko *</label>
                <input type="text" name="name" required placeholder="Contoh: CV Kemasan Sejahtera"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">No. WhatsApp</label>
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori Pemasok</label>
                    <input type="text" name="category" placeholder="Contoh: Kemasan Cup"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Alamat</label>
                <input type="text" name="address" placeholder="Kota / Alamat Toko"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Catatan Produk</label>
                <input type="text" name="notes" placeholder="Contoh: Menyediakan cup 16oz dan sedotan steril"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalSupplier')" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Pemasok</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-amber-600', 'text-amber-700');
            btn.classList.add('border-transparent', 'text-slate-500');
        });

        document.getElementById('tabContent-' + tabId).classList.remove('hidden');
        const activeBtn = document.getElementById('tabBtn-' + tabId);
        activeBtn.classList.remove('border-transparent', 'text-slate-500');
        activeBtn.classList.add('border-amber-600', 'text-amber-700');
    }

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function calculateRestockTotal() {
        const qty = Number(document.getElementById('restockQty').value) || 0;
        const cost = Number(document.getElementById('restockCost').value) || 0;
        const total = qty * cost;
        document.getElementById('restockTotalText').innerText = 'Rp ' + Number(total).toLocaleString('id-ID');
    }
</script>
@endpush
@endsection
