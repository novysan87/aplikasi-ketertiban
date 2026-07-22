@props(['href', 'icon', 'active' => false])

@php
$isActive = $active ?? request()->fullUrlIs($href) || request()->is(trim(parse_url($href, PHP_URL_PATH) ?? '', '/'));
$classes = $isActive
    ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-500'
    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';

$faIcons = [
    'home' => 'fa-solid fa-house',
    'plus-circle' => 'fa-solid fa-circle-plus',
    'exclamation-triangle' => 'fa-solid fa-triangle-exclamation',
    'users' => 'fa-solid fa-users',
    'clipboard-check' => 'fa-solid fa-clipboard-check',
    'document-text' => 'fa-solid fa-file-lines',
    'tag' => 'fa-solid fa-tag',
    'list' => 'fa-solid fa-list',
    'chart-bar' => 'fa-solid fa-chart-simple',
    'refresh' => 'fa-solid fa-rotate',
    'cog' => 'fa-solid fa-gear',
    'users-cog' => 'fa-solid fa-user-gear',
    'database' => 'fa-solid fa-database',
];
$faClass = $faIcons[$icon] ?? 'fa-solid fa-circle';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'group flex items-center px-3 py-2 text-sm font-medium rounded-md ' . $classes]) }}>
    <i class="{{ $faClass }} mr-3 w-5 text-center text-sm flex-shrink-0"></i>
    {{ $slot }}
</a>
