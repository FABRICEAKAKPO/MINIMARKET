<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Réception {{ $purchaseOrder->number }}</h2></x-slot>
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8"><form method="POST" action="{{ route('purchasing.receive.store', $purchaseOrder) }}" class="space-y-5 rounded-lg bg-white p-6 shadow">@csrf
        <p class="text-sm text-gray-600">Fournisseur : <strong>{{ $purchaseOrder->supplier->name }}</strong></p>
        <div><x-input-label for="purchase_order_line_id" value="Ligne à réceptionner" /><select id="purchase_order_line_id" name="purchase_order_line_id" class="mt-1 block w-full rounded-md border-gray-300" required>@foreach ($purchaseOrder->lines as $line)<option value="{{ $line->id }}">{{ $line->product->name }} — restant {{ $line->ordered_quantity - $line->received_quantity }} — {{ number_format($line->unit_cost_xof, 0, ',', ' ') }} FCFA</option>@endforeach</select></div>
        <div class="grid gap-5 sm:grid-cols-2"><div><x-input-label for="lot_number" value="Numéro de lot" /><x-text-input id="lot_number" name="lot_number" class="mt-1 block w-full" required /></div><div><x-input-label for="quantity" value="Quantité reçue" /><x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" required /></div><div><x-input-label for="expires_at" value="Péremption" /><x-text-input id="expires_at" name="expires_at" type="date" class="mt-1 block w-full" /></div></div>
        <x-text-input name="notes" class="block w-full" placeholder="Notes de réception" /><div class="flex justify-end"><button class="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white">Enregistrer la réception</button></div>
    </form></div>
</x-app-layout>
