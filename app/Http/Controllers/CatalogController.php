<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'brand', 'unit', 'stockLots'])
            ->when($request->string('search')->trim()->value(), function ($query, string $search): void {
                $query->where(function ($productQuery) use ($search): void {
                    $productQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('catalog.index', compact('products'));
    }

    public function create(): View
    {
        return view('catalog.create', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:80', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'purchase_price_xof' => ['required', 'integer', 'min:0'],
            'selling_price_xof' => ['required', 'integer', 'min:0'],
            'tax_rate_basis_points' => ['required', 'integer', 'min:0', 'max:10000'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:80', 'unique:product_barcodes,barcode'],
            'is_tax_exempt' => ['sometimes', 'boolean'],
            'is_weighted' => ['sometimes', 'boolean'],
            'has_expiry' => ['sometimes', 'boolean'],
        ]);

        $product = Product::create($validated);

        if ($request->filled('barcode')) {
            $product->barcodes()->create([
                'barcode' => $validated['barcode'],
                'is_primary' => true,
            ]);
        }

        return redirect()->route('catalog.index')->with('status', 'Produit créé avec succès.');
    }
}
