<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MiniMarket Togo') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-950 antialiased">
        <div class="grid min-h-screen bg-[#f5f7f4] lg:grid-cols-[0.85fr_1.15fr]">
            <aside class="relative hidden overflow-hidden bg-[#123c2d] px-10 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-16">
                <div class="absolute -right-24 -top-20 h-72 w-72 rounded-full border-[3rem] border-[#f4c542]/20"></div>
                <div class="absolute -bottom-16 -left-12 h-44 w-44 rounded-full bg-[#e14b3b]/80"></div>
                <a href="{{ url('/') }}" class="relative flex items-center gap-3" wire:navigate>
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#f4c542] text-xl font-black text-[#123c2d]">M</span>
                    <span class="text-sm font-bold tracking-wide">MiniMarket <span class="text-[#f4c542]">Togo</span></span>
                </a>
                <div class="relative max-w-md">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-100">Votre espace de gestion</p>
                    <h1 class="mt-5 text-4xl font-black leading-tight xl:text-5xl">Gardez le contrôle de chaque journée.</h1>
                    <p class="mt-5 leading-7 text-emerald-50/75">Caisse, stock, achats et crédit client réunis dans un espace simple pour votre commerce.</p>
                    <div class="mt-8 space-y-3 text-sm font-semibold text-emerald-50/85"><p><span class="mr-3 text-[#f4c542]">✓</span>Suivi des ventes en FCFA</p><p><span class="mr-3 text-[#f4c542]">✓</span>Stock et alertes de péremption</p><p><span class="mr-3 text-[#f4c542]">✓</span>Historique et traçabilité</p></div>
                </div>
                <p class="relative text-xs text-emerald-100/55">Une gestion pensée pour les commerces de proximité au Togo.</p>
            </aside>

            <main class="flex min-h-screen flex-col px-5 py-6 sm:px-8 lg:px-16 lg:py-10">
                <div class="flex items-center justify-between lg:justify-end">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 lg:hidden" wire:navigate><span class="grid h-9 w-9 place-items-center rounded-lg bg-[#123c2d] text-sm font-black text-[#f4c542]">M</span><span class="text-sm font-black text-[#123c2d]">MiniMarket Togo</span></a>
                    <a href="{{ url('/') }}" class="text-sm font-semibold text-slate-500 transition hover:text-emerald-800" wire:navigate>Retour à l’accueil <span class="ml-1">→</span></a>
                </div>
                <div class="flex flex-1 items-center justify-center py-10">
                    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-emerald-950/5 sm:p-9">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
