<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLot;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function receive(
        Product $product,
        string $lotNumber,
        int $quantity,
        int $unitCostXof,
        ?string $expiresAt,
        int $userId,
        ?string $reason = null,
    ): StockLot {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'La quantité reçue doit être supérieure à zéro.']);
        }

        return DB::transaction(function () use ($product, $lotNumber, $quantity, $unitCostXof, $expiresAt, $userId, $reason): StockLot {
            $lot = StockLot::query()->lockForUpdate()->firstOrCreate(
                ['product_id' => $product->id, 'lot_number' => $lotNumber],
                ['quantity' => 0, 'unit_cost_xof' => $unitCostXof, 'expires_at' => $expiresAt],
            );
            $before = $product->stock_quantity;
            $lot->increment('quantity', $quantity);
            $lot->update(['unit_cost_xof' => $unitCostXof, 'expires_at' => $expiresAt]);

            $this->recordMovement($product, $lot, $userId, 'receipt', $quantity, $before, $before + $quantity, $reason ?? 'Réception de stock');

            activity('stock')->causedBy(auth()->user())->performedOn($product)->withProperties([
                'lot' => $lotNumber,
                'quantity' => $quantity,
                'unit_cost_xof' => $unitCostXof,
            ])->log('Réception de stock');

            return $lot->refresh();
        });
    }

    public function issue(Product $product, int $quantity, int $userId, string $type = 'sale', ?string $reason = null): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'La quantité sortie doit être supérieure à zéro.']);
        }

        DB::transaction(function () use ($product, $quantity, $userId, $type, $reason): void {
            $lots = StockLot::query()
                ->where('product_id', $product->id)
                ->where('quantity', '>', 0)
                ->where(function ($query): void {
                    $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today());
                })
                ->orderByRaw('expires_at IS NULL')
                ->orderBy('expires_at')
                ->lockForUpdate()
                ->get();

            $available = (int) $lots->sum('quantity');
            if ($available < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'Stock insuffisant ou lots périmés indisponibles.']);
            }

            $remaining = $quantity;
            foreach ($lots as $lot) {
                $taken = min($remaining, $lot->quantity);
                $before = $product->stock_quantity;
                $lot->decrement('quantity', $taken);
                $this->recordMovement($product, $lot, $userId, $type, -$taken, $before, $before - $taken, $reason ?? 'Sortie FEFO');
                $remaining -= $taken;
                if ($remaining === 0) {
                    break;
                }
            }
        });
    }

    public function returnToStock(Product $product, int $quantity, int $userId, string $reason): StockLot
    {
        return $this->receive(
            $product,
            'RETURN-'.now()->format('YmdHis').'-'.random_int(100, 999),
            $quantity,
            $product->purchase_price_xof,
            null,
            $userId,
            $reason,
        );
    }

    private function recordMovement(Product $product, StockLot $lot, int $userId, string $type, int $quantity, int $before, int $after, string $reason): void
    {
        StockMovement::create([
            'product_id' => $product->id,
            'stock_lot_id' => $lot->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => max(0, $before),
            'quantity_after' => max(0, $after),
            'reason' => $reason,
        ]);
    }
}
