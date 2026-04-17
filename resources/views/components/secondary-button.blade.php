<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center px-5 py-3 bg-slate-700 border border-slate-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-600 focus:ring-2 focus:ring-indigo-500 transition-all duration-200'
]) }}>
    {{ $slot }}
</button>