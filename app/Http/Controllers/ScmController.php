<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScmController extends Controller
{
    /**
     * Dashboard SCM: Inventaris Bahan Baku, Produk Minuman, dan Pemasok
     */
    public function index()
    {
        $materials = Material::with('recipes.product')->orderBy('name')->get();
        $products = Product::with('recipes.material')->orderBy('name')->get();
        $suppliers = Supplier::withCount('stockMovements')->orderBy('name')->get();
        $recentMovements = StockMovement::with(['material', 'supplier', 'product'])
            ->latest()
            ->take(20)
            ->get();

        $categories = config('beverage.categories', []);
        $store = config('beverage.store', []);

        return view('seller.scm', compact('materials', 'products', 'suppliers', 'recentMovements', 'categories', 'store'));
    }

    /**
     * Tambah / Edit Bahan Baku & Kemasan
     */
    public function storeMaterial(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50',
            'category' => 'required|string',
            'unit' => 'required|string|max:20',
            'stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
        ]);

        Material::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'sku' => $request->sku ?: 'MAT-' . strtoupper(Str::random(6)),
                'category' => $request->category,
                'unit' => $request->unit,
                'stock' => $request->stock,
                'min_stock' => $request->min_stock,
                'cost_per_unit' => $request->cost_per_unit,
                'is_active' => true,
            ]
        );

        return redirect()->route('seller.scm')->with('success', 'Bahan baku berhasil disimpan!');
    }

    /**
     * Kulakan / Restock Bahan Baku dari Supplier
     * Terintegrasi dengan:
     * - SCM: Menambah kuantitas stok bahan dan mencatat audit trail StockMovement
     * - ERP: Otomatis mencatat pengeluaran uang (kas keluar) ke buku kas
     */
    public function restock(Request $request)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'required|numeric|min:0.1',
            'unit_cost' => 'required|numeric|min:0',
            'reference_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $material = Material::findOrFail($request->material_id);
            $qty = (float) $request->quantity;
            $unitCost = (float) $request->unit_cost;
            $totalCost = $qty * $unitCost;

            // 1. Tambah stok bahan
            $material->increment('stock', $qty);
            $material->update(['cost_per_unit' => $unitCost]);

            $refNo = $request->reference_no ?: 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            // 2. Catat Mutasi Stok SCM
            StockMovement::create([
                'material_id' => $material->id,
                'type' => 'in',
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'supplier_id' => $request->supplier_id,
                'reference_no' => $refNo,
                'notes' => $request->notes ?: "Restock {$material->name}",
            ]);

            // 3. ERP Integration: Catat Kas Keluar Otomatis
            $supplierName = $request->supplier_id ? Supplier::find($request->supplier_id)?->name : 'Supplier Tunai';
            CashFlow::create([
                'type' => 'out',
                'category' => 'restock',
                'amount' => $totalCost,
                'description' => "Kulakan Bahan: {$qty} {$material->unit} {$material->name} dari {$supplierName} ({$refNo})",
                'transaction_date' => now()->toDateString(),
            ]);

            return redirect()->route('seller.scm')
                ->with('success', "Restock {$material->name} berhasil! Biaya Rp " . number_format($totalCost, 0, ',', '.') . " tercatat di buku kas.");
        });
    }

    /**
     * Tambah / Edit Menu Minuman & Resep Sederhana
     */
    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'recipes' => 'nullable|array',
            'recipes.*.material_id' => 'required_with:recipes|exists:materials,id',
            'recipes.*.quantity' => 'required_with:recipes|numeric|min:0.1',
        ]);

        return DB::transaction(function () use ($request) {
            $product = Product::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'sku' => $request->sku ?: 'DRK-' . strtoupper(Str::random(6)),
                    'category' => $request->category,
                    'selling_price' => $request->selling_price,
                    'cost_price' => $request->cost_price ?: 0,
                    'description' => $request->description,
                    'is_active' => true,
                ]
            );

            // Simpan atau perbarui resep jika ada input resep
            if ($request->has('recipes')) {
                $product->recipes()->delete();
                $calculatedCost = 0;

                foreach ($request->recipes as $r) {
                    if (!empty($r['material_id']) && !empty($r['quantity'])) {
                        $material = Material::find($r['material_id']);
                        $qty = (float) $r['quantity'];

                        ProductRecipe::create([
                            'product_id' => $product->id,
                            'material_id' => $material->id,
                            'quantity' => $qty,
                        ]);

                        $calculatedCost += ($material->cost_per_unit * $qty);
                    }
                }

                // Update HPP otomatis jika tidak diisi manual
                if (empty($request->cost_price) && $calculatedCost > 0) {
                    $product->update(['cost_price' => $calculatedCost]);
                }
            }

            return redirect()->route('seller.scm')->with('success', 'Menu minuman dan resep berhasil disimpan!');
        });
    }

    /**
     * Tambah Pemasok Baru
     */
    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'category' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Supplier::create($request->all());

        return redirect()->route('seller.scm')->with('success', 'Pemasok baru berhasil ditambahkan!');
    }

    /**
     * Hapus Bahan Baku & Kemasan
     */
    public function destroyMaterial($id)
    {
        $material = Material::findOrFail($id);
        $name = $material->name;
        $material->recipes()->delete();
        $material->delete();

        return redirect()->route('seller.scm')->with('success', "Bahan baku {$name} berhasil dihapus!");
    }

    /**
     * Hapus Menu Minuman
     */
    public function destroyProduct($id)
    {
        $product = Product::findOrFail($id);
        $name = $product->name;
        $product->recipes()->delete();
        $product->delete();

        return redirect()->route('seller.scm')->with('success', "Menu minuman {$name} berhasil dihapus!");
    }

    /**
     * Hapus Pemasok (Supplier)
     */
    public function destroySupplier($id)
    {
        $supplier = Supplier::findOrFail($id);
        $name = $supplier->name;
        $supplier->delete();

        return redirect()->route('seller.scm')->with('success', "Pemasok {$name} berhasil dihapus!");
    }
}
