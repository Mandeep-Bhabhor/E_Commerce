<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center px-5 py-3 bg-red-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:ring-2 focus:ring-red-500 transition-all duration-200'
]) }}>
    {{ $slot }}
</button>