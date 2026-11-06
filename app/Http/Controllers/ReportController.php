<?php

namespace App\Http\Controllers;

use App\Models\CreditEntry;
use App\Models\Product;
use App\Models\Sale;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        [$from, $to] = $this->period($request);
        $sales = $this->salesQuery($from, $to)->get();
        $completedSales = $sales->where('status', 'completed');
        $cancelledSales = $sales->where('status', 'cancelled');

        return view('reports.sales', [
            'sales' => $sales,
            'from' => $from,
            'to' => $to,
            'completedCount' => $completedSales->count(),
            'completedAmount' => (int) $completedSales->sum('total_xof'),
            'taxAmount' => (int) $completedSales->sum('tax_xof'),
            'cancelledCount' => $cancelledSales->count(),
            'cancelledAmount' => (int) $cancelledSales->sum('total_xof'),
            'paymentSummary' => $completedSales
                ->flatMap(fn (Sale $sale) => $sale->payments)
                ->groupBy('method')
                ->map(fn ($payments): int => (int) $payments->sum('amount_xof')),
        ]);
    }

    public function salesCsv(Request $request): Response
    {
        [$from, $to] = $this->period($request);
        $sales = $this->salesQuery($from, $to)->get();

        return response()->streamDownload(function () use ($sales, $from, $to): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Rapport des ventes', 'Du '.$from->format('d/m/Y').' au '.$to->format('d/m/Y')], ';');
            fputcsv($handle, ['Généré le', now()->format('d/m/Y H:i'), 'Par', auth()->user()->name], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['Référence', 'Date', 'Statut', 'Client', 'Caissier', 'Sous-total FCFA', 'TVA FCFA', 'Total FCFA'], ';');

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->number,
                    $sale->created_at->format('d/m/Y H:i'),
                    $sale->status === 'completed' ? 'Terminée' : 'Annulée',
                    $sale->customer?->name ?? 'Client comptant',
                    $sale->user?->name ?? '—',
                    $sale->subtotal_xof,
                    $sale->tax_xof,
                    $sale->total_xof,
                ], ';');
            }

            fclose($handle);
        }, 'rapport-ventes-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function stock(): View
    {
        $stock = Product::query()->with(['category', 'stockLots'])->where('is_active', true)->get()->map(function (Product $product): array {
            $quantity = (int) $product->stockLots->sum('quantity');
            $expiredQuantity = (int) $product->stockLots->filter(fn ($lot): bool => $lot->quantity > 0 && $lot->expires_at?->isPast())->sum('quantity');

            return [
                'product' => $product,
                'quantity' => $quantity,
                'expiredQuantity' => $expiredQuantity,
                'value' => $quantity * (int) $product->purchase_price_xof,
                'isLow' => $quantity <= (int) $product->low_stock_threshold,
            ];
        });

        return view('reports.stock', [
            'stock' => $stock,
            'totalValue' => (int) $stock->sum('value'),
            'lowCount' => $stock->where('isLow', true)->count(),
            'expiredQuantity' => (int) $stock->sum('expiredQuantity'),
        ]);
    }

    public function stockCsv(): Response
    {
        $stock = Product::query()->with(['category', 'stockLots'])->where('is_active', true)->get();

        return response()->streamDownload(function () use ($stock): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['État du stock', 'Généré le '.now()->format('d/m/Y H:i'), 'Par '.auth()->user()->name], ';');
            fputcsv($handle, ['Produit', 'Catégorie', 'Quantité', 'Seuil', 'Valeur achat FCFA', 'Péremption'], ';');
            foreach ($stock as $product) {
                fputcsv($handle, [$product->name, $product->category?->name ?? '—', $product->stockLots->sum('quantity'), $product->low_stock_threshold, $product->stockLots->sum('quantity') * $product->purchase_price_xof, $product->stockLots->filter(fn ($lot): bool => $lot->quantity > 0 && $lot->expires_at?->isPast())->sum('quantity')], ';');
            }
            fclose($handle);
        }, 'etat-stock-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function credit(Request $request): View
    {
        [$from, $to] = $this->period($request);
        $entries = CreditEntry::query()->with(['account.customer', 'sale'])->whereBetween('created_at', [$from, $to])->latest()->get();

        return view('reports.credit', [
            'entries' => $entries,
            'from' => $from,
            'to' => $to,
            'charges' => (int) $entries->where('type', 'charge')->sum('amount_xof'),
            'payments' => (int) $entries->where('type', 'payment')->sum('amount_xof'),
        ]);
    }

    public function creditCsv(Request $request): Response
    {
        [$from, $to] = $this->period($request);
        $entries = CreditEntry::query()->with(['account.customer', 'sale'])->whereBetween('created_at', [$from, $to])->latest()->get();

        return response()->streamDownload(function () use ($entries, $from, $to): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Journal du crédit', 'Du '.$from->format('d/m/Y').' au '.$to->format('d/m/Y'), 'Par '.auth()->user()->name], ';');
            fputcsv($handle, ['Date', 'Client', 'Type', 'Référence', 'Montant FCFA', 'Solde après FCFA'], ';');
            foreach ($entries as $entry) {
                fputcsv($handle, [$entry->created_at->format('d/m/Y H:i'), $entry->account?->customer?->name ?? '—', $entry->type === 'charge' ? 'Crédit' : 'Paiement', $entry->sale?->number ?? $entry->reference ?? '—', $entry->amount_xof, $entry->balance_after_xof], ';');
            }
            fclose($handle);
        }, 'journal-credit-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function period(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            CarbonImmutable::parse($data['from'] ?? now()->startOfMonth()->toDateString())->startOfDay(),
            CarbonImmutable::parse($data['to'] ?? now()->toDateString())->endOfDay(),
        ];
    }

    private function salesQuery(CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return Sale::query()
            ->with(['customer', 'user', 'payments'])
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest();
    }
}
