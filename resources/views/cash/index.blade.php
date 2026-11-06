<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Caisse</h2></x-slot>
    <div class="mx-auto max-w-xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))<div class="mb-5 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>@endif
        @if ($session)
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="font-semibold">Caisse ouverte</h3>
                <p class="mt-2 text-sm text-gray-600">Montant attendu : <strong>{{ number_format($session->expected_amount_xof, 0, ',', ' ') }} FCFA</strong></p>
                <form method="POST" action="{{ route('cash.close', $session) }}" class="mt-6 space-y-4">@csrf
                    <x-input-label for="closing_amount_xof" value="Comptage final (FCFA)" />
                    <x-text-input id="closing_amount_xof" name="closing_amount_xof" type="number" min="0" class="block w-full" required />
                    <x-input-label for="closing_note" value="Note sur l'écart (facultative)" />
                    <textarea id="closing_note" name="closing_note" class="block w-full rounded-md border-gray-300"></textarea>
                    <button class="rounded-md bg-red-700 px-4 py-2 text-sm font-semibold text-white">Clôturer la caisse</button>
                </form>
            </div>
        @else
            <form method="POST" action="{{ route('cash.open') }}" class="space-y-5 rounded-lg bg-white p-6 shadow">@csrf
                <h3 class="font-semibold">Ouvrir une caisse</h3>
                <x-input-label for="opening_amount_xof" value="Fond de caisse initial (FCFA)" />
                <x-text-input id="opening_amount_xof" name="opening_amount_xof" type="number" min="0" value="0" class="block w-full" required />
                <button class="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white">Ouvrir la caisse</button>
            </form>
        @endif
    </div>
</x-app-layout>
