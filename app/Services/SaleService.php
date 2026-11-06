<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\Sale;
use App\Models\SaleReversal;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function complete(CashSession $cashSession, array $items, array $payments, int $userId): Sale
    {
        return DB::transaction(function () use ($cashSession, $items, $payments, $userId): Sale {
            if ($cashSession->status !== 'open') {
                throw ValidationException::withMessages(['cash' => 'La caisse doit être ouverte pour enregistrer une vente.']);
            }

            if ($items === []) {
                throw ValidationException::withMessages(['items' => 'Le panier est vide.']);
            }

            $subtotal = 0;
            $tax = 0;
            $sale = new Sale;
            $sale->fill([
                'cash_session_id' => $cashSession->id,
                'user_id' => $userId,
                'number' => 'VT-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'status' => 'completed',
                'subtotal_xof' => 0,
                'tax_xof' => 0,
                'total_xof' => 0,
            ]);
            $sale->save();

            foreach ($items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $lineSubtotal = $product->selling_price_xof * $quantity;
                $lineTax = $product->is_tax_exempt ? 0 : (int) round($lineSubtotal * $product->tax_rate_basis_points / 10000);
                app(StockService::class)->issue($product, $quantity, $userId, 'sale', "Vente {$sale->number}");
                $sale->lines()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price_xof' => $product->selling_price_xof,
                    'unit_cost_xof' => $product->purchase_price_xof,
                    'tax_xof' => $lineTax,
                    'line_total_xof' => $lineSubtotal,
                ]);
                $subtotal += $lineSubtotal;
                $tax += $lineTax;
            }

            $total = $subtotal;
            $paid = array_sum(array_map(static fn (array $payment): int => (int) $payment['amount_xof'], $payments));
            if ($paid !== $total) {
                throw ValidationException::withMessages(['payments' => 'La somme des paiements doit être exactement égale au total de la vente.']);
            }

            foreach ($payments as $payment) {
                if (in_array($payment['method'], ['tmoney', 'flooz'], true) && blank($payment['reference'])) {
                    throw ValidationException::withMessages(['payments' => 'Une référence est obligatoire pour un paiement mobile money.']);
                }
                $sale->payments()->create([
                    'method' => $payment['method'],
                    'amount_xof' => (int) $payment['amount_xof'],
                    'reference' => $payment['reference'] ?? null,
                    'status' => 'confirmed',
                ]);
            }

            $sale->update(['subtotal_xof' => $subtotal, 'tax_xof' => $tax, 'total_xof' => $total]);
            $customerId = collect($payments)->first()['customer_id'] ?? null;
            if ($customerId !== null) {
                $customer = \App\Models\Customer::findOrFail($customerId);
                $sale->update(['customer_id' => $customer->id]);
                $customer->increment('loyalty_points', intdiv($total, 1000));
            }
            $creditPayment = collect($payments)->firstWhere('method', 'credit');
            if ($creditPayment !== null) {
                if (! isset($creditPayment['customer_id'])) {
                    throw ValidationException::withMessages(['customer_id' => 'Un client est obligatoire pour une vente à crédit.']);
                }
                $customer = \App\Models\Customer::findOrFail($creditPayment['customer_id']);
                app(CreditService::class)->charge($customer, $sale, (int) $creditPayment['amount_xof'], $userId, $creditPayment['due_at'] ?? null);
            }
            $cashSession->increment('expected_amount_xof', collect($payments)->where('method', 'cash')->sum('amount_xof'));
            activity('sales')->causedBy(auth()->user())->performedOn($sale)->log('Vente enregistrée');
            return $sale->refresh();
        });
    }

    public function cancel(Sale $sale, string $reason, int $userId): void
    {
        DB::transaction(function () use ($sale, $reason, $userId): void {
            $sale->load('lines', 'cashSession');
            if ($sale->status !== 'completed') {
                throw ValidationException::withMessages(['sale' => 'Cette vente est déjà annulée ou retournée.']);
            }

            foreach ($sale->lines as $line) {
                app(StockService::class)->returnToStock($line->product, $line->quantity, $userId, "Annulation {$sale->number}: {$reason}");
            }

            $cashAmount = (int) $sale->payments()->where('method', 'cash')->sum('amount_xof');
            $sale->cashSession->decrement('expected_amount_xof', $cashAmount);
            $sale->update(['status' => 'cancelled', 'cancellation_reason' => $reason]);
            SaleReversal::create(['sale_id' => $sale->id, 'user_id' => $userId, 'type' => 'cancellation', 'reason' => $reason]);
            activity('sales')->causedBy(auth()->user())->performedOn($sale)->withProperties(['reason' => $reason])->log('Vente annulée par écriture inverse');
        });
    }
}
