<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="text-xl font-semibold text-gray-800">Inventaire physique</h2><button onclick="window.print()" class="rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 print:hidden">Imprimer la feuille</button></div></x-slot>
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('inventory.store') }}" class="space-y-6 rounded-lg bg-white p-6 shadow">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2"><div><x-input-label for="counted_at" value="Date du comptage" /><x-text-input id="counted_at" name="counted_at" type="date" value="{{ today()->toDateString() }}" class="mt-1 block w-full" required /></div><div><x-input-label for="notes" value="Notes" /><x-text-input id="notes" name="notes" class="mt-1 block w-full" /></div></div>
            <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="border-b text-left"><th class="px-3 py-2">Produit</th><th class="px-3 py-2">Stock système</th><th class="px-3 py-2">Comptage réel</th></tr></thead><tbody>@foreach ($products as $product)<tr class="border-b"><td class="px-3 py-3">{{ $product->name }}</td><td class="px-3 py-3">{{ $product->stock_quantity }} {{ $product->unit->short_name }}</td><td class="px-3 py-3"><input name="counts[{{ $product->id }}]" type="number" min="0" value="{{ $product->stock_quantity }}" class="w-32 rounded border-gray-300" required></td></tr>@endforeach</tbody></table></div>
            <div class="flex justify-end"><button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white">Clôturer l'inventaire</button></div>
        </form>
    </div>
</x-app-layout>
