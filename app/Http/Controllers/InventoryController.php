<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function create(): View
    {
        return view('inventory.create', [
            'products' => Product::query()->with('stockLots')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'counted_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'counts' => ['required', 'array'],
            'counts.*' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $inventory = Inventory::create([
                'user_id' => $request->user()->id,
                'status' => 'completed',
                'counted_at' => $validated['counted_at'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['counts'] as $productId => $countedQuantity) {
                $product = Product::with('stockLots')->findOrFail($productId);
                $systemQuantity = (int) $product->stockLots->sum('quantity');
                $inventory->lines()->create([
                    'product_id' => $product->id,
                    'system_quantity' => $systemQuantity,
                    'counted_quantity' => $countedQuantity,
                    'difference' => $countedQuantity - $systemQuantity,
                ]);
            }

            activity('stock')->causedBy($request->user())->performedOn($inventory)->withProperties([
                'lines' => $inventory->lines()->count(),
            ])->log('Inventaire physique clôturé');
        });

        return redirect()->route('stock.index')->with('status', 'Inventaire physique enregistré.');
    }
}
