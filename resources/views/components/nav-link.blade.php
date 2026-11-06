@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-lg border-b-2 border-[#f4c542] bg-white/15 px-3 py-2 text-sm font-semibold leading-5 text-white transition duration-150 ease-in-out focus:outline-none'
            : 'inline-flex items-center rounded-lg border-b-2 border-transparent px-3 py-2 text-sm font-medium leading-5 text-emerald-50/75 transition duration-150 ease-in-out hover:bg-white/10 hover:text-white focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
