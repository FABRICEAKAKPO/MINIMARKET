<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Catalogue produits</h2>
                <p class="text-sm text-gray-500">Prix en FCFA, stocks calculés par lots.</p>
            </div>
            @can('catalog.manage')
                <a href="{{ route('catalog.create') }}" class="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                    Nouveau produit
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <form method="GET" class="flex gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Rechercher un nom ou un SKU"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600">
            <button class="rounded-md bg-gray-800 px-5 py-2 text-sm font-semibold text-white">Rechercher</button>
        </form>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Produit</th>
                            <th class="px-6 py-3">Catégorie</th>
                            <th class="px-6 py-3">Prix vente</th>
                            <th class="px-6 py-3">Stock</th>
                            <th class="px-6 py-3">Statut</th>
                            @can('catalog.manage')<th class="px-6 py-3">Action</th>@endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="font-semibold text-gray-900">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $product->sku }}</div>
                                </td>
                                <td class="px-6 py-4">{{ $product->category->name }}</td>
                                <td class="px-6 py-4">{{ number_format($product->selling_price_xof, 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-4">
                                    <span class="{{ $product->stock_quantity <= $product->low_stock_threshold ? 'font-semibold text-red-600' : 'text-gray-700' }}">
                                        {{ $product->stock_quantity }} {{ $product->unit->short_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Actif</span></td>
                                @can('catalog.manage')
                                    <td class="px-6 py-4">
                                        <form method="POST" action="{{ route('catalog.destroy', $product) }}" onsubmit="return confirm('Retirer ce produit du catalogue actif ? Son historique sera conservé.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-bold text-red-700 hover:text-red-900">Retirer</button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Aucun produit trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t px-6 py-4">{{ $products->links() }}</div>
        </div>
    </div>
</x-app-layout>
