<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\CreditAccount;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockLot;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();
        $salesToday = Sale::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', $today);

        $products = Product::query()
            ->where('is_active', true)
            ->withSum('stockLots', 'quantity')
            ->get();

        $lowStockProducts = $products
            ->filter(fn (Product $product): bool => (int) $product->stock_lots_sum_quantity <= (int) $product->low_stock_threshold)
            ->sortBy('stock_lots_sum_quantity')
            ->take(5);

        $expiringLots = StockLot::query()
            ->with('product')
            ->where('quantity', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', Carbon::today()->addDays(30))
            ->orderBy('expires_at')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'salesTodayAmount' => (int) $salesToday->sum('total_xof'),
            'salesTodayCount' => (int) $salesToday->count(),
            'cashSession' => CashSession::query()->where('status', 'open')->latest('opened_at')->first(),
            'lowStockProducts' => $lowStockProducts,
            'expiringLots' => $expiringLots,
            'creditBalance' => (int) CreditAccount::query()->sum('balance_xof'),
            'recentSales' => Sale::query()
                ->with(['customer', 'user'])
                ->where('status', 'completed')
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }
}
