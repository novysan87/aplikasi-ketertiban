@props(['href', 'icon', 'active' => false])

@php
$isActive = $active ?? request()->fullUrlIs($href) || request()->is(trim(parse_url($href, PHP_URL_PATH) ?? '', '/'));
$classes = $isActive
    ? 'bg-gradient-to-r from-blue-50 to-white text-blue-700 border-l-[3px] border-blue-500 shadow-sm'
    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/70 border-l-[3px] border-transparent';

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
    'arrows-rotate' => 'fa-solid fa-arrows-rotate',
    'lock' => 'fa-solid fa-lock',
];
$faClass = $faIcons[$icon] ?? 'fa-solid fa-circle';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 ' . $classes]) }}>
    <i class="{{ $faClass }} mr-3 w-5 text-center text-sm flex-shrink-0 {{ $isActive ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
    {{ $slot }}
</a>
