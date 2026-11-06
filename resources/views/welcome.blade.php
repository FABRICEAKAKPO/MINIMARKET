<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MiniMarket Togo') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f7f4] font-sans text-slate-950 antialiased">
    <main class="min-h-screen overflow-hidden">
        <section class="relative bg-[#123c2d] text-white">
            <div class="absolute right-[-8rem] top-[-7rem] h-72 w-72 rounded-full border-[3rem] border-[#f4c542]/20"></div>
            <div class="absolute bottom-[-5rem] left-[-3rem] h-40 w-40 rounded-full bg-[#e14b3b]/80"></div>
            <nav class="relative mx-auto flex max-w-[1440px] items-center justify-between px-5 py-5 sm:px-8 lg:px-12">
                <a href="{{ url('/') }}" class="flex items-center gap-3" wire:navigate>
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#f4c542] text-xl font-black text-[#123c2d]">M</span>
                    <span class="text-sm font-bold tracking-wide">MiniMarket <span class="text-[#f4c542]">Togo</span></span>
                </a>
                <div class="flex items-center gap-3 text-sm font-semibold">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg border border-white/25 px-4 py-2 text-white transition hover:bg-white/10">Accéder au tableau de bord</a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="rounded-lg border border-white/25 px-4 py-2 text-white transition hover:bg-white/10">Se connecter</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="hidden rounded-lg bg-[#f4c542] px-4 py-2 text-[#123c2d] transition hover:bg-[#ffd867] sm:inline-flex">Créer un accès</a>
                        @endif
                    @endauth
                </div>
            </nav>

            <div class="relative mx-auto grid max-w-[1440px] gap-12 px-5 pb-16 pt-12 sm:px-8 sm:pb-20 lg:grid-cols-[0.95fr_1.05fr] lg:items-center lg:px-12 lg:pb-24 lg:pt-20">
                <div class="max-w-xl">
                    <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-emerald-100"><span class="h-2 w-2 rounded-full bg-[#f4c542]"></span> Gestion commerciale de proximité</p>
                    <h1 class="text-4xl font-black leading-[1.05] tracking-tight sm:text-6xl">Votre commerce, <span class="text-[#f4c542]">simplement maîtrisé.</span></h1>
                    <p class="mt-6 max-w-lg text-base leading-7 text-emerald-50/80 sm:text-lg">Une caisse rapide, un stock fiable et une vision claire de votre activité, conçus pour les réalités des commerces à Lomé.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#e14b3b] px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-black/10 transition hover:bg-[#c83e31]">Ouvrir mon espace <span class="text-lg">→</span></a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#e14b3b] px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-black/10 transition hover:bg-[#c83e31]">Ouvrir ma session <span class="text-lg">→</span></a>
                        @endauth
                        <a href="#espaces" class="inline-flex items-center gap-2 rounded-xl border border-white/25 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">Voir les espaces</a>
                    </div>
                    <div class="mt-10 flex flex-wrap gap-x-6 gap-y-2 text-xs font-semibold text-emerald-100/70"><span>FCFA sans décimales</span><span>TVA configurable</span><span>Traçabilité intégrée</span></div>
                </div>

                <div class="relative lg:pl-8">
                    <div class="rounded-[1.5rem] border border-white/15 bg-[#f5f7f4] p-3 shadow-2xl shadow-black/20 sm:p-5">
                        <div class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4"><div><p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">Aperçu de gestion</p><p class="mt-1 text-lg font-black text-slate-900">La journée en un coup d'œil</p></div><span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">En ligne</span></div>
                            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div class="rounded-xl bg-[#123c2d] p-3 text-white"><p class="text-[10px] text-emerald-100">Ventes</p><p class="mt-2 text-lg font-black">248 500</p><p class="text-[10px] text-[#f4c542]">FCFA aujourd'hui</p></div>
                                <div class="rounded-xl bg-emerald-50 p-3"><p class="text-[10px] text-emerald-700">Caisse</p><p class="mt-2 text-lg font-black text-slate-900">Ouverte</p><p class="text-[10px] text-slate-500">Session active</p></div>
                                <div class="rounded-xl bg-amber-50 p-3"><p class="text-[10px] text-amber-700">Stock bas</p><p class="mt-2 text-lg font-black text-slate-900">08</p><p class="text-[10px] text-slate-500">à surveiller</p></div>
                                <div class="rounded-xl bg-red-50 p-3"><p class="text-[10px] text-red-700">Crédit</p><p class="mt-2 text-lg font-black text-slate-900">76 000</p><p class="text-[10px] text-slate-500">FCFA à recouvrer</p></div>
                            </div>
                            <div class="mt-5 rounded-xl border border-slate-100 p-4"><div class="flex items-center justify-between"><p class="text-xs font-bold text-slate-700">Activité des ventes</p><p class="text-[10px] text-slate-400">Aujourd'hui</p></div><div class="mt-5 flex h-24 items-end gap-2 sm:gap-3">@foreach ([35, 54, 42, 68, 51, 82, 63, 94, 76, 88, 72, 100] as $height)<span class="flex-1 rounded-t-md bg-[#f4c542]" style="height: {{ $height }}%"></span>@endforeach</div><div class="mt-2 flex justify-between text-[9px] text-slate-400"><span>08h</span><span>12h</span><span>16h</span><span>20h</span></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="espaces" class="mx-auto max-w-[1440px] px-5 py-14 sm:px-8 lg:px-12 lg:py-20">
            <div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Un seul espace de travail</p><h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Les bons outils au bon moment.</h2><p class="mt-4 leading-7 text-slate-500">Chaque écran est pensé pour aller vite, limiter les erreurs et garder une trace claire des opérations.</p></div>
            <div class="mt-10 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 text-xl text-emerald-800">▦</span><h3 class="mt-5 font-black text-slate-950">Caisse & ventes</h3><p class="mt-2 text-sm leading-6 text-slate-500">Encaissez en espèces, mobile money ou crédit avec des tickets clairs.</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><span class="grid h-11 w-11 place-items-center rounded-xl bg-amber-100 text-xl text-amber-800">◫</span><h3 class="mt-5 font-black text-slate-950">Stock & achats</h3><p class="mt-2 text-sm leading-6 text-slate-500">Suivez les lots, les seuils et les réceptions avant les ruptures.</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><span class="grid h-11 w-11 place-items-center rounded-xl bg-red-100 text-xl text-red-800">◌</span><h3 class="mt-5 font-black text-slate-950">Clients & suivi</h3><p class="mt-2 text-sm leading-6 text-slate-500">Gardez un carnet de crédit transparent et des relevés accessibles.</p></div>
            </div>
        </section>

        <footer class="border-t border-slate-200 bg-white px-5 py-6 sm:px-8 lg:px-12"><div class="mx-auto flex max-w-[1440px] flex-col gap-2 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between"><span class="font-bold text-slate-700">MiniMarket <span class="text-emerald-700">Togo</span></span><span>Gestion simple, décisions sereines.</span></div></footer>
    </main>
</body>
</html>
