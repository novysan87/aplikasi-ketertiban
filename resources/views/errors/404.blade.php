@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="text-center max-w-md mx-auto px-4">
        <div class="mx-auto w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 flex items-center justify-center shadow-sm mb-6">
            <i class="fa-solid fa-map text-3xl text-amber-400"></i>
        </div>

        <div class="text-7xl font-black text-amber-200 tracking-tight mb-2">404</div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Halaman Tidak Ditemukan</h1>
        <p class="text-sm text-gray-500 leading-relaxed mb-8">Halaman yang Anda cari mungkin telah dipindah atau tidak tersedia.</p>

        <div class="flex items-center justify-center gap-3">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Kembali
            </a>
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                <i class="fa-solid fa-house text-xs"></i>
                Dashboard
            </a>
        </div>

        <div class="mt-12 flex items-center justify-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-200"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-amber-300"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
        </div>
    </div>
</div>
@endsection
