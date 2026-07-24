@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="text-center max-w-md mx-auto px-4">
        {{-- Icon --}}
        <div class="mx-auto w-20 h-20 rounded-2xl bg-gradient-to-br from-red-50 to-red-100 border border-red-200 flex items-center justify-center shadow-sm mb-6">
            <i class="fa-solid fa-shield-halved text-3xl text-red-400"></i>
        </div>

        {{-- Code --}}
        <div class="text-7xl font-black text-red-200 tracking-tight mb-2">403</div>

        {{-- Title --}}
        <h1 class="text-xl font-bold text-gray-900 mb-2">Akses Ditolak</h1>

        {{-- Message --}}
        <p class="text-sm text-gray-500 leading-relaxed mb-8">
            @if($exception->getMessage() && $exception->getMessage() !== '')
                {{ $exception->getMessage() }}
            @else
                Anda tidak memiliki izin untuk mengakses halaman ini.
            @endif
        </p>

        {{-- Actions --}}
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

        {{-- Decorative dots --}}
        <div class="mt-12 flex items-center justify-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-red-200"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-red-300"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
        </div>
    </div>
</div>
@endsection
