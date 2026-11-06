<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        return view('pos.index', [
            'cashSession' => CashSession::where('user_id', $request->user()->id)->where('status', 'open')->latest()->first(),
            'products' => Product::with(['unit', 'barcodes'])->where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function sell(Request $request, SaleService $saleService): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['required', 'in:cash,tmoney,flooz,card,credit'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'credit_due_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        $session = CashSession::where('user_id', $request->user()->id)->where('status', 'open')->latest()->firstOrFail();
        $items = collect($data['items'])->filter(fn ($quantity): bool => (int) $quantity > 0)->map(fn ($quantity, $productId): array => ['product_id' => (int) $productId, 'quantity' => (int) $quantity])->values()->all();
        $total = collect($items)->sum(fn (array $item): int => Product::findOrFail($item['product_id'])->selling_price_xof * $item['quantity']);
        $sale = $saleService->complete($session, $items, [[
            'method' => $data['payment_method'],
            'amount_xof' => $total,
            'reference' => $data['payment_reference'],
            'customer_id' => $data['customer_id'] ?? null,
            'due_at' => $data['credit_due_at'] ?? null,
        ]], (int) $request->user()->id);
        return redirect()->route('pos.receipt', $sale)->with('status', 'Vente enregistrée.');
    }

    public function cancel(Request $request, Sale $sale, SaleService $saleService): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $saleService->cancel($sale, $data['reason'], (int) $request->user()->id);
        return back()->with('status', 'Vente annulée par écriture inverse.');
    }

    public function receipt(Sale $sale): View
    {
        $sale->load('lines.product', 'payments', 'user');
        return view('pos.receipt', compact('sale'));
    }
}
