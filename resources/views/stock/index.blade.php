<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Stock et alertes</h2>
                <p class="text-sm text-gray-500">Les sorties utilisent automatiquement les lots les plus proches de la péremption.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.stock') }}" class="rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Fiche stock</a>
                <button onclick="window.print()" class="rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 print:hidden">Imprimer</button>
                @can('inventory.manage')
                    <a href="{{ route('stock.receive') }}" class="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white">Réception</a>
                    <a href="{{ route('inventory.create') }}" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white">Inventaire</a>
                @endcan
            </div>
        </div>
    </x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))<div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>@endif
        @if ($expiringLots->isNotEmpty())
            <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-900">
                <strong>Alertes de péremption :</strong>
                @foreach ($expiringLots as $lot)
                    <span class="ml-2">{{ $lot->product->name }} (lot {{ $lot->lot_number }}, {{ $lot->expires_at->format('d/m/Y') }})</span>
                @endforeach
            </div>
        @endif
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-6 py-3">Produit</th><th class="px-6 py-3">Stock</th><th class="px-6 py-3">Seuil</th><th class="px-6 py-3">Lots</th><th class="px-6 py-3">Action</th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($products as $product)
                            <tr>
                                <td class="px-6 py-4 font-semibold">{{ $product->name }}<div class="text-xs font-normal text-gray-500">{{ $product->sku }}</div></td>
                                <td class="px-6 py-4 {{ $product->stock_quantity <= $product->low_stock_threshold ? 'font-semibold text-red-600' : '' }}">{{ $product->stock_quantity }} {{ $product->unit->short_name }}</td>
                                <td class="px-6 py-4">{{ $product->low_stock_threshold }}</td>
                                <td class="px-6 py-4">{{ $product->stockLots->where('quantity', '>', 0)->count() }}</td>
                                <td class="px-6 py-4">
                                    @can('inventory.manage')
                                        <form method="POST" action="{{ route('stock.issue', $product) }}" class="flex gap-2">
                                            @csrf
                                            <input name="quantity" type="number" min="1" placeholder="Qté" class="w-20 rounded border-gray-300 text-sm" required>
                                            <input name="reason" placeholder="Motif" class="w-32 rounded border-gray-300 text-sm" required>
                                            <button class="rounded bg-red-700 px-3 py-1 text-xs font-semibold text-white">Sortir</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
