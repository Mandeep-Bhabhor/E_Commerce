<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center px-5 py-3 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200'
]) }}>
    {{ $slot }}
</button>