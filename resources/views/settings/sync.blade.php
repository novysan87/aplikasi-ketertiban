@extends('layouts.app')

@section('title', 'Sinkronisasi Data Siswa')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Sinkronisasi Data Siswa</h1>
        <p class="text-sm text-gray-500">Sinkronkan data siswa dari Database Kesiswaan</p>
    </div>

    {{-- Sync Database Kesiswaan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form action="{{ route('settings.sync.run') }}" method="POST" class="p-6 space-y-5">
            @csrf

            {{-- URL Kesiswaan --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">URL Database Kesiswaan</label>
                <input type="url" name="base_url" value="{{ old('base_url', $baseUrl) }}" required
                    placeholder="http://database-kesiswaan.local"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                @error('base_url') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                @if($baseUrl)
                    <p class="mt-1.5 text-xs text-green-600 flex items-center">
                        <i class="fa-solid fa-check"></i>
                        URL tersimpan: {{ $baseUrl }}
                    </p>
                @endif
            </div>

            {{-- Token --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Token Sinkronisasi</label>
                <div class="relative">
                    <input type="password" name="token" value="{{ old('token', $hasToken ? '********' : '') }}"
                        placeholder="Masukkan token akses"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition pr-24"
                        id="tokenInput"
                        @if($hasToken) readonly onfocus="this.removeAttribute('readonly'); this.value=''; this.type='password';" @endif>
                    @if($hasToken)
                        <button type="button" onclick="document.getElementById('tokenInput').removeAttribute('readonly'); document.getElementById('tokenInput').value=''; document.getElementById('tokenInput').focus();"
                            class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                            Ganti
                        </button>
                    @endif
                </div>
                @error('token') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                @if($hasToken)
                    <p class="mt-1.5 text-xs text-green-600 flex items-center">
                        <i class="fa-solid fa-check"></i>
                        Token sudah tersimpan. Klik <strong>"Ganti"</strong> untuk memperbarui.
                    </p>
                @else
                    <p class="mt-1 text-xs text-gray-500">Token didapatkan dari menu Pengaturan → Sync Tokens di Database Kesiswaan.</p>
                @endif
            </div>

            {{-- Info siswa --}}
            @if($studentCount > 0)
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-info"></i>
                        <div class="text-sm text-blue-700">
                            <strong>{{ $studentCount }}</strong> siswa sudah tersinkron. Sinkronisasi ulang akan memperbarui data yang sudah ada.
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tombol --}}
            <button type="submit"
                class="w-full px-4 py-3 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm flex items-center justify-center space-x-2">
                <i class="fa-solid fa-rotate"></i>
                <span>Jalankan Sinkronisasi</span>
            </button>
        </form>
    </div>

    {{-- E-Jurnal Sync Token --}}
    <div class="mt-8"></div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-4">
        <div class="p-6 space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Sinkronisasi Presensi E-Jurnal</h2>
                <p class="text-sm text-gray-500 mt-1">Generate token untuk diinput di Aplikasi E-Jurnal Guru agar bisa push data presensi.</p>
            </div>

            {{-- Token display --}}
            @if($hasEjurnalToken)
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5">
                            <i class="fa-solid fa-check-circle text-emerald-500"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-emerald-800">Token Aktif</div>
                            <div class="mt-2 flex items-center gap-2">
                                <code class="flex-1 block bg-white border border-emerald-200 rounded-lg px-3 py-2 text-sm font-mono text-emerald-900 select-all break-all" id="ejurnalTokenDisplay">{{ $ejurnalToken }}</code>
                                <button type="button" onclick="copyToken()"
                                    class="shrink-0 px-3 py-2 text-xs font-medium text-emerald-700 bg-emerald-100 border border-emerald-200 rounded-lg hover:bg-emerald-200 transition whitespace-nowrap">
                                    <i class="fa-regular fa-copy"></i> Salin
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-emerald-700">Salin token ini dan paste di menu Sinkronisasi Akademik → Aplikasi Ketertiban di E-Jurnal Guru.</p>
                        </div>
                    </div>
                </div>

                {{-- Regenerate --}}
                <form action="{{ route('settings.sync.ejurnal-token.generate') }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Regenerate token? Token lama akan langsung tidak berlaku.')"
                        class="w-full px-4 py-3 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate"></i>
                        Regenerate Token
                    </button>
                </form>

            @else
                {{-- Generate first token --}}
                <form action="{{ route('settings.sync.ejurnal-token.generate') }}" method="POST">
                    @csrf
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center">
                        <div class="text-sm text-slate-500 mb-3">Belum ada token. Generate satu sekarang:</div>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition shadow-sm">
                            <i class="fa-solid fa-key"></i>
                            Generate Token Sekarang
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
    function copyToken() {
        const el = document.getElementById('ejurnalTokenDisplay');
        if (!el) return;
        navigator.clipboard.writeText(el.textContent).then(() => {
            const btn = el.nextElementSibling;
            if (btn) {
                btn.innerHTML = '<i class="fa-regular fa-check"></i> Tersalin';
                setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy"></i> Salin'; }, 2000);
            }
        });
    }
    </script>
</div>
@endsection