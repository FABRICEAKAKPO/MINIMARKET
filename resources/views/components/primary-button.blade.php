<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl border border-transparent bg-[#e14b3b] px-5 py-3 text-sm font-black text-white shadow-sm transition duration-150 hover:bg-[#c83e31] focus:outline-none focus:ring-2 focus:ring-[#e14b3b]/40 focus:ring-offset-2 active:bg-[#b4362b]']) }}>
    {{ $slot }}
</button>
