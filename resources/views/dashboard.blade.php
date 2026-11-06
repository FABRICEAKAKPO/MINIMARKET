<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">{{ now()->translatedFormat('l d F Y') }}</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Bonjour, {{ auth()->user()->name }}</h2>
            </div>
            <p class="text-sm text-slate-500">Voici l'activité de votre supérette en un coup d'œil.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="relative overflow-hidden rounded-2xl bg-[#123c2d] p-5 text-white shadow-lg shadow-emerald-950/10">
                <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10"></div>
                <p class="relative text-sm font-medium text-emerald-100">Ventes du jour</p>
                <p class="relative mt-3 text-3xl font-black tracking-tight">{{ number_format($salesTodayAmount, 0, ',', ' ') }} <span class="text-base font-bold text-[#f4c542]">FCFA</span></p>
                <p class="relative mt-2 text-xs text-emerald-100/75">{{ $salesTodayCount }} vente(s) enregistrée(s)</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">État de la caisse</p>
                    <span class="h-2.5 w-2.5 rounded-full {{ $cashSession ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>
                </div>
                <p class="mt-3 text-2xl font-black text-slate-950">{{ $cashSession ? 'Ouverte' : 'À ouvrir' }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $cashSession ? 'Session active depuis '.$cashSession->opened_at->format('H:i') : 'Aucune session en cours' }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Créances clients</p>
                <p class="mt-3 text-2xl font-black text-slate-950">{{ number_format($creditBalance, 0, ',', ' ') }} <span class="text-sm font-bold text-slate-500">FCFA</span></p>
                <a href="{{ route('customers.index') }}" class="mt-2 inline-flex text-xs font-bold text-emerald-700 hover:text-emerald-900">Voir le carnet de crédit <span class="ml-1">→</span></a>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Alertes stock</p>
                <p class="mt-3 text-2xl font-black text-slate-950">{{ $lowStockProducts->count() }}</p>
                <a href="{{ route('stock.index') }}" class="mt-2 inline-flex text-xs font-bold text-emerald-700 hover:text-emerald-900">Contrôler les produits <span class="ml-1">→</span></a>
            </div>
        </div>

        <div class="mb-8 flex flex-wrap items-center gap-3">
            @can('sales.create')
                <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#e14b3b] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#c83e31]">
                    <span class="grid h-6 w-6 place-items-center rounded-lg bg-white/20">+</span> Nouvelle vente
                </a>
            @endcan
            @can('inventory.manage')
                <a href="{{ route('stock.receive') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-emerald-300 hover:text-emerald-800">
                    Réceptionner du stock <span class="text-lg leading-none">↗</span>
                </a>
            @endcan
            @can('catalog.manage')
                <a href="{{ route('catalog.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-emerald-300 hover:text-emerald-800">
                    Ajouter un produit <span class="text-lg leading-none">+</span>
                </a>
            @endcan
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.35fr_1fr]">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="font-black text-slate-950">Dernières ventes</h3>
                        <p class="mt-1 text-xs text-slate-500">Les transactions les plus récentes</p>
                    </div>
                    @can('sales.create')
                        <a href="{{ route('pos.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">Ouvrir la caisse</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                            <tr><th class="px-5 py-3 font-bold">Référence</th><th class="px-5 py-3 font-bold">Client</th><th class="px-5 py-3 font-bold">Caissier</th><th class="px-5 py-3 text-right font-bold">Total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentSales as $sale)
                                <tr class="transition hover:bg-emerald-50/40">
                                    <td class="px-5 py-4"><p class="font-bold text-slate-800">{{ $sale->number }}</p><p class="text-xs text-slate-400">{{ $sale->created_at->format('d/m/Y H:i') }}</p></td>
                                    <td class="px-5 py-4 text-slate-600">{{ $sale->customer?->name ?? 'Client comptant' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $sale->user?->name ?? '—' }}</td>
                                    <td class="px-5 py-4 text-right font-black text-slate-900">{{ number_format($sale->total_xof, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">Aucune vente enregistrée pour le moment.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-1">
                <section class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5">
                    <div class="flex items-start justify-between"><div><p class="text-xs font-bold uppercase tracking-wider text-amber-700">À surveiller</p><h3 class="mt-1 font-black text-slate-950">Stock bas</h3></div><span class="rounded-lg bg-amber-200 px-2 py-1 text-xs font-black text-amber-800">{{ $lowStockProducts->count() }}</span></div>
                    <div class="mt-4 space-y-3">
                        @forelse ($lowStockProducts as $product)
                            <div class="flex items-center justify-between gap-3 text-sm"><span class="truncate font-semibold text-slate-700">{{ $product->name }}</span><span class="shrink-0 font-black text-amber-800">{{ (int) $product->stock_lots_sum_quantity }} restant(s)</span></div>
                        @empty
                            <p class="text-sm text-emerald-800">Tous les niveaux de stock sont corrects.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-red-200 bg-red-50/70 p-5">
                    <div class="flex items-start justify-between"><div><p class="text-xs font-bold uppercase tracking-wider text-red-700">Prévention pertes</p><h3 class="mt-1 font-black text-slate-950">Péremptions proches</h3></div><span class="rounded-lg bg-red-200 px-2 py-1 text-xs font-black text-red-800">{{ $expiringLots->count() }}</span></div>
                    <div class="mt-4 space-y-3">
                        @forelse ($expiringLots as $lot)
                            <div class="flex items-center justify-between gap-3 text-sm"><span class="truncate font-semibold text-slate-700">{{ $lot->product->name }}</span><span class="shrink-0 font-black text-red-800">{{ $lot->expires_at->format('d/m/Y') }}</span></div>
                        @empty
                            <p class="text-sm text-emerald-800">Aucune péremption dans les 30 prochains jours.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
