<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Administration</p><h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Utilisateurs et accès</h1></div>
            <p class="text-sm text-slate-500">Seul le propriétaire peut créer ou attribuer un accès.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))<div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif

        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6"><h2 class="font-black text-slate-950">Créer un accès</h2><p class="mt-1 text-sm leading-6 text-slate-500">Le rôle choisi détermine les fonctionnalités visibles dans la navigation.</p></div>
                <form method="POST" action="{{ route('users.store') }}" class="space-y-4">@csrf
                    <div><x-input-label for="name" value="Nom complet" /><x-text-input id="name" name="name" class="mt-1 block w-full" required /></div>
                    <div><x-input-label for="email" value="Adresse email" /><x-text-input id="email" name="email" type="email" class="mt-1 block w-full" required /></div>
                    <div><x-input-label for="password" value="Mot de passe temporaire" /><x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required /></div>
                    <div><x-input-label for="password_confirmation" value="Confirmer le mot de passe" /><x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required /></div>
                    <div><x-input-label for="role" value="Rôle et permissions" /><select id="role" name="role" class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-600 focus:ring-emerald-600" required><option value="">Sélectionner un rôle</option>@foreach ($roles as $role)<option value="{{ $role->name }}">{{ $role->name }} ({{ $role->permissions->count() }} permissions)</option>@endforeach</select></div>
                    <button class="w-full rounded-xl bg-[#123c2d] px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-900">Créer l’accès</button>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5"><h2 class="font-black text-slate-950">Comptes existants</h2><p class="mt-1 text-sm text-slate-500">Les opérations sensibles restent journalisées.</p></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm"><thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400"><tr><th class="px-6 py-3">Utilisateur</th><th class="px-6 py-3">Rôle</th><th class="px-6 py-3">Dernière connexion</th><th class="px-6 py-3">État</th><th class="px-6 py-3">Action</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($users as $user)<tr class="hover:bg-emerald-50/30"><td class="px-6 py-4"><p class="font-bold text-slate-800">{{ $user->name }}</p><p class="text-xs text-slate-500">{{ $user->email }}</p></td><td class="px-6 py-4"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">{{ $user->roles->pluck('name')->join(', ') ?: 'Aucun rôle' }}</span></td><td class="px-6 py-4 text-slate-600">{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Jamais' }}</td><td class="px-6 py-4">@if (! $user->is_active)<span class="font-bold text-slate-500">Désactivé</span>@elseif ($user->locked_until?->isFuture())<span class="font-bold text-red-700">Verrouillé</span>@else<span class="font-bold text-emerald-700">Actif</span>@endif</td><td class="px-6 py-4">@if (! $user->is(auth()->user()) && $user->is_active)<form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Désactiver cet accès ? L’historique sera conservé.');">@csrf @method('DELETE')<button class="font-bold text-red-700 hover:text-red-900">Désactiver</button></form>@else<span class="text-xs text-slate-400">—</span>@endif</td></tr>@empty<tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Aucun utilisateur.</td></tr>@endforelse</tbody></table></div>
                <div class="border-t border-slate-100 px-6 py-4">{{ $users->links() }}</div>
            </section>
        </div>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-black text-slate-950">Rôles disponibles</h2><div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">@foreach ($roles as $role)<div class="rounded-xl bg-slate-50 p-4"><p class="font-bold text-slate-800">{{ $role->name }}</p><p class="mt-1 text-xs leading-5 text-slate-500">{{ $role->permissions->pluck('name')->join(', ') }}</p></div>@endforeach</div></section>
    </div>
</x-app-layout>
