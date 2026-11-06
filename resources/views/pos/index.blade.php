<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="text-xl font-semibold text-gray-800">Caisse POS</h2><a href="{{ route('cash.index') }}" class="text-sm text-green-700 underline">Gérer la caisse</a></div></x-slot>
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))<div class="mb-5 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>@endif
        @if (!$cashSession)
            <div class="rounded-md bg-amber-50 p-5 text-amber-900">Ouvrez une caisse avant d'enregistrer une vente.</div>
        @else
            <form method="POST" action="{{ route('pos.sell') }}" class="space-y-6">@csrf
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($products as $product)
                        <div class="rounded-lg border bg-white p-4 shadow-sm">
                            <div class="font-semibold">{{ $product->name }}</div>
                            <div class="text-sm text-gray-500">{{ number_format($product->selling_price_xof, 0, ',', ' ') }} FCFA / {{ $product->unit->short_name }}</div>
                            <label class="mt-3 block text-xs text-gray-500">Quantité</label>
                            <input name="items[{{ $product->id }}]" type="number" min="0" value="0" class="mt-1 w-full rounded border-gray-300">
                        </div>
                    @endforeach
                </div>
                <div class="grid gap-4 rounded-lg bg-white p-5 shadow sm:grid-cols-5">
                    <div><label class="block text-sm font-medium text-gray-700">Paiement</label><select name="payment_method" class="mt-1 w-full rounded border-gray-300"><option value="cash">Espèces</option><option value="tmoney">T-Money</option><option value="flooz">Flooz</option><option value="card">Carte</option><option value="credit">Crédit</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Client crédit</label><select name="customer_id" class="mt-1 w-full rounded border-gray-300"><option value="">Aucun</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Échéance</label><input name="credit_due_at" type="date" class="mt-1 w-full rounded border-gray-300"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Référence mobile</label><input name="payment_reference" class="mt-1 w-full rounded border-gray-300" placeholder="Obligatoire T-Money/Flooz"></div>
                    <div class="flex items-end"><button class="w-full rounded-md bg-green-700 px-4 py-2 font-semibold text-white">Valider la vente</button></div>
                </div>
            </form>
            <div class="mt-6 rounded-lg bg-gray-800 p-5 text-white"><h3 class="font-semibold">Session de caisse</h3><p class="mt-2 text-sm text-gray-300">Attendu : {{ number_format($cashSession->expected_amount_xof, 0, ',', ' ') }} FCFA</p></div>
        @endif
    </div>
</x-app-layout>
