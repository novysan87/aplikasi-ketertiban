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

// Warna ikon per menu
$iconColors = [
    'home' => ['active' => 'text-blue-600', 'inactive' => 'text-blue-400/60 group-hover:text-blue-500'],
    'plus-circle' => ['active' => 'text-emerald-600', 'inactive' => 'text-emerald-400/60 group-hover:text-emerald-500'],
    'exclamation-triangle' => ['active' => 'text-red-600', 'inactive' => 'text-red-400/60 group-hover:text-red-500'],
    'users' => ['active' => 'text-violet-600', 'inactive' => 'text-violet-400/60 group-hover:text-violet-500'],
    'clipboard-check' => ['active' => 'text-cyan-600', 'inactive' => 'text-cyan-400/60 group-hover:text-cyan-500'],
    'document-text' => ['active' => 'text-amber-600', 'inactive' => 'text-amber-400/60 group-hover:text-amber-500'],
    'tag' => ['active' => 'text-pink-600', 'inactive' => 'text-pink-400/60 group-hover:text-pink-500'],
    'list' => ['active' => 'text-orange-600', 'inactive' => 'text-orange-400/60 group-hover:text-orange-500'],
    'chart-bar' => ['active' => 'text-indigo-600', 'inactive' => 'text-indigo-400/60 group-hover:text-indigo-500'],
    'refresh' => ['active' => 'text-teal-600', 'inactive' => 'text-teal-400/60 group-hover:text-teal-500'],
    'cog' => ['active' => 'text-slate-600', 'inactive' => 'text-slate-400/60 group-hover:text-slate-500'],
    'users-cog' => ['active' => 'text-purple-600', 'inactive' => 'text-purple-400/60 group-hover:text-purple-500'],
    'database' => ['active' => 'text-sky-600', 'inactive' => 'text-sky-400/60 group-hover:text-sky-500'],
    'arrows-rotate' => ['active' => 'text-rose-600', 'inactive' => 'text-rose-400/60 group-hover:text-rose-500'],
    'lock' => ['active' => 'text-yellow-600', 'inactive' => 'text-yellow-400/60 group-hover:text-yellow-500'],
];
$ic = $iconColors[$icon] ?? ['active' => 'text-blue-600', 'inactive' => 'text-gray-400 group-hover:text-gray-600'];
$iconClass = $isActive ? $ic['active'] : $ic['inactive'];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 ' . $classes]) }}>
    <i class="{{ $faClass }} mr-3 w-5 text-center text-sm flex-shrink-0 {{ $iconClass }} transition-colors duration-150"></i>
    {{ $slot }}
</a>
