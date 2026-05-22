@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' =>
        'w-full rounded-xl border border-slate-600 bg-slate-950 text-dark px-4 py-3 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
]) !!}>
