@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-bordure-champ focus:border-vert focus:ring-vert rounded-lg shadow-sm']) }}>
