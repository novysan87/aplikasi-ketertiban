<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $schoolName = App\Models\Setting::getValue('school_name', 'Ketertiban');
        $appName = App\Models\Setting::getValue('app_name', 'Aplikasi Ketertiban');
        $logoPath = App\Models\Setting::getValue('school_logo');
    @endphp
    <title>{{ $schoolName }} - @yield('title', $appName)</title>

    {{-- Open Graph / Social Preview --}}
    <meta property="og:title" content="@yield('title', $appName) - {{ $schoolName }}">
    <meta property="og:description" content="Aplikasi Ketertiban Siswa {{ $schoolName }} — Manajemen pelanggaran, presensi, dan surat peringatan siswa.">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($logoPath)
        <meta property="og:image" content="{{ asset('storage/' . $logoPath) }}">
        <meta property="og:image:width" content="256">
        <meta property="og:image:height" content="256">
    @else
        <meta property="og:image" content="{{ asset('favicon.ico') }}">
    @endif
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', $appName) - {{ $schoolName }}">
    <meta name="twitter:description" content="Aplikasi Ketertiban Siswa {{ $schoolName }}.">

    <link rel="icon" type="image/png" href="{{ $logoPath ? asset('storage/' . $logoPath) : asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/png" href="{{ $logoPath ? asset('storage/' . $logoPath) : asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeInUp 0.3s ease-out; }
        .stat-card-glow:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.08); transform: translateY(-1px); }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeInUp 0.3s ease-out; }
        .stat-card-glow:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.08); transform: translateY(-1px); }
        .flatpickr-calendar { border-radius: 12px !important; box-shadow: 0 8px 30px rgba(0,0,0,0.12) !important; }
        .flatpickr-day.selected { border-radius: 8px !important; }
        .flatpickr-day.today { border-radius: 8px !important; }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased">
    <div x-data="notifications()" class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-white via-blue-50/30 to-white border-r border-gray-200 lg:translate-x-0 lg:static lg:inset-auto transition-transform duration-200 ease-in-out flex flex-col shadow-sm">
            <div class="flex items-start justify-between h-auto px-4 pt-4 pb-3 border-b border-gray-200">
                <a href="{{ route('dashboard') }}" class="min-w-0">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden">
                            @if($logoPath)
                                <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" class="w-full h-full object-contain p-0.5">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 leading-tight truncate max-w-[140px]">{{ $appName }}</p>
                        </div>
                    </div>

                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700 flex-shrink-0 ml-2 mt-0.5">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <nav class="mt-2 px-2 space-y-1">
                @canPermission('access-dashboard')
                <x-nav-item href="{{ route('dashboard') }}" icon="home" :active="request()->routeIs('dashboard')">Dashboard</x-nav-item>
                @endcanPermission
                @canPermission('input-violations')
                <x-nav-item href="{{ route('violations.create') }}" icon="plus-circle" :active="request()->routeIs('violations.create')">Input Pelanggaran</x-nav-item>
                @endcanPermission
                @canPermission('view-violations')
                <x-nav-item href="{{ route('violations.index') }}" icon="exclamation-triangle" :active="request()->routeIs('violations.index')">Data Pelanggaran</x-nav-item>
                @endcanPermission
                @canPermission('view-students')
                <x-nav-item href="{{ route('students.index') }}" icon="users" :active="request()->routeIs('students.index*')">Data Siswa</x-nav-item>
                @endcanPermission
                @canPermission('manage-attendance')
                <x-nav-item href="{{ route('attendances.index') }}" icon="clipboard-check" :active="request()->routeIs('attendances*')">Presensi Siswa</x-nav-item>
                @endcanPermission
                @canPermission('view-sp-letters')
                <x-nav-item href="{{ route('sp-letters.index') }}" icon="document-text" :active="request()->routeIs('sp-letters.*')">Surat Peringatan</x-nav-item>
                @endcanPermission

                @canPermission('categories-manage')
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Master Data</p>
                </div>
                <x-nav-item href="{{ route('settings.categories') }}" icon="tag" :active="request()->routeIs('settings.categories*')">Kategori Pelanggaran</x-nav-item>
                <x-nav-item href="{{ route('settings.violation-types') }}" icon="list" :active="request()->routeIs('settings.violation-types*')">Jenis Pelanggaran</x-nav-item>
                <x-nav-item href="{{ route('settings.thresholds') }}" icon="chart-bar" :active="request()->routeIs('settings.thresholds*')">Ambang SP</x-nav-item>
                @endcanPermission

                @canPermission('settings-manage')
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Administrasi</p>
                </div>
                @endcanPermission
                @canPermission('sync-data')
                <x-nav-item href="{{ route('settings.sync') }}" icon="refresh" :active="request()->routeIs('settings.sync*')">Sinkronisasi</x-nav-item>
                @endcanPermission
                @canPermission('users-manage')
                <x-nav-item href="{{ route('users.index') }}" icon="users-cog" :active="request()->routeIs('users.*')">Manajemen User</x-nav-item>
                <x-nav-item href="{{ route('settings.permissions') }}" icon="lock" :active="request()->routeIs('settings.permissions*')">Hak Akses Role</x-nav-item>
                @endcanPermission
                @canPermission('backup-database')
                <x-nav-item href="{{ route('settings.backup') }}" icon="database" :active="request()->routeIs('settings.backup*')">Backup Database</x-nav-item>
                @endcanPermission
                @canPermission('reset-application')
                <x-nav-item href="{{ route('settings.reset') }}" icon="arrows-rotate" :active="request()->routeIs('settings.reset*')">Reset Aplikasi</x-nav-item>
                @endcanPermission
                @canPermission('settings-manage')
                <x-nav-item href="{{ route('settings.index') }}" icon="cog" :active="request()->routeIs('settings.index')">Pengaturan</x-nav-item>
                @endcanPermission
            </nav>

            {{-- Footer --}}
            <div class="mt-auto px-4 py-3 border-t border-gray-100">
                <div class="text-center">
                    <p class="text-[10px] text-gray-400">Developed by</p>
                    <a href="https://noctkj.net/" target="_blank" rel="noopener noreferrer"
                        class="text-xs font-bold text-blue-500 hover:text-blue-700 transition block mt-0.5">
                        NOCTKJ.net
                    </a>
                </div>
            </div>
        </aside>

        {{-- Overlay for mobile --}}
        <div @click="sidebarOpen = false" x-show="sidebarOpen" class="fixed inset-0 z-40 bg-gray-600 bg-opacity-50 lg:hidden" style="display: none;"></div>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Topbar --}}
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-6">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <div class="flex-1"></div>

                <div class="flex items-center space-x-4">
                    {{-- Notifications --}}
                    <div class="relative" @click.outside="showNotif = false">
                        <button @click="showNotif = !showNotif" class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fa-solid fa-bell text-lg"></i>
                            <span x-text="notifCount" x-show="notifCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"></span>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="showNotif" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden z-50" style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
                                <button @click="markAllRead" class="text-xs text-blue-600 hover:text-blue-800">Tandai semua dibaca</button>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <template x-for="notif in notifications" :key="notif.id">
                                    <a :href="notif.action_url || '#'" @click="markAsRead(notif.id)" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                        <div class="flex items-start space-x-3">
                                            <div :class="'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center ' + (notif.color ? 'bg-'+notif.color+'-100' : 'bg-blue-100')">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate" x-text="notif.title"></p>
                                                <p class="text-xs text-gray-500 truncate" x-text="notif.body"></p>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                                <div x-show="notifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                                    Tidak ada notifikasi
                                </div>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-100 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Tampilkan semua notifikasi</a>
                            </div>
                        </div>
                    </div>

                    {{-- User Dropdown --}}
                    <div class="relative" @click.outside="showUserMenu = false">
                        <button @click="showUserMenu = !showUserMenu"
                            class="flex items-center gap-3 pl-3 pr-2 py-2 rounded-xl hover:bg-gray-50 transition group">
                            <div class="hidden sm:block text-right">
                                <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="showUserMenu"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden z-50" style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('profile.index') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Pengaturan Akun
                                </a>
                                <hr class="mx-3 my-1 border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:text-blue-600 hover:bg-red-50 transition">
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 lg:p-6 animate-fade-in">
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">&times;</button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-blue-200 rounded-lg text-sm text-red-700 flex items-center justify-between">
                        <span>{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr.localize(flatpickr.l10ns.id);
            document.querySelectorAll('input[type="date"]').forEach(function(el) {
                el.type = 'text';
                flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    altFormat: 'd/m/Y',
                    altInput: true,
                    allowInput: true,
                    animate: true,
                });
            });
        });
    </script>

    @stack('scripts')

    {{-- Reverb Config --}}
    <script>
        window.__REVERB_CONFIG = {
            key: @json(config('reverb.apps.apps.0.key') ?: env('REVERB_APP_KEY')),
            host: window.location.hostname,
            port: parseInt(window.location.port || '80'),
        };
        console.log('Reverb config:', window.__REVERB_CONFIG);
    </script>

    {{-- Reverb + Echo for realtime --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('notifications', () => ({
                showNotif: false,
                sidebarOpen: false,
                showUserMenu: false,
                notifications: [],
                notifCount: 0,
                init() {
                    this.fetchCount();
                    this.fetchNotifications();
                    this.connectReverb();
                },
                fetchCount() {
                    fetch('{{ route("notifications.unread-count") }}')
                        .then(r => r.json())
                        .then(d => { if (d.count !== undefined) this.notifCount = d.count; });
                },
                fetchNotifications() {
                    fetch('{{ route("notifications.recent") }}')
                        .then(r => r.json())
                        .then(d => { if (d.notifications) this.notifications = d.notifications; });
                },
                markAllRead() {
                    fetch('{{ route("notifications.read-all") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                    this.notifications = [];
                    this.notifCount = 0;
                },
                markAsRead(id) {
                    fetch('/notifications/' + id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                    this.notifications = this.notifications.filter(n => n.id !== id);
                    if (this.notifCount > 0) this.notifCount--;
                },
                connectReverb() {
                    if (typeof window.Echo === 'undefined' || window.Echo === null) {
                        try {
                            window.Echo = new window.EchoClass({
                                broadcaster: 'reverb',
                                key: window.__REVERB_CONFIG.key,
                                wsHost: window.__REVERB_CONFIG.host,
                                wsPort: window.__REVERB_CONFIG.port,
                                wssPort: 443,
                                forceTLS: false,
                                enabledTransports: ['ws', 'wss'],
                            });
                            console.log('Echo initialized ✅');
                        } catch(e) {
                            console.error('Echo init error:', e);
                        }
                    }
                    if (typeof window.Echo !== 'undefined') {
                        window.Echo.channel('violations')
                            .listen('.violation.recorded', (e) => {
                                // Refresh dari database untuk hindari duplikasi
                                this.fetchCount();
                                this.fetchNotifications();
                            });
                    } else {
                        // Fallback: poll every 30s if Echo not loaded
                        setInterval(() => { this.fetchCount(); }, 30000);
                    }
                }
            }));
        });
    </script>

    {{-- Global helpers --}}
    <script>
        window.confirmSwal = async function (opts = {}) {
            const result = await Swal.fire({
                title: opts.title || 'Konfirmasi',
                text: opts.text || 'Yakin ingin melanjutkan?',
                icon: opts.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: opts.confirmColor || '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: opts.confirmText || 'Ya, hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            });
            return result.isConfirmed;
        };
    </script>
</body>
</html>
