<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-3 min-h-[48px] bg-vert border border-transparent rounded-xl font-semibold text-base text-white hover:bg-[#0B4A38] focus:outline-none focus:ring-2 focus:ring-vert focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
