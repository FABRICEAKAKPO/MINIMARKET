<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Rapports & transparence</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Rapport des ventes</h1>
            </div>
            <p class="text-sm text-slate-500">Période du {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:hidden sm:flex-row sm:items-end sm:justify-between">
            <form method="GET" action="{{ route('reports.sales') }}" class="grid gap-3 sm:grid-cols-[auto_auto_auto] sm:items-end">
                <div><label for="from" class="block text-xs font-bold text-slate-600">Du</label><input id="from" name="from" type="date" value="{{ $from->toDateString() }}" class="mt-1 rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-emerald-600 focus:ring-emerald-600"></div>
                <div><label for="to" class="block text-xs font-bold text-slate-600">Au</label><input id="to" name="to" type="date" value="{{ $to->toDateString() }}" class="mt-1 rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-emerald-600 focus:ring-emerald-600"></div>
                <button class="rounded-xl bg-[#123c2d] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-900">Actualiser</button>
            </form>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.stock') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-800">État du stock</a>
                <a href="{{ route('reports.credit') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-800">Journal crédit</a>
                <a href="{{ route('reports.sales.csv', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-800">Télécharger CSV</a>
                <button onclick="window.print()" class="rounded-xl bg-[#e14b3b] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#c83e31]">Imprimer</button>
            </div>
        </div>

        <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-[#123c2d] p-5 text-white shadow-sm"><p class="text-sm text-emerald-100">Chiffre d'affaires</p><p class="mt-2 text-2xl font-black">{{ number_format($completedAmount, 0, ',', ' ') }} <span class="text-sm text-[#f4c542]">FCFA</span></p><p class="mt-1 text-xs text-emerald-100/70">{{ $completedCount }} vente(s) terminée(s)</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">TVA collectée</p><p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($taxAmount, 0, ',', ' ') }} <span class="text-sm text-slate-500">FCFA</span></p><p class="mt-1 text-xs text-slate-400">Calculée sur les ventes terminées</p></div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm"><p class="text-sm text-amber-700">Annulations</p><p class="mt-2 text-2xl font-black text-slate-950">{{ $cancelledCount }}</p><p class="mt-1 text-xs text-amber-800">{{ number_format($cancelledAmount, 0, ',', ' ') }} FCFA concernés</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Paiements encaissés</p><div class="mt-2 space-y-1 text-xs text-slate-600">@forelse ($paymentSummary as $method => $amount)<p class="flex justify-between gap-3"><span>{{ match ($method) { 'cash' => 'Espèces', 'tmoney' => 'T-Money', 'flooz' => 'Flooz', 'card' => 'Carte', 'credit' => 'Crédit', default => ucfirst($method) } }}</span><strong>{{ number_format($amount, 0, ',', ' ') }} FCFA</strong></p>@empty<p>Aucun paiement sur la période.</p>@endforelse</div></div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5"><h2 class="font-black text-slate-950">Journal des ventes</h2><p class="mt-1 text-xs text-slate-500">Les ventes annulées restent visibles pour préserver la traçabilité.</p></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">Référence</th><th class="px-5 py-3">Date</th><th class="px-5 py-3">Client</th><th class="px-5 py-3">Caissier</th><th class="px-5 py-3">Statut</th><th class="px-5 py-3 text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($sales as $sale)
                            <tr class="hover:bg-emerald-50/30"><td class="px-5 py-4 font-bold text-slate-800">{{ $sale->number }}</td><td class="px-5 py-4 text-slate-600">{{ $sale->created_at->format('d/m/Y H:i') }}</td><td class="px-5 py-4 text-slate-600">{{ $sale->customer?->name ?? 'Client comptant' }}</td><td class="px-5 py-4 text-slate-600">{{ $sale->user?->name ?? '—' }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $sale->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">{{ $sale->status === 'completed' ? 'Terminée' : 'Annulée' }}</span></td><td class="px-5 py-4 text-right font-black text-slate-900">{{ number_format($sale->total_xof, 0, ',', ' ') }} FCFA</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Aucune vente sur cette période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <p class="mt-5 text-xs text-slate-400">Document généré le {{ now()->format('d/m/Y à H:i') }} par {{ auth()->user()->name }}. Les montants sont exprimés en FCFA, sans décimales.</p>
    </div>
</x-app-layout>
