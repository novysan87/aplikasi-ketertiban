@extends('layouts.app')

@section('title', 'Presensi Siswa')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Presensi Siswa</h1>
            <p class="text-sm text-gray-500 mt-1">Pencatatan kehadiran siswa per jam pelajaran</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('attendances.recap') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-violet-600 bg-violet-50 border border-violet-200 rounded-xl hover:bg-violet-100 transition shadow-sm">
                <i class="fa-solid fa-chart-simple text-xs"></i>
                Rekap Bulanan
            </a>
            <a href="{{ route('attendances.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                <i class="fa-solid fa-plus text-xs"></i>
                Input Presensi
            </a>
        </div>
    </div>

    {{-- Quick Stats --}}
    @php
        $today = now()->toDateString();
        $todayAlpha = \App\Models\Attendance::where('date', $today)->where('status', 'alpha')->count();
        $todayTotal = \App\Models\Attendance::where('date', $today)->count();
        $monthTotal = \App\Models\Attendance::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $monthAlpha = \App\Models\Attendance::whereMonth('date', now()->month)->whereYear('date', now()->year)->where('status', 'alpha')->count();
        $recentDates = \App\Models\Attendance::select('date')
            ->distinct()
            ->orderByDesc('date')
            ->take(10)
            ->get();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10"><i class="fa-solid fa-clipboard-check text-white text-6xl"></i></div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Presensi Hari Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $todayTotal }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">data tercatat</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-500 to-red-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10"><i class="fa-solid fa-xmark text-white text-6xl"></i></div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Alpha Hari Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $todayAlpha }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">tanpa keterangan</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10"><i class="fa-solid fa-calendar text-white text-6xl"></i></div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Bulan Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $monthTotal }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">total presensi</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10"><i class="fa-solid fa-chart-bar text-white text-6xl"></i></div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Alpha Bulan Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $monthAlpha }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">pelanggaran</p>
            </div>
        </div>
    </div>

    {{-- Recent --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Riwayat Presensi</h3>
                    <p class="text-xs text-gray-400">10 hari terakhir</p>
                </div>
            </div>
        </div>
        @if($recentDates->count() > 0)
            <div class="divide-y divide-gray-50">
                @foreach($recentDates as $rd)
                    <a href="{{ route('attendances.create', ['date' => $rd->date->format('Y-m-d'), 'class_name' => request('class_name')]) }}"
                        class="flex items-center justify-between px-6 py-3.5 hover:bg-gray-50/50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-calendar text-emerald-500 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $rd->date->translatedFormat('l, d M Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $rd->date->diffForHumans() }}</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-arrow-right text-gray-300 group-hover:text-blue-500 transition"></i>
                    </a>
                @endforeach
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-clipboard text-gray-300 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-500 mb-0.5">Belum Ada Presensi</p>
                <p class="text-xs text-gray-400">Klik "Input Presensi" untuk memulai</p>
            </div>
        @endif
    </div>
</div>
@endsection
