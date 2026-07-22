@extends('layouts.app')

@section('title', 'Sinkronisasi Data Siswa')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Sinkronisasi Data Siswa</h1>
        <p class="text-sm text-gray-500">Sinkronkan data siswa dari Database Kesiswaan</p>
    </div>

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
</div>
@endsection
