<?php

namespace App\Services;

use App\Models\CreditAccount;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreditService
{
    public function charge(Customer $customer, Sale $sale, int $amount, int $userId, ?string $dueAt = null): void
    {
        DB::transaction(function () use ($customer, $sale, $amount, $userId, $dueAt): void {
            $account = CreditAccount::firstOrCreate(['customer_id' => $customer->id]);
            $newBalance = $account->balance_xof + $amount;
            if ($customer->credit_limit_xof > 0 && $newBalance > $customer->credit_limit_xof) {
                throw ValidationException::withMessages(['credit' => 'Le plafond de crédit de ce client serait dépassé.']);
            }
            $account->update(['balance_xof' => $newBalance]);
            $account->entries()->create([
                'sale_id' => $sale->id,
                'user_id' => $userId,
                'type' => 'charge',
                'amount_xof' => $amount,
                'balance_after_xof' => $newBalance,
                'note' => $dueAt ? "Échéance {$dueAt}" : null,
            ]);
            $sale->update(['customer_id' => $customer->id, 'credit_due_at' => $dueAt]);
        });
    }

    public function payment(Customer $customer, int $amount, int $userId, ?string $reference, ?string $note = null): void
    {
        DB::transaction(function () use ($customer, $amount, $userId, $reference, $note): void {
            $account = CreditAccount::query()->where('customer_id', $customer->id)->lockForUpdate()->firstOrFail();
            if ($amount < 1 || $amount > $account->balance_xof) {
                throw ValidationException::withMessages(['amount_xof' => 'Le paiement doit être positif et inférieur ou égal au solde dû.']);
            }
            $newBalance = $account->balance_xof - $amount;
            $account->update(['balance_xof' => $newBalance]);
            $account->entries()->create([
                'user_id' => $userId,
                'type' => 'payment',
                'amount_xof' => $amount,
                'balance_after_xof' => $newBalance,
                'reference' => $reference,
                'note' => $note,
            ]);
        });
    }
}
