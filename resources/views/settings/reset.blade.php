@extends('layouts.app')

@section('title', 'Reset Aplikasi')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="fa-solid fa-rotate-left text-red-600 text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Reset Aplikasi</h1>
                <p class="text-sm text-gray-500 mt-0.5">Kembalikan aplikasi ke kondisi awal pabrik</p>
            </div>
        </div>
    </div>

    {{-- Warning Card --}}
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-6">
        <div class="flex items-start space-x-3">
            <i class="fa-solid fa-triangle-exclamation text-red-600 mt-0.5"></i>
            <div>
                <h3 class="text-sm font-semibold text-red-800">⚠️ Peringatan Keras!</h3>
                <p class="text-sm text-red-700 mt-1">Semua data berikut akan <strong>DIHAPUS PERMANEN</strong>:</p>
                <ul class="mt-2 space-y-1 text-sm text-red-700">
                    <li>✕ Pelanggaran & foto bukti</li>
                    <li>✕ Surat Peringatan & notifikasi</li>
                    <li>✕ Data siswa & kelas (hasil sinkron)</li>
                    <li>✕ Kategori, jenis pelanggaran & ambang SP</li>
                    <li>✕ User lain (selain admin utama)</li>
                    <li>✕ Pengaturan sekolah</li>
                    <li>✕ File foto yang sudah diupload</li>
                </ul>
                <p class="text-sm text-green-700 mt-3 font-medium">Yang <u>tetap</u> tersimpan:</p>
                <ul class="mt-1 space-y-0.5 text-sm text-green-700">
                    <li>✓ Akun super admin (username: <strong>admin</strong>)</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Data yang akan terhapus</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-4 bg-red-50 rounded-xl border border-red-100 text-center">
                    <p class="text-xs text-red-600 font-medium uppercase tracking-wider">Pelanggaran</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $stats['violations'] }}</p>
                </div>
                <div class="p-4 bg-orange-50 rounded-xl border border-orange-100 text-center">
                    <p class="text-xs text-orange-600 font-medium uppercase tracking-wider">Foto</p>
                    <p class="text-2xl font-bold text-orange-700 mt-1">{{ $stats['evidences'] }}</p>
                </div>
                <div class="p-4 bg-yellow-50 rounded-xl border border-yellow-100 text-center">
                    <p class="text-xs text-yellow-600 font-medium uppercase tracking-wider">SP</p>
                    <p class="text-2xl font-bold text-yellow-700 mt-1">{{ $stats['sp_letters'] }}</p>
                </div>
                <div class="p-4 bg-red-50 rounded-xl border border-red-100 text-center">
                    <p class="text-xs text-red-600 font-medium uppercase tracking-wider">Notifikasi</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $stats['notifications'] }}</p>
                </div>
                <div class="p-4 bg-purple-50 rounded-xl border border-purple-100 text-center">
                    <p class="text-xs text-purple-600 font-medium uppercase tracking-wider">Siswa</p>
                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ $stats['students'] }}</p>
                </div>
                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100 text-center">
                    <p class="text-xs text-indigo-600 font-medium uppercase tracking-wider">Kelas</p>
                    <p class="text-2xl font-bold text-indigo-700 mt-1">{{ $stats['classes'] }}</p>
                </div>
                <div class="p-4 bg-teal-50 rounded-xl border border-teal-100 text-center">
                    <p class="text-xs text-teal-600 font-medium uppercase tracking-wider">Settings</p>
                    <p class="text-2xl font-bold text-teal-700 mt-1">{{ $stats['settings'] }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-center">
                    <p class="text-xs text-gray-600 font-medium uppercase tracking-wider">User Lain</p>
                    <p class="text-2xl font-bold text-gray-700 mt-1">{{ $stats['users_other'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form action="{{ route('settings.reset.run') }}" method="POST" onsubmit="return confirm('⚠️ YAKIN INGIN RESET? Semua data kecuali akun admin akan hilang permanen!')">
            @csrf
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="fa-solid fa-lock mr-1.5 text-gray-400"></i>
                        Masukkan Password Admin untuk Konfirmasi
                    </label>
                    <input type="password" name="confirm_password" required
                        placeholder="Password akun admin saat ini"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
            </div>
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit"
                    class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                    <i class="fa-solid fa-bomb mr-2"></i>
                    Reset Aplikasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
