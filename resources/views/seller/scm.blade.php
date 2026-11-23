@extends('layouts.seller')

@section('title', 'SCM & Manajemen Rantai Pasok')

@section('content')
<div class="space-y-5">
    <!-- Header SCM -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">📦 Supply Chain Management (SCM)</h1>
            <p class="text-[11px] sm:text-xs text-slate-500">Kontrol stok bahan baku, resep racikan minuman, dan pembelian kulakan dari pemasok.</p>
        </div>
        <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 no-scrollbar -mx-1 px-1">
            <button onclick="openCreateMaterialModal()" class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 text-[11px] sm:text-xs font-bold rounded-xl shadow-sm transition-all whitespace-nowrap">
                + Tambah Bahan
            </button>
            <button onclick="openCreateSupplierModal()" class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 text-[11px] sm:text-xs font-bold rounded-xl shadow-sm transition-all whitespace-nowrap">
                + Tambah Pemasok
            </button>
            <button onclick="openCreateProductModal()" class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-amber-600 hover:bg-amber-700 text-white text-[11px] sm:text-xs font-bold rounded-xl shadow-sm transition-all whitespace-nowrap">
                + Tambah Menu
            </button>
        </div>
    </div>

    <!-- Navigation Tabs SCM (Swipeable Horizontal Scroll on Mobile) -->
    <div class="flex border-b border-slate-200 space-x-2 sm:space-x-4 text-xs font-bold overflow-x-auto pb-1 no-scrollbar -mx-1 px-1">
        <button type="button" onclick="switchTab('materials')" id="tabBtn-materials"
                class="tab-btn pb-2.5 sm:pb-3 border-b-2 border-amber-600 text-amber-700 whitespace-nowrap flex items-center space-x-1">
            <span>🌾 Bahan Baku ({{ $materials->count() }})</span>
        </button>
        <button type="button" onclick="switchTab('restock')" id="tabBtn-restock"
                class="tab-btn pb-2.5 sm:pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 whitespace-nowrap flex items-center space-x-1">
            <span>🛒 Kulakan / Restock</span>
        </button>
        <button type="button" onclick="switchTab('recipes')" id="tabBtn-recipes"
                class="tab-btn pb-2.5 sm:pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 whitespace-nowrap flex items-center space-x-1">
            <span>📋 Resep Minuman ({{ $products->count() }})</span>
        </button>
        <button type="button" onclick="switchTab('suppliers')" id="tabBtn-suppliers"
                class="tab-btn pb-2.5 sm:pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 whitespace-nowrap flex items-center space-x-1">
            <span>🏭 Pemasok ({{ $suppliers->count() }})</span>
        </button>
        <button type="button" onclick="switchTab('movements')" id="tabBtn-movements"
                class="tab-btn pb-2.5 sm:pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 whitespace-nowrap flex items-center space-x-1">
            <span>🔄 Mutasi Stok</span>
        </button>
    </div>

    <!-- TAB 1: BAHAN BAKU & KEMASAN -->
    <div id="tabContent-materials" class="tab-pane space-y-4">
        <!-- Tampilan Kartu Ringkas di Layar HP (Mobile Card View) -->
        <div class="sm:hidden space-y-2.5">
            @foreach($materials as $m)
                <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[9px] font-mono text-slate-400 block">{{ $m->sku ?: '-' }}</span>
                            <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $m->name }}</h4>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-slate-100 text-slate-600 mt-1 inline-block">
                                {{ $m->category }}
                            </span>
                        </div>
                        <div class="text-right">
                            @if($m->stock <= 0)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Habis</span>
                            @elseif($m->stock <= $m->min_stock)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">Menipis</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Aman</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-xs pt-2 border-t border-slate-100 text-slate-600">
                        <span>Sisa: <strong class="text-slate-900 font-extrabold">{{ number_format($m->stock, 0, ',', '.') }} {{ $m->unit }}</strong></span>
                        <span>Min: {{ number_format($m->min_stock, 0, ',', '.') }} {{ $m->unit }}</span>
                        <span>HPP: Rp {{ number_format($m->cost_per_unit, 0, ',', '.') }}</span>
                        <div class="flex items-center space-x-1">
                            <button type="button" onclick="openEditMaterialModal({{ json_encode($m) }})" class="text-amber-600 hover:text-amber-800 text-xs font-bold p-1" title="Edit Bahan Baku">✏️</button>
                            <form action="{{ route('seller.scm.material.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus bahan baku {{ $m->name }}?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold p-1" title="Hapus Bahan">🗑️</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tabel Lengkap di Tablet & Desktop -->
        <div class="hidden sm:block bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
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
                            <th class="py-3 px-4 text-center">Aksi</th>
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
                                <td class="py-3 px-4 text-center space-x-1">
                                    <button type="button" onclick="openEditMaterialModal({{ json_encode($m) }})" class="p-1 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded transition-all" title="Edit Bahan Baku">
                                        ✏️
                                    </button>
                                    <form action="{{ route('seller.scm.material.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus bahan baku {{ $m->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition-all" title="Hapus Bahan Baku">
                                            🗑️
                                        </button>
                                    </form>
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
        <div class="max-w-2xl bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
            <div class="border-b border-slate-100 pb-3 mb-4">
                <h3 class="font-bold text-slate-900 text-base">Kulakan Bahan Baku dari Pemasok</h3>
                <p class="text-xs text-slate-500">Mencatat penambahan stok di SCM dan otomatis membukukan kas keluar di ERP.</p>
            </div>

            <form action="{{ route('seller.scm.restock') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
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
                            <option value="">-- Beli Tunai / Pasar --</option>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">No. Referensi / Nota (Opsional)</label>
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
                        <span class="text-[10px] text-amber-700">Otomatis tercatat di Buku Kas Keluar</span>
                    </div>
                    <span id="restockTotalText" class="text-sm sm:text-base font-extrabold text-amber-950">Rp 0</span>
                </div>

                <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md transition-all active:scale-95">
                    Konfirmasi Kulakan & Masukkan Stok →
                </button>
            </form>
        </div>
    </div>

    <!-- TAB 3: RESEP & MENU MINUMAN -->
    <div id="tabContent-recipes" class="tab-pane hidden space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
            @foreach($products as $p)
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">
                            {{ $p->category }}
                        </span>
                        <div class="flex items-center space-x-1.5">
                            <span class="text-xs font-bold text-slate-900">
                                Rp {{ number_format($p->selling_price, 0, ',', '.') }}
                            </span>
                            <button type="button" onclick="openEditProductModal({{ json_encode($p) }})" class="p-1 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded text-xs transition-all" title="Edit Menu & Resep">
                                ✏️
                            </button>
                            <form action="{{ route('seller.scm.product.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus menu {{ $p->name }}?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 hover:bg-rose-50 rounded text-xs transition-all" title="Hapus Menu Minuman">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ $p->name }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $p->description ?: 'Menu minuman racikan.' }}</p>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
                            Resep Takaran per Cup:
                        </span>
                        @if($p->recipes->isNotEmpty())
                            <ul class="space-y-1 text-xs">
                                @foreach($p->recipes as $rcp)
                                    <li class="flex justify-between text-slate-600 text-[11px]">
                                        <span>• {{ $rcp->material?->name ?? 'Bahan' }}</span>
                                        <span class="font-bold text-slate-800">{{ $rcp->quantity }} {{ $rcp->material?->unit ?? '' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-[11px] text-slate-400 italic">Belum ada resep bahan baku.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 4: PEMASOK / SUPPLIERS -->
    <div id="tabContent-suppliers" class="tab-pane hidden space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
            @foreach($suppliers as $sup)
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-100 text-indigo-800">
                            {{ $sup->category }}
                        </span>
                        <div class="flex items-center space-x-1.5">
                            <span class="text-[11px] text-slate-400">{{ $sup->stock_movements_count }}x Transaksi</span>
                            <button type="button" onclick="openEditSupplierModal({{ json_encode($sup) }})" class="p-1 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded text-xs transition-all" title="Edit Pemasok">
                                ✏️
                            </button>
                            <form action="{{ route('seller.scm.supplier.destroy', $sup->id) }}" method="POST" onsubmit="return confirm('Hapus pemasok {{ $sup->name }}?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 hover:bg-rose-50 rounded text-xs transition-all" title="Hapus Pemasok">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ $sup->name }}</h4>
                        <p class="text-xs text-slate-600 mt-1">📞 {{ $sup->phone ?: '-' }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">📍 {{ $sup->address ?: '-' }}</p>
                    </div>
                    @if($sup->notes)
                        <p class="text-[10px] text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100">
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
            <div class="p-3.5 sm:p-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Audit Trail Mutasi Stok Bahan Baku</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4">Waktu</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4">Bahan Baku</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4">Tipe</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4 text-right">Kuantitas</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4">Referensi / Pemasok</th>
                            <th class="py-2.5 sm:py-3 px-3 sm:px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentMovements as $mv)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 text-slate-500">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold text-slate-900">{{ $mv->material?->name ?? '-' }}</td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4">
                                    @if($mv->type === 'in')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">+ Masuk</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">- Keluar</span>
                                    @endif
                                </td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 text-right font-bold text-slate-900">
                                    {{ number_format($mv->quantity, 0, ',', '.') }} {{ $mv->material?->unit }}
                                </td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 text-slate-600">
                                    {{ $mv->supplier?->name ?: ($mv->reference_no ?: '-') }}
                                </td>
                                <td class="py-2.5 sm:py-3 px-3 sm:px-4 text-slate-500">{{ $mv->notes ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT BAHAN BAKU (Mobile Bottom Sheet & Desktop) -->
<div id="modalMaterial" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-3.5 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <h3 id="materialModalTitle" class="text-sm font-bold text-slate-900">+ Tambah Bahan Baku / Packaging Baru</h3>
            <button onclick="closeModal('modalMaterial')" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
        </div>
        <form action="{{ route('seller.scm.material') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <input type="hidden" name="id" id="materialId">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Bahan *</label>
                <input type="text" name="name" id="materialName" required placeholder="Contoh: Biji Kopi Arabika"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
                    <select name="category" id="materialCategory" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                        <option value="Bahan Baku">Bahan Baku</option>
                        <option value="Packaging">Packaging</option>
                        <option value="Sirup & Pemanis">Sirup & Pemanis</option>
                        <option value="Topping">Topping</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Satuan Unit *</label>
                    <input type="text" name="unit" id="materialUnit" required placeholder="pcs, gram, ml"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Stok Awal</label>
                    <input type="number" step="any" name="stock" id="materialStock" value="0" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Min. Stok</label>
                    <input type="number" step="any" name="min_stock" id="materialMinStock" value="10" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">HPP / Unit</label>
                    <input type="number" step="any" name="cost_per_unit" id="materialCostPerUnit" value="0" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
            </div>
            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalMaterial')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Bahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT PEMASOK (Mobile Bottom Sheet & Desktop) -->
<div id="modalSupplier" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-3.5 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <h3 id="supplierModalTitle" class="text-sm font-bold text-slate-900">+ Tambah Pemasok Baru</h3>
            <button onclick="closeModal('modalSupplier')" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
        </div>
        <form action="{{ route('seller.scm.supplier') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <input type="hidden" name="id" id="supplierId">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Perusahaan / Toko *</label>
                <input type="text" name="name" id="supplierName" required placeholder="Contoh: CV Kemasan Sejahtera"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">No. WhatsApp</label>
                    <input type="text" name="phone" id="supplierPhone" placeholder="08xxxxxxxxxx"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori Pemasok</label>
                    <input type="text" name="category" id="supplierCategory" placeholder="Contoh: Kemasan Cup"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Alamat</label>
                <input type="text" name="address" id="supplierAddress" placeholder="Kota / Alamat Toko"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Catatan Produk</label>
                <input type="text" name="notes" id="supplierNotes" placeholder="Contoh: Menyediakan cup 16oz dan sedotan steril"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalSupplier')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Pemasok</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT MENU MINUMAN (Mobile Bottom Sheet & Desktop) -->
<div id="modalProduct" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-3.5 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
            <h3 id="productModalTitle" class="text-sm font-bold text-slate-900">+ Tambah Menu Minuman & Resep Baru</h3>
            <button onclick="closeModal('modalProduct')" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
        </div>
        <form action="{{ route('seller.scm.product') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <input type="hidden" name="id" id="productId">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Minuman *</label>
                <input type="text" name="name" id="productName" required placeholder="Contoh: Es Kopi Susu Aren"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
                    <select name="category" id="productCategory" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                        @foreach($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}">{{ $catKey }} ({{ $catLabel }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kode SKU (Opsional)</label>
                    <input type="text" name="sku" id="productSku" placeholder="DRK-XXX"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Harga Jual (Rp) *</label>
                    <input type="number" name="selling_price" id="productSellingPrice" required placeholder="Contoh: 18000"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500 font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Estimasi HPP (Rp, Opsional)</label>
                    <input type="number" name="cost_price" id="productCostPrice" placeholder="Dihitung otomatis jika ada resep"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Deskripsi Menu</label>
                <input type="text" name="description" id="productDescription" placeholder="Perpaduan espresso arabika dengan susu segar & gula aren asli"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-1 focus:ring-amber-500">
            </div>

            <!-- Resep Racikan Bahan Baku -->
            <div class="pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <span class="font-bold text-slate-800 block text-xs">Resep Takaran Bahan per Cup:</span>
                        <span class="text-[10px] text-slate-400">Stok bahan akan otomatis dipotong saat pesanan kasir checkout.</span>
                    </div>
                    <button type="button" onclick="addRecipeRow()" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 text-[10px] font-bold rounded-lg border border-amber-200 transition-all">
                        + Tambah Bahan
                    </button>
                </div>
                <div id="recipeRowsContainer" class="space-y-2 max-h-40 overflow-y-auto pr-1">
                    <div class="recipe-row flex items-center space-x-2">
                        <select name="recipes[0][material_id]" class="flex-1 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs">
                            <option value="">-- Pilih Bahan --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit }})</option>
                            @endforeach
                        </select>
                        <input type="number" step="any" name="recipes[0][quantity]" placeholder="Takaran" class="w-24 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs">
                        <button type="button" onclick="removeRecipeRow(this)" class="text-rose-500 hover:text-rose-700 font-bold text-xs p-1">✕</button>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex space-x-2">
                <button type="button" onclick="closeModal('modalProduct')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let recipeRowCount = 1;
    const materialOptions = `@foreach($materials as $m)<option value="{{ $m->id }}">{{ addslashes($m->name) }} ({{ $m->unit }})</option>@endforeach`;

    function addRecipeRow(matId = '', qty = '') {
        const container = document.getElementById('recipeRowsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'recipe-row flex items-center space-x-2 mt-2';
        newRow.innerHTML = `
            <select name="recipes[${recipeRowCount}][material_id]" class="flex-1 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs">
                <option value="">-- Pilih Bahan --</option>
                ${materialOptions}
            </select>
            <input type="number" step="any" name="recipes[${recipeRowCount}][quantity]" value="${qty}" placeholder="Takaran" class="w-24 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs">
            <button type="button" onclick="removeRecipeRow(this)" class="text-rose-500 hover:text-rose-700 font-bold text-xs p-1">✕</button>
        `;
        container.appendChild(newRow);
        if (matId) {
            newRow.querySelector('select').value = matId;
        }
        recipeRowCount++;
    }

    function removeRecipeRow(btn) {
        const rows = document.querySelectorAll('.recipe-row');
        if (rows.length > 1) {
            btn.closest('.recipe-row').remove();
        } else {
            const inputs = btn.closest('.recipe-row').querySelectorAll('input, select');
            inputs.forEach(input => input.value = '');
        }
    }

    function openCreateMaterialModal() {
        document.getElementById('materialModalTitle').innerText = '+ Tambah Bahan Baku / Packaging Baru';
        document.getElementById('materialId').value = '';
        document.getElementById('materialName').value = '';
        document.getElementById('materialCategory').value = 'Bahan Baku';
        document.getElementById('materialUnit').value = '';
        document.getElementById('materialStock').value = '0';
        document.getElementById('materialMinStock').value = '10';
        document.getElementById('materialCostPerUnit').value = '0';
        openModal('modalMaterial');
    }

    function openEditMaterialModal(data) {
        document.getElementById('materialModalTitle').innerText = '✏️ Edit Bahan: ' + data.name;
        document.getElementById('materialId').value = data.id;
        document.getElementById('materialName').value = data.name;
        document.getElementById('materialCategory').value = data.category;
        document.getElementById('materialUnit').value = data.unit;
        document.getElementById('materialStock').value = data.stock;
        document.getElementById('materialMinStock').value = data.min_stock;
        document.getElementById('materialCostPerUnit').value = data.cost_per_unit;
        openModal('modalMaterial');
    }

    function openCreateSupplierModal() {
        document.getElementById('supplierModalTitle').innerText = '+ Tambah Pemasok Baru';
        document.getElementById('supplierId').value = '';
        document.getElementById('supplierName').value = '';
        document.getElementById('supplierPhone').value = '';
        document.getElementById('supplierCategory').value = 'Kemasan Cup';
        document.getElementById('supplierAddress').value = '';
        document.getElementById('supplierNotes').value = '';
        openModal('modalSupplier');
    }

    function openEditSupplierModal(data) {
        document.getElementById('supplierModalTitle').innerText = '✏️ Edit Pemasok: ' + data.name;
        document.getElementById('supplierId').value = data.id;
        document.getElementById('supplierName').value = data.name;
        document.getElementById('supplierPhone').value = data.phone || '';
        document.getElementById('supplierCategory').value = data.category || 'Bahan Minuman';
        document.getElementById('supplierAddress').value = data.address || '';
        document.getElementById('supplierNotes').value = data.notes || '';
        openModal('modalSupplier');
    }

    function openCreateProductModal() {
        document.getElementById('productModalTitle').innerText = '+ Tambah Menu Minuman & Resep Baru';
        document.getElementById('productId').value = '';
        document.getElementById('productName').value = '';
        document.getElementById('productCategory').value = 'Coffee';
        document.getElementById('productSku').value = '';
        document.getElementById('productSellingPrice').value = '';
        document.getElementById('productCostPrice').value = '';
        document.getElementById('productDescription').value = '';
        document.getElementById('recipeRowsContainer').innerHTML = `
            <div class="recipe-row flex items-center space-x-2">
                <select name="recipes[0][material_id]" class="flex-1 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs">
                    <option value="">-- Pilih Bahan --</option>
                    ${materialOptions}
                </select>
                <input type="number" step="any" name="recipes[0][quantity]" placeholder="Takaran" class="w-24 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs">
                <button type="button" onclick="removeRecipeRow(this)" class="text-rose-500 hover:text-rose-700 font-bold text-xs p-1">✕</button>
            </div>
        `;
        recipeRowCount = 1;
        openModal('modalProduct');
    }

    function openEditProductModal(product) {
        document.getElementById('productModalTitle').innerText = '✏️ Edit Menu: ' + product.name;
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productCategory').value = product.category;
        document.getElementById('productSku').value = product.sku || '';
        document.getElementById('productSellingPrice').value = product.selling_price;
        document.getElementById('productCostPrice').value = product.cost_price || '';
        document.getElementById('productDescription').value = product.description || '';

        const container = document.getElementById('recipeRowsContainer');
        container.innerHTML = '';
        if (product.recipes && product.recipes.length > 0) {
            recipeRowCount = 0;
            product.recipes.forEach((rcp) => {
                addRecipeRow(rcp.material_id, rcp.quantity);
            });
        } else {
            addRecipeRow();
        }
        openModal('modalProduct');
    }
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
