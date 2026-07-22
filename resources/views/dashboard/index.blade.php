@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500">Ringkasan pelanggaran siswa</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pelanggaran Hari Ini</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['today_violations'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Pelanggaran</p>
                    <p class="text-3xl font-bold text-orange-600 mt-1">{{ $stats['total_violations'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-list-check text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Siswa Aktif</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['total_students'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">SP Aktif (Draft)</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['active_sp'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-file-lines text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Violations --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Pelanggaran Terbaru</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentViolations as $v)
                    <a href="{{ route('violations.show', $v->id) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $v->violationType?->category?->color ?? '#6b7280' }}"></span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $v->student->full_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $v->violationType->name ?? '-' }} <span class="text-red-500 font-medium">+{{ $v->points }} poin</span></p>
                            </div>
                        </div>
                        <div class="text-right text-xs text-gray-400">
                            <p>{{ $v->violation_date->format('d/m/Y') }}</p>
                            <p>{{ $v->recorder->name ?? '-' }}</p>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada pelanggaran tercatat</div>
                @endforelse
            </div>
            <div class="px-5 py-3 border-t border-gray-100">
                <a href="{{ route('violations.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lihat semua &rarr;</a>
            </div>
        </div>

        {{-- Top Students + SP Alerts --}}
        <div class="space-y-6">
            {{-- Notifikasi Ambang SP --}}
            @if($spThresholds->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Ambang SP</h2>
                </div>
                <div class="p-5 space-y-3">
                    @foreach($spThresholds as $threshold)
                        @php
                            $studentsAtRisk = \App\Models\Student::where('is_active', true)->get()->filter(function($s) use ($threshold) {
                                $pts = $s->total_points;
                                $maxOk = $threshold->max_points ?? 9999;
                                return $pts >= $threshold->min_points && $pts <= $maxOk;
                            });
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-lg border" style="border-color: {{ $threshold->color }}20; background-color: {{ $threshold->color }}08">
                            <div>
                                <p class="text-sm font-semibold" style="color: {{ $threshold->color }}">{{ $threshold->name }}</p>
                                <p class="text-xs text-gray-500">{{ $threshold->min_points }}-{{ $threshold->max_points ?? '~' }} poin</p>
                            </div>
                            <span class="text-lg font-bold" style="color: {{ $threshold->color }}">{{ $studentsAtRisk->count() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Top 5 Siswa Poin Tertinggi --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Poin Tertinggi</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($topStudents as $student)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-blue-100 text-red-700 flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $student->full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->class_name }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-blue-600">{{ $student->total_points }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada data</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
