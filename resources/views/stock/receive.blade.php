<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between gap-3"><div class="flex items-center gap-4"><a href="{{ route('stock.index') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-900">← Retour au stock</a><h2 class="text-xl font-semibold text-gray-800">Réception de stock</h2></div><button onclick="window.print()" class="rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 print:hidden">Imprimer la fiche</button></div></x-slot>
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('stock.receive.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow">
            @csrf
            @if ($errors->any())<div class="rounded-md bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div><x-input-label for="product_id" value="Produit" /><select id="product_id" name="product_id" class="mt-1 block w-full rounded-md border-gray-300" required>@foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>@endforeach</select></div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div><x-input-label for="lot_number" value="Numéro de lot" /><x-text-input id="lot_number" name="lot_number" class="mt-1 block w-full" required /></div>
                <div><x-input-label for="quantity" value="Quantité" /><x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" required /></div>
                <div><x-input-label for="unit_cost_xof" value="Coût unitaire (FCFA)" /><x-text-input id="unit_cost_xof" name="unit_cost_xof" type="number" min="0" class="mt-1 block w-full" required /></div>
                <div><x-input-label for="expires_at" value="Date de péremption (facultative)" /><x-text-input id="expires_at" name="expires_at" type="date" class="mt-1 block w-full" /></div>
            </div>
            <div><x-input-label for="reason" value="Référence ou motif" /><x-text-input id="reason" name="reason" class="mt-1 block w-full" /></div>
            <div class="flex justify-end gap-3"><a href="{{ route('stock.index') }}" class="rounded-md px-4 py-2 text-sm text-gray-600">Annuler</a><button class="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white">Enregistrer la réception</button></div>
        </form>
    </div>
</x-app-layout>
