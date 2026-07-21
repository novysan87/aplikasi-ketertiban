@extends('layouts.app')

@section('title', $student->full_name)

@push('styles')
<style>
    .violation-timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: #e5e7eb;
    }
    .sp-progress-bar {
        background: linear-gradient(90deg, #dbeafe 0%, #93c5fd 40%, #60a5fa 70%, #3b82f6 100%);
    }
    .perspective-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .perspective-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.08);
    }
    .glass-stat {
        background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.98));
        backdrop-filter: blur(8px);
    }
</style>
@endpush

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <nav class="flex items-center space-x-2 text-sm text-gray-400 mb-2">
                <a href="{{ route('students.index') }}" class="hover:text-gray-600 transition">Data Siswa</a>
                <span>/</span>
                <span class="text-gray-700 font-medium truncate max-w-[200px]">{{ $student->full_name }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Profil Siswa</h1>
        </div>
        <a href="{{ route('students.index') }}"
            class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- ===== HERO CARD ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6 perspective-card">
        <div class="bg-gradient-to-r from-blue-500 via-blue-400 to-sky-300 h-28 sm:h-36"></div>
        <div class="relative px-6 pb-6">
            {{-- Avatar --}}
            <div class="flex flex-col sm:flex-row sm:items-end -mt-14 sm:-mt-20 mb-4 gap-4">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white shadow-lg border-2 border-white flex items-center justify-center flex-shrink-0">
                    @if($student->photo_path)
                        <img src="{{ Storage::url($student->photo_path) }}" alt="{{ $student->full_name }}"
                            class="w-full h-full rounded-2xl object-cover">
                    @else
                        <span class="text-3xl sm:text-4xl font-bold text-blue-600">{{ strtoupper(substr($student->full_name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0 pt-5 sm:pt-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 truncate leading-tight tracking-tight">{{ $student->full_name }}</h2>
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 text-sm font-mono font-semibold bg-gray-50 text-blue-700 border border-blue-200 rounded-xl">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
                                    {{ $student->nisn ?? '—' }}
                                </span>
                                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 text-sm font-medium bg-blue-50 text-blue-700 border border-blue-200 rounded-xl">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    {{ $student->class_name ?? '—' }}
                                </span>
                                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 text-sm font-medium bg-blue-50 text-blue-700 border border-blue-200 rounded-xl">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    {{ $student->department_name ?? $student->department_code ?? '—' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($student->is_active)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    Tidak Aktif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- SP Level Badge --}}
            @if($currentSpLevel)
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold mb-4"
                    style="background-color: {{ $currentSpLevel->color }}15; color: {{ $currentSpLevel->color }}; border: 1px solid {{ $currentSpLevel->color }}30;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $currentSpLevel->name }} — {{ $currentSpLevel->min_points }}+ poin
                </div>
            @endif

            {{-- Quick Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="glass-stat rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Total Poin</p>
                    <p class="text-2xl font-bold {{ $totalPoints >= 100 ? 'text-blue-600' : ($totalPoints >= 50 ? 'text-blue-500' : ($totalPoints > 0 ? 'text-blue-400' : 'text-gray-400')) }}">
                        {{ $totalPoints }}
                    </p>
                </div>
                <div class="glass-stat rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Pelanggaran</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $violationCount }}<span class="text-sm font-medium text-gray-400">x</span></p>
                </div>
                <div class="glass-stat rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">SP Terbit</p>
                    <p class="text-2xl font-bold {{ $activeSpLetters > 0 ? 'text-blue-600' : 'text-gray-400' }}">{{ $activeSpLetters }}</p>
                </div>
                <div class="glass-stat rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Terakhir</p>
                    <p class="text-sm font-semibold text-gray-700 mt-1">
                        @if($lastViolation)
                            {{ $lastViolation->violation_date->diffForHumans() }}
                        @else
                            —
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SP PROGRESS ===== --}}
    @if($spThresholds->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6 perspective-card">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-gray-900">Progress Ambang Surat Peringatan</h3>
            <span class="text-xs text-gray-400">{{ $totalPoints }} / {{ $nextSpThreshold ? $nextSpThreshold->min_points : $spThresholds->last()->min_points }} poin</span>
        </div>

        {{-- Progress Bar --}}
        @php
            $maxPoints = $nextSpThreshold ? $nextSpThreshold->min_points : $spThresholds->last()->min_points;
            if ($totalPoints >= $spThresholds->last()->min_points) {
                $progressPercent = 100;
            } else {
                $progressPercent = min(100, ($totalPoints / $maxPoints) * 100);
            }
        @endphp
        <div class="relative h-3 bg-gray-100 rounded-full overflow-hidden mb-4">
            <div class="sp-progress-bar h-full rounded-full transition-all duration-500 ease-out"
                style="width: {{ $progressPercent }}%"></div>
        </div>

        {{-- Threshold Markers --}}
        <div class="relative flex justify-between">
            @foreach($spThresholds as $threshold)
                @php
                    $tsReached = $totalPoints >= $threshold->min_points;
                @endphp
                <div class="flex flex-col items-center" style="flex:1">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="w-3 h-3 rounded-full {{ $tsReached ? 'shadow-sm' : 'bg-gray-300' }}"
                            style="background-color: {{ $tsReached ? $threshold->color : '' }}"></span>
                        <span class="text-xs font-semibold {{ $tsReached ? '' : 'text-gray-400' }}"
                            style="color: {{ $tsReached ? $threshold->color : '' }}">
                            {{ $threshold->min_points }}
                        </span>
                    </div>
                    <span class="text-[10px] {{ $tsReached ? 'font-medium' : 'text-gray-400' }} leading-tight text-center"
                        style="color: {{ $tsReached ? $threshold->color : '' }}">
                        {{ $threshold->name }}
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Status --}}
        @if($currentSpLevel)
            <div class="mt-5 p-3.5 rounded-xl text-sm flex items-start gap-3"
                style="background-color: {{ $currentSpLevel->color }}10; border: 1px solid {{ $currentSpLevel->color }}20;">
                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    style="color: {{ $currentSpLevel->color }}">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold" style="color: {{ $currentSpLevel->color }}">{{ $currentSpLevel->name }} sudah tercapai</p>
                    <p class="text-gray-500 mt-0.5 text-xs">Siswa ini telah mencapai ambang minimal {{ $currentSpLevel->min_points }} poin. Surat peringatan telah diterbitkan.</p>
                </div>
            </div>
        @elseif($nextSpThreshold && $violationCount > 0)
            <div class="mt-5 p-3.5 rounded-xl bg-blue-50 border border-blue-100 text-sm flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold text-blue-700">Menuju {{ $nextSpThreshold->name }}</p>
                    <p class="text-blue-600/70 mt-0.5 text-xs">{{ $nextSpThreshold->min_points - $totalPoints }} poin lagi untuk mencapai ambang {{ $nextSpThreshold->name }} ({{ $nextSpThreshold->min_points }} poin)</p>
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- ===== VIOLATIONS TIMELINE ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Riwayat Pelanggaran</h3>
                    <p class="text-xs text-gray-400">{{ $violationCount }} catatan pelanggaran</p>
                </div>
            </div>
        </div>

        @forelse($student->violations as $v)
            <div class="violation-timeline relative pl-14 pr-6 py-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }} hover:bg-gray-50/50 transition">
                {{-- Timeline dot --}}
                <div class="absolute left-[13px] top-[18px] w-[15px] h-[15px] rounded-full border-2 flex items-center justify-center"
                    style="border-color: {{ $v->violationType?->category?->color ?? '#9ca3af' }}; background-color: white;">
                    <span class="w-[7px] h-[7px] rounded-full" style="background-color: {{ $v->violationType?->category?->color ?? '#9ca3af' }}"></span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-gray-900">{{ $v->violationType->name ?? '—' }}</span>
                            @if($v->is_verified)
                                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium bg-blue-50 text-blue-600 border border-blue-200 rounded">✓ Terverifikasi</span>
                            @endif
                        </div>
                        @if($v->description)
                            <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $v->description }}</p>
                        @endif
                        <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $v->violation_date->format('d M Y') }}
                                @if($v->violation_time)
                                    {{ $v->violation_time }}
                                @endif
                            </span>
                            @if($v->location)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $v->location }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $v->recorder->name ?? '—' }}
                            </span>
                        </div>

                        {{-- Evidence photos --}}
                        @if($v->evidences && $v->evidences->count() > 0)
                            <div class="flex gap-2 mt-2">
                                @foreach($v->evidences->take(3) as $ev)
                                    <a href="{{ Storage::url($ev->file_path) }}" target="_blank"
                                        class="w-14 h-14 rounded-lg border border-gray-200 overflow-hidden hover:ring-2 hover:ring-blue-300 transition-all">
                                        <img src="{{ Storage::url($ev->file_path) }}" class="w-full h-full object-cover" alt="Bukti">
                                    </a>
                                @endforeach
                                @if($v->evidences->count() > 3)
                                    <div class="w-14 h-14 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-xs text-gray-400 font-medium">
                                        +{{ $v->evidences->count() - 3 }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-end flex-shrink-0">
                        <span class="text-sm font-bold {{ $v->points >= 50 ? 'text-blue-600' : ($v->points >= 15 ? 'text-blue-500' : 'text-blue-400') }}">
                            +{{ $v->points }}
                        </span>
                        @if($v->sanction)
                            <span class="text-[10px] text-gray-400 mt-0.5 line-clamp-1 max-w-[100px] text-right">{{ $v->sanction }}</span>
                        @endif
                        <a href="{{ route('violations.show', $v->id) }}" class="text-[10px] text-blue-500 hover:text-blue-700 font-medium mt-1 transition">Detail</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-medium text-gray-500 mb-1">Belum Ada Pelanggaran</h4>
                <p class="text-xs text-gray-400">Siswa ini belum memiliki catatan pelanggaran</p>
            </div>
        @endforelse
    </div>

    {{-- ===== SP LETTERS ===== --}}
    @if($student->spLetters->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Surat Peringatan</h3>
                    <p class="text-xs text-gray-400">{{ $student->spLetters->count() }} surat diterbitkan</p>
                </div>
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($student->spLetters as $sp)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50/50 transition group">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                            style="background-color: {{ $sp->spThreshold?->color ?? '#3b82f6' }}15">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                style="color: {{ $sp->spThreshold?->color ?? '#3b82f6' }}">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $sp->spThreshold?->name ?? 'SP' }}
                                @if($sp->status === 'draft')
                                    <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium bg-blue-50 text-blue-600 border border-blue-200 rounded ml-1.5">Draft</span>
                                @elseif($sp->status === 'issued')
                                    <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200 rounded ml-1.5">Terbit</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $sp->letter_number }} • {{ $sp->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('sp-letters.show', $sp->id) }}"
                            class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all">
                            Lihat
                        </a>
                        <a href="{{ route('sp-letters.print', $sp->id) }}"
                            class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-all">
                            Cetak
                        </a>
                    </div>
                    {{-- Always show on mobile --}}
                    <div class="flex items-center gap-2 flex-shrink-0 sm:hidden">
                        <a href="{{ route('sp-letters.show', $sp->id) }}" class="text-xs text-blue-600 font-medium">Lihat</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ===== STUDENT INFO DETAILS ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Informasi Lengkap</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">NISN</p>
                    <p class="text-sm font-medium text-gray-900 font-mono">{{ $student->nisn ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">NIS</p>
                    <p class="text-sm font-medium text-gray-900 font-mono">{{ $student->student_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Jenis Kelamin</p>
                    <p class="text-sm font-medium text-gray-900">{{ $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : '—') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Tempat, Tanggal Lahir</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $student->place_of_birth ?? '—' }}{{ $student->place_of_birth && $student->date_of_birth ? ', ' : '' }}
                        {{ $student->date_of_birth ? $student->date_of_birth->format('d M Y') : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Kelas</p>
                    <p class="text-sm font-medium text-gray-900">{{ $student->class_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Jurusan</p>
                    <p class="text-sm font-medium text-gray-900">{{ $student->department_name ?? $student->department_code ?? '—' }}</p>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Alamat</p>
                    <p class="text-sm text-gray-700">{{ $student->address ?? '—' }}</p>
                </div>
                @if($student->phone_number || $student->email)
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Telepon</p>
                    <p class="text-sm font-medium text-gray-900">{{ $student->phone_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Email</p>
                    <p class="text-sm font-medium text-gray-900">{{ $student->email ?? '—' }}</p>
                </div>
                @endif
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Tahun Akademik</p>
                    <p class="text-sm font-medium text-gray-900">{{ $student->academic_year_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Terakhir Sinkron</p>
                    <p class="text-sm font-medium text-gray-900">{{ $student->synced_at ? $student->synced_at->format('d M Y H:i') : '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
