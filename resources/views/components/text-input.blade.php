@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:ring-emerald-600']) }}>
