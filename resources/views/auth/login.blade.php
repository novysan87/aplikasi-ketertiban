<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $schoolName = App\Models\Setting::getValue('school_name', 'Ketertiban');
        $appName = App\Models\Setting::getValue('app_name', 'Aplikasi Ketertiban');
        $logoPath = App\Models\Setting::getValue('school_logo');
        $bgPath = App\Models\Setting::getValue('login_background');
    @endphp
    <title>Login - {{ $appName }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 px-3 py-3 sm:px-6 sm:py-6 antialiased">
    <div class="mx-auto grid min-h-[calc(100vh-1.5rem)] max-w-7xl overflow-hidden rounded-[24px] sm:rounded-3xl border border-slate-200 bg-white shadow-2xl lg:grid-cols-2 lg:min-h-[92vh]">
        {{-- Left Side: Branding --}}
        <div class="relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between">
            @if($bgPath)
                <img src="{{ asset('storage/' . $bgPath) }}" alt="Background" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-black/10"></div>
            @else
                <div class="absolute inset-0"
                    style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #3b82f6 100%);"></div>
                <div class="absolute inset-0"
                    style="background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 24px 24px;"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
            @endif

            <div class="relative z-10 p-10">
                <div class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-white/90 shadow-lg">
                    @if($logoPath)
                        <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" class="w-10 h-10 object-contain">
                    @else
                        <span class="text-xl font-extrabold tracking-tight text-blue-600">KT</span>
                    @endif
                </div>
            </div>

            <div class="relative z-10 mx-10 mb-10 rounded-3xl border border-white/15 bg-white/10 p-6 backdrop-blur-sm">
                <p class="text-sm font-semibold text-white">{{ $appName }}</p>
                <p class="text-xs text-white/50 mt-1">{{ $schoolName }}</p>
                <p class="mt-2 text-sm leading-6 text-white/70">
                    Sistem pencatatan pelanggaran siswa terintegrasi dengan manajemen poin, surat peringatan otomatis, dan notifikasi realtime.
                </p>
            </div>
        </div>

        {{-- Right Side: Form --}}
        <div class="flex items-center justify-center bg-white p-4 sm:p-8 lg:p-8">
            <div class="w-full max-w-sm">
                <div class="mb-5 text-center lg:text-left">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">{{ $appName }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $schoolName }}</p>
                    <h2 class="mt-2 text-2xl sm:text-[28px] font-semibold text-slate-900">Selamat Datang</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Masukkan kredensial Anda untuk mengelola data pelanggaran, memantau poin siswa, dan menerbitkan surat peringatan.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded-2xl border border-blue-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            required
                            autofocus
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('username') border-red-300 @enderror"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Masukkan password"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 pr-12 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
                            <button
                                type="button"
                                id="toggle-password"
                                class="absolute inline-flex items-center justify-center text-slate-500 transition hover:text-blue-600"
                                style="top: 50%; right: 16px; transform: translateY(-50%); width: 20px; height: 20px;"
                                aria-label="Tampilkan password"
                                aria-pressed="false"
                            >
                                <i id="toggle-password-icon" class="fa-regular fa-eye" style="font-size: 15px; line-height: 1;"></i>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>Ingat Saya</span>
                    </label>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('toggle-password');
            const toggleIcon = document.getElementById('toggle-password-icon');

            if (!passwordInput || !toggleButton) return;

            toggleButton.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleButton.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                toggleButton.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                if (toggleIcon) {
                    toggleIcon.className = isHidden ? 'fa-regular fa-eye-slash text-base' : 'fa-regular fa-eye text-base';
                }
            });
        });
    </script>
</body>
</html>
