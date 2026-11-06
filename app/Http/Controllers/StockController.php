<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLot;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with(['unit', 'stockLots'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $expiringLots = StockLot::query()
            ->with('product')
            ->where('quantity', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', today()->addDays(30))
            ->orderBy('expires_at')
            ->get();

        return view('stock.index', compact('products', 'expiringLots'));
    }

    public function receiveForm(): View
    {
        return view('stock.receive', [
            'products' => Product::query()->with('barcodes')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function receive(Request $request, StockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'lot_number' => ['required', 'string', 'max:80'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost_xof' => ['required', 'integer', 'min:0'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $stockService->receive(
            $product,
            $validated['lot_number'],
            $validated['quantity'],
            $validated['unit_cost_xof'],
            $validated['expires_at'] ?? null,
            (int) $request->user()->id,
            $validated['reason'] ?? null,
        );

        return redirect()->route('stock.index')->with('status', 'Réception enregistrée avec traçabilité.');
    }

    public function issue(Request $request, Product $product, StockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $stockService->issue($product, $validated['quantity'], (int) $request->user()->id, 'adjustment_out', $validated['reason']);

        return redirect()->route('stock.index')->with('status', 'Sortie enregistrée selon la méthode FEFO.');
    }
}
