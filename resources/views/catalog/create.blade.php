<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="text-xl font-semibold text-gray-800">Nouveau produit</h2><a href="{{ route('catalog.index') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-900">← Retour au catalogue</a></div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('catalog.store') }}" class="space-y-6 rounded-lg bg-white p-6 shadow">
            @csrf
            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2"><x-input-label for="name" value="Nom du produit" /><x-text-input id="name" name="name" class="mt-1 block w-full" required /></div>
                <div><x-input-label for="sku" value="Référence SKU" /><x-text-input id="sku" name="sku" class="mt-1 block w-full" required /></div>
                <div><x-input-label for="barcode" value="Code-barres principal" /><x-text-input id="barcode" name="barcode" inputmode="numeric" autocomplete="off" autofocus class="mt-1 block w-full" /><p class="mt-1 text-xs text-gray-500">Un lecteur USB peut saisir le code automatiquement dans ce champ.</p></div>
                <div><x-input-label for="category_id" value="Catégorie" /><select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300" required>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                <div><x-input-label for="brand_id" value="Marque" /><select id="brand_id" name="brand_id" class="mt-1 block w-full rounded-md border-gray-300"><option value="">Sans marque</option>@foreach ($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach</select></div>
                <div><x-input-label for="unit_id" value="Unité" /><select id="unit_id" name="unit_id" class="mt-1 block w-full rounded-md border-gray-300" required>@foreach ($units as $unit)<option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_name }})</option>@endforeach</select></div>
                <div><x-input-label for="purchase_price_xof" value="Prix d'achat (FCFA)" /><x-text-input id="purchase_price_xof" name="purchase_price_xof" type="number" min="0" class="mt-1 block w-full" required /></div>
                <div><x-input-label for="selling_price_xof" value="Prix de vente (FCFA)" /><x-text-input id="selling_price_xof" name="selling_price_xof" type="number" min="0" class="mt-1 block w-full" required /></div>
                <div><x-input-label for="tax_rate_basis_points" value="TVA (centièmes de pourcentage)" /><x-text-input id="tax_rate_basis_points" name="tax_rate_basis_points" type="number" value="1800" min="0" max="10000" class="mt-1 block w-full" required /></div>
                <div><x-input-label for="low_stock_threshold" value="Seuil d'alerte stock" /><x-text-input id="low_stock_threshold" name="low_stock_threshold" type="number" min="0" value="0" class="mt-1 block w-full" required /></div>
            </div>
            <div class="flex flex-wrap gap-5 text-sm">
                <label><input type="checkbox" name="is_tax_exempt" value="1" class="rounded border-gray-300"> Produit exonéré</label>
                <label><input type="checkbox" name="is_weighted" value="1" class="rounded border-gray-300"> Produit au poids</label>
                <label><input type="checkbox" name="has_expiry" value="1" class="rounded border-gray-300"> Date de péremption</label>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('catalog.index') }}" class="rounded-md px-4 py-2 text-sm text-gray-600">Annuler</a>
                <button class="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white">Enregistrer</button>
            </div>
        </form>
    </div>
</x-app-layout>
