<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\WholesaleRule;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'products');
        $search = trim($request->input('search', ''));
        $categorySlug = $request->input('category', 'all');

        $query = Product::with(['category', 'activeWholesaleRule']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categorySlug !== 'all') {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $products = $query->orderBy('name', 'asc')->paginate(10, ['*'], 'p_page')->withQueryString();

        $moveSearch = trim($request->input('move_search', ''));
        $moveType = $request->input('move_type', 'all');
        $moveStart = $request->input('move_start_date', '');
        $moveEnd = $request->input('move_end_date', '');

        $moveQuery = StockMovement::with(['product', 'user']);

        if ($moveSearch) {
            $moveQuery->where(function ($q) use ($moveSearch) {
                $q->where('notes', 'like', "%{$moveSearch}%")
                  ->orWhereHas('product', function ($pq) use ($moveSearch) {
                      $pq->where('name', 'like', "%{$moveSearch}%")
                         ->orWhere('sku', 'like', "%{$moveSearch}%")
                         ->orWhere('barcode', 'like', "%{$moveSearch}%");
                  });
            });
        }

        if ($moveType !== 'all') {
            $moveQuery->where('movement_type', $moveType);
        }

        if ($moveStart) {
            $moveQuery->whereDate('created_at', '>=', $moveStart);
        }
        if ($moveEnd) {
            $moveQuery->whereDate('created_at', '<=', $moveEnd);
        }

        $movements = $moveQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'm_page')->withQueryString();

        $categories = Category::withCount('products')->orderBy('name', 'asc')->get();
        $allProducts = Product::where('is_active', 1)->orderBy('name', 'asc')->get(['id', 'name', 'sku', 'stock', 'unit']);

        $stats = [
            'total_sku' => Product::where('is_active', 1)->count(),
            'total_stock_physical' => (int)Product::where('is_active', 1)->sum('stock'),
            'low_stock_count' => Product::where('is_active', 1)->whereRaw('stock <= min_stock')->count(),
            'total_in_qty' => (int)StockMovement::whereIn('movement_type', ['in', 'void_return'])->sum('qty_change'),
            'total_out_qty' => abs((int)StockMovement::where('movement_type', 'sale')->sum('qty_change')),
        ];

        return view('inventory.index', compact('products', 'allProducts', 'movements', 'categories', 'stats', 'tab', 'search', 'categorySlug', 'moveSearch', 'moveType', 'moveStart', 'moveEnd'));
    }

    public function storeProduct(Request $request)
    {
        $id = $request->input('id');
        $validated = $request->validate([
            'sku' => 'required|string|max:60',
            'name' => 'required|string|max:200',
            'retail_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:30',
            'category_id' => 'nullable|exists:categories,id',
            'barcode' => 'nullable|string|max:60',
        ]);

        $sku = strtoupper(trim($validated['sku']));
        $unit = trim($validated['unit'] ?? 'pcs') ?: 'pcs';
        $minStock = (int)($validated['min_stock'] ?? 5);
        $costPrice = (float)($validated['cost_price'] ?? 0);
        $retailPrice = (float)$validated['retail_price'];
        $categoryId = !empty($validated['category_id']) ? (int)$validated['category_id'] : null;
        $barcode = trim($validated['barcode'] ?? '');

        if ($id) {
            $product = Product::findOrFail($id);
            $product->update([
                'category_id' => $categoryId,
                'sku' => $sku,
                'barcode' => $barcode,
                'name' => trim($validated['name']),
                'unit' => $unit,
                'cost_price' => $costPrice,
                'retail_price' => $retailPrice,
                'min_stock' => $minStock,
            ]);
        } else {
            $stock = (int)($validated['stock'] ?? 0);
            $product = Product::create([
                'category_id' => $categoryId,
                'sku' => $sku,
                'barcode' => $barcode,
                'name' => trim($validated['name']),
                'unit' => $unit,
                'cost_price' => $costPrice,
                'retail_price' => $retailPrice,
                'stock' => $stock,
                'min_stock' => $minStock,
                'is_active' => 1,
                'created_at' => now(),
            ]);

            if ($stock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'reference_id' => null,
                    'movement_type' => 'in',
                    'qty_change' => $stock,
                    'stock_before' => 0,
                    'stock_after' => $stock,
                    'notes' => 'Stok Awal Input Produk Baru',
                    'created_at' => now(),
                ]);
            }
        }

        $hasWholesale = !empty($request->input('has_wholesale'));
        $wsMinQty = (int)$request->input('ws_min_qty', 0);
        $wsDiscountType = $request->input('ws_discount_type', 'fixed_amount');
        $wsDiscountValue = (float)$request->input('ws_discount_value', 0);

        WholesaleRule::where('product_id', $product->id)->delete();
        if ($hasWholesale && $wsMinQty > 1 && $wsDiscountValue > 0) {
            WholesaleRule::create([
                'product_id' => $product->id,
                'rule_type' => 'per_item_qty',
                'min_qty' => $wsMinQty,
                'min_amount' => 0.00,
                'discount_type' => $wsDiscountType,
                'discount_value' => $wsDiscountValue,
                'is_active' => 1,
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Data produk berhasil disimpan.', 'product' => $product]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);

        $baseSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $cat = Category::create([
            'name' => $name,
            'slug' => $slug,
            'description' => trim($validated['description'] ?? ''),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan.', 'category' => $cat]);
    }

    public function adjustStock(Request $request, StockService $stockService)
    {
        $productId = (int)$request->input('product_id');
        $qtyChange = (int)$request->input('qty_change', $request->input('qty', 0));
        $movementType = $request->input('movement_type', $request->input('type', 'adjustment'));
        $notes = trim($request->input('notes', ''));

        if ($movementType === 'in') {
            $qtyChange = abs($qtyChange);
        } elseif ($movementType === 'out') {
            $qtyChange = -abs($qtyChange);
            $movementType = 'adjustment';
        } elseif (!in_array($movementType, ['in', 'sale', 'void_return', 'adjustment'], true)) {
            $movementType = 'adjustment';
        }

        try {
            $result = $stockService->adjustStock($productId, $qtyChange, $movementType, $notes, Auth::user());
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function setPhysicalStock(Request $request, StockService $stockService)
    {
        $productId = (int)$request->input('product_id');
        $newStock = (int)$request->input('new_stock', $request->input('actual_stock', 0));
        $notes = trim($request->input('notes', ''));

        try {
            $result = $stockService->setPhysicalStock($productId, $newStock, $notes, Auth::user());
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function stockHistory($id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }

        $movements = StockMovement::with('user')
            ->where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $totalIn = (int)StockMovement::where('product_id', $id)->whereIn('movement_type', ['in', 'void_return'])->sum('qty_change');
        $totalOut = abs((int)StockMovement::where('product_id', $id)->where('movement_type', 'sale')->sum('qty_change'));

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'unit' => $product->unit,
                'stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
            ],
            'movements' => $movements->map(function ($m) {
                return [
                    'id' => $m->id,
                    'created_at' => $m->created_at->format('d/m/Y H:i'),
                    'type' => $m->movement_type,
                    'qty_change' => $m->qty_change,
                    'stock_before' => $m->stock_before,
                    'stock_after' => $m->stock_after,
                    'notes' => $m->notes,
                    'user_name' => $m->user->name ?? 'System',
                ];
            }),
        ]);
    }
}
