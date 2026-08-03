@extends('layouts.app')

@section('title', $student->full_name)

@push('styles')
<style>
    .violation-timeline::before {
        content: '';
        position: absolute;
        left: 23px;
        top: 12px;
        bottom: 12px;
        width: 2px;
        background: #e5e7eb;
    }
    .sp-progress-bar {
        background: linear-gradient(90deg, #fbbf24 0%, #f97316 40%, #ef4444 70%, #dc2626 100%);
    }
</style>
@endpush

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-400 mb-1">
                <a href="{{ route('students.index') }}" class="hover:text-gray-600 transition">Data Siswa</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-medium truncate max-w-[200px]">{{ $student->full_name }}</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Profil Siswa</h1>
        </div>
        <a href="{{ route('students.index') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-gray-600 bg-white border border-slate-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition shadow-sm">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Kembali
        </a>
    </div>

    {{-- ===== HERO CARD ===== --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-blue-500 to-sky-400 p-6 sm:p-8 text-white shadow-xl shadow-blue-200/60 mb-6">
        <div class="pointer-events-none absolute -top-20 -right-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-12 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
            style="background-image: radial-gradient(circle at 25% 40%, #fff 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>

        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center">
            <div class="relative flex-shrink-0">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white/15 backdrop-blur-sm ring-1 ring-white/30 flex items-center justify-center overflow-hidden shadow-lg">
                    @if($student->photo_path)
                        <img src="{{ Storage::url($student->photo_path) }}" alt="{{ $student->full_name }}"
                            class="w-full h-full object-cover">
                    @else
                        <span class="text-4xl sm:text-5xl font-black text-white">{{ strtoupper(substr($student->full_name, 0, 1)) }}</span>
                    @endif
                </div>
                @if($student->is_active)
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-400 border-4 border-white shadow"></span>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <nav class="flex items-center gap-1.5 text-xs text-white/60 mb-1.5">
                    <a href="{{ route('students.index') }}" class="hover:text-white transition">Data Siswa</a>
                    <span>/</span>
                    <span class="text-white/80 font-medium truncate max-w-[200px]">{{ $student->full_name }}</span>
                </nav>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white truncate leading-tight tracking-tight">{{ $student->full_name }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono font-semibold bg-white/15 rounded-full ring-1 ring-white/25 backdrop-blur-sm">
                        <i class="fa-solid fa-id-card text-white/70"></i>
                        {{ $student->nisn ?? '—' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-emerald-400/20 text-emerald-100 rounded-full ring-1 ring-emerald-300/30">
                        <i class="fa-solid fa-building text-emerald-200"></i>
                        {{ $student->class_name ?? '—' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-purple-400/20 text-purple-100 rounded-full ring-1 ring-purple-300/30">
                        <i class="fa-solid fa-graduation-cap text-purple-200"></i>
                        {{ $student->department_name ?? $student->department_code ?? '—' }}
                    </span>
                    @if(!$student->is_active)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-500/30 text-gray-100 rounded-full ring-1 ring-gray-400/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                            Tidak Aktif
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('students.index') }}"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-sm font-bold text-white bg-white/10 ring-1 ring-white/25 rounded-xl hover:bg-white/20 transition backdrop-blur-sm shrink-0 self-start lg:self-center">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Kembali
            </a>
        </div>

        {{-- SP Level Badge --}}
        @if($currentSpLevel)
            <div class="relative z-10 mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold"
                style="background-color: {{ $currentSpLevel->color }}22; color: #fff; border: 1px solid {{ $currentSpLevel->color }}66;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ $currentSpLevel->name }} — {{ $currentSpLevel->min_points }}+ poin
            </div>
        @endif

        {{-- Stat Cards --}}
        <div class="relative z-10 grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
            <div class="relative overflow-hidden rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/20 p-4 shadow-lg">
                <div class="absolute right-0 top-0 w-12 h-12 opacity-15">
                    <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Total Poin</p>
                <p class="text-2xl font-black text-white mt-1">{{ $totalPoints }}</p>
            </div>
            <div class="relative overflow-hidden rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/20 p-4 shadow-lg">
                <div class="absolute right-0 top-0 w-12 h-12 opacity-15">
                    <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Pelanggaran</p>
                <p class="text-2xl font-black text-white mt-1">{{ $violationCount }}<span class="text-sm font-semibold text-white/60">x</span></p>
            </div>
            <div class="relative overflow-hidden rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/20 p-4 shadow-lg">
                <div class="absolute right-0 top-0 w-12 h-12 opacity-15">
                    <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">SP Terbit</p>
                <p class="text-2xl font-black text-white mt-1">{{ $activeSpLetters }}</p>
            </div>
            <div class="relative overflow-hidden rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/20 p-4 shadow-lg">
                <div class="absolute right-0 top-0 w-12 h-12 opacity-15">
                    <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Terakhir</p>
                <p class="text-base font-black text-white mt-1.5">
                    @if($lastViolation) {{ $lastViolation->violation_date->diffForHumans() }} @else — @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ===== SP PROGRESS ===== --}}
    @if($spThresholds->count() > 0)
        @php
            $lastMin = $spThresholds->last()->min_points;
            $progressPercent = min(100, ($totalPoints / $lastMin) * 100);
        @endphp
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-500 to-red-500 flex items-center justify-center shadow-md shadow-orange-200/50">
                    <i class="fa-solid fa-triangle-exclamation text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-gray-900">Progress Ambang SP</h3>
                    <p class="text-xs text-gray-400">Jarak menuju surat peringatan</p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-2xl font-black text-gray-900 leading-none">{{ $totalPoints }}<span class="text-sm font-bold text-gray-400"> poin</span></p>
                <p class="text-[10px] text-gray-400 mt-1">maks. {{ $lastMin }} poin</p>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            {{-- Bar + milestone --}}
            <div class="relative h-4 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                <div class="sp-progress-bar h-full rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                {{-- Tick marker di atas bar --}}
                @foreach($spThresholds as $threshold)
                    @php $markerLeft = min(100, ($threshold->min_points / $lastMin) * 100); @endphp
                    <div class="absolute top-0 bottom-0 w-0.5 bg-white/80" style="left: {{ $markerLeft }}%"></div>
                @endforeach
            </div>

            {{-- Label marker --}}
            <div class="relative h-11 mt-2">
                @foreach($spThresholds as $threshold)
                    @php
                        $tsReached = $totalPoints >= $threshold->min_points;
                        $markerLeft = min(100, ($threshold->min_points / $lastMin) * 100);
                    @endphp
                    <div class="absolute top-0 flex flex-col items-center -translate-x-1/2" style="left: {{ $markerLeft }}%">
                        <span class="w-3 h-3 rounded-full mb-1 shadow-sm {{ $tsReached ? '' : 'bg-gray-300' }}"
                            style="background-color: {{ $tsReached ? $threshold->color : '' }}"></span>
                        <span class="text-[11px] font-black leading-none {{ $tsReached ? '' : 'text-gray-400' }}"
                            style="color: {{ $tsReached ? $threshold->color : '' }}">{{ $threshold->min_points }}</span>
                        <span class="text-[9px] font-semibold mt-0.5 {{ $tsReached ? '' : 'text-gray-400' }}"
                            style="color: {{ $tsReached ? $threshold->color : '' }}">{{ $threshold->name }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Status box --}}
            @if($currentSpLevel)
                <div class="mt-4 p-4 rounded-2xl flex items-start gap-3 border"
                    style="background-color: {{ $currentSpLevel->color }}10; border-color: {{ $currentSpLevel->color }}30;">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                        style="background: linear-gradient(135deg, {{ $currentSpLevel->color }}, {{ $currentSpLevel->color }}bb);">
                        <i class="fa-solid fa-triangle-exclamation text-white"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-black" style="color: {{ $currentSpLevel->color }}">{{ $currentSpLevel->name }} Sudah Tercapai</p>
                        <p class="text-xs text-gray-500 mt-1">Siswa telah mencapai ambang <strong>{{ $currentSpLevel->min_points }}</strong> poin.
                            @if($nextSpThreshold) Menuju {{ $nextSpThreshold->name }} ({{ $nextSpThreshold->min_points }} poin). @endif
                        </p>
                    </div>
                    <span class="ml-auto inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-full shrink-0"
                        style="background-color: {{ $currentSpLevel->color }}20; color: {{ $currentSpLevel->color }};">
                        <i class="fa-solid fa-flag-checkered mr-1"></i> Tercapai
                    </span>
                </div>
            @elseif($nextSpThreshold && $violationCount > 0)
                <div class="mt-4 p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <i class="fa-solid fa-circle-info text-white"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-black text-amber-700">Menuju {{ $nextSpThreshold->name }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <div class="flex-1 h-1.5 bg-amber-100 rounded-full overflow-hidden max-w-[160px]">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full"
                                    style="width: {{ min(100, ($totalPoints / $nextSpThreshold->min_points) * 100) }}%"></div>
                            </div>
                            <p class="text-xs font-bold text-amber-600">{{ $nextSpThreshold->min_points - $totalPoints }} poin lagi</p>
                        </div>
                    </div>
                    <span class="shrink-0 text-right">
                        <p class="text-lg font-black text-amber-600 leading-none">{{ $totalPoints }}/{{ $nextSpThreshold->min_points }}</p>
                        <p class="text-[9px] text-amber-400 font-semibold mt-0.5">menuju target</p>
                    </span>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ===== VIOLATIONS TIMELINE ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="relative overflow-hidden bg-gradient-to-r from-orange-500 via-red-500 to-red-600 px-6 py-5 flex flex-wrap items-center justify-between gap-3">
            <div class="pointer-events-none absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
                style="background-image: radial-gradient(circle at 25% 40%, #fff 1.5px, transparent 1.5px); background-size: 20px 20px;"></div>
            <div class="relative z-10 flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-white/15 flex items-center justify-center ring-1 ring-white/25 backdrop-blur-sm">
                    <i class="fa-solid fa-triangle-exclamation text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-white">Riwayat Pelanggaran</h3>
                    <p class="text-xs text-white/75">{{ $violationCount }} catatan pelanggaran</p>
                </div>
            </div>
            <span class="relative z-10 inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-black text-white bg-white/15 ring-1 ring-white/25 rounded-xl backdrop-blur-sm">
                <i class="fa-solid fa-star text-[10px]"></i>
                Total +{{ $totalPoints }} poin
            </span>
        </div>

        @if($student->violations->count() > 0)
        <div class="relative pl-3">
            <div class="absolute left-[26px] top-4 bottom-4 border-l-2 border-dashed border-gray-300"></div>
        @foreach($student->violations as $v)
            @php
                $handleStatusLabels = [
                    'unhandled' => ['label' => 'Belum Ditangani', 'class' => 'bg-red-50 text-red-600 border-red-200'],
                    'in_progress' => ['label' => 'Dalam Proses', 'class' => 'bg-yellow-50 text-yellow-600 border-yellow-200'],
                    'resolved' => ['label' => 'Selesai', 'class' => 'bg-green-50 text-green-600 border-green-200'],
                ];
                $hs = $handleStatusLabels[$v->handling_status] ?? ['label' => $v->handling_status, 'class' => 'bg-gray-50 text-gray-600'];
            @endphp
            <div class="relative pl-20 pr-8 py-5 hover:bg-gray-50/40 transition">
                {{-- Bubble ikon kategori --}}
                <div class="absolute left-[7px] top-5 w-10 h-10 rounded-xl flex items-center justify-center shadow-md ring-4 ring-gray-50"
                    style="background: linear-gradient(135deg, {{ $v->violationType?->category?->color ?? '#6b7280' }}, {{ $v->violationType?->category?->color ?? '#6b7280' }}bb);">
                    <i class="fa-solid fa-triangle-exclamation text-white text-sm"></i>
                </div>

                <div class="space-y-2.5">
                    {{-- Top row: violation name + badges --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-sm font-black text-gray-900">{{ $v->violationType->name ?? '—' }}</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-full"
                                    style="background-color: {{ $v->violationType?->category?->color ?? '#6b7280' }}15; color: {{ $v->violationType?->category?->color ?? '#6b7280' }}">
                                    <span class="w-1 h-1 rounded-full" style="background-color: {{ $v->violationType?->category?->color ?? '#6b7280' }}"></span>
                                    {{ $v->violationType?->category?->name ?? '—' }}
                                </span>
                                {{-- Verification badge --}}
                                @if($v->is_verified)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-medium bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200">
                                        <span class="w-1 h-1 bg-emerald-500 rounded-full"></span>
                                        Terverifikasi
                                    </span>
                                @endif
                                {{-- Handling badge --}}
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-medium rounded-full border {{ $hs['class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $v->handling_status === 'resolved' ? 'bg-green-500' : ($v->handling_status === 'in_progress' ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
                                    {{ $hs['label'] }}
                                </span>
                            </div>
                            @if($v->description)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $v->description }}</p>
                            @endif
                        </div>

                        {{-- Poin --}}
                        <div class="flex flex-col items-center flex-shrink-0 min-w-[72px]">
                            <span class="inline-flex items-center px-3.5 py-1.5 text-sm font-black text-white rounded-xl shadow-md
                                {{ $v->points >= 50 ? 'bg-gradient-to-br from-red-500 to-red-600 shadow-red-200' : ($v->points >= 15 ? 'bg-gradient-to-br from-amber-500 to-orange-500 shadow-amber-200' : 'bg-gradient-to-br from-blue-500 to-blue-600 shadow-blue-200') }}">
                                +{{ $v->points }}
                            </span>
                            @if($v->sanction)
                                <span class="text-[10px] text-gray-400 leading-tight text-center line-clamp-2 mt-1 max-w-[72px]">{{ $v->sanction }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Metadata row --}}
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-gray-300"></i>
                            {{ $v->violation_date->format('d M Y') }}
                            @if($v->violation_time) {{ \Carbon\Carbon::parse($v->violation_time)->format('H:i') }} @endif
                        </span>
                        @if($v->location)
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-location-dot text-gray-300"></i>
                                {{ $v->location }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-user-pen text-gray-300"></i>
                            {{ $v->recorder->name ?? '—' }}
                        </span>
                    </div>

                    {{-- Evidence thumbs --}}
                    @if($v->evidences && $v->evidences->count() > 0)
                        <div class="flex gap-2">
                            @foreach($v->evidences->take(3) as $ev)
                                <a href="{{ Storage::url($ev->file_path) }}" target="_blank"
                                    class="w-10 h-10 rounded-lg border border-slate-200 overflow-hidden hover:ring-2 hover:ring-blue-300 transition-all flex-shrink-0">
                                    <img src="{{ Storage::url($ev->file_path) }}" class="w-full h-full object-cover" alt="Bukti">
                                </a>
                            @endforeach
                            @if($v->evidences->count() > 3)
                                <div class="w-10 h-10 rounded-lg bg-gray-50 border border-slate-200 flex items-center justify-center text-[10px] text-gray-400 font-medium flex-shrink-0">
                                    +{{ $v->evidences->count() - 3 }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Handlings --}}
                    @if($v->handlings && $v->handlings->count() > 0)
                        <div class="ml-0 space-y-1.5">
                            @foreach($v->handlings as $h)
                                <div class="inline-flex items-center gap-1.5 bg-white border border-gray-100 rounded-lg px-2.5 py-1.5 shadow-sm">
                                    <div class="w-5 h-5 rounded bg-gray-50 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-hand-holding-heart text-[9px] text-amber-500"></i>
                                    </div>
                                    <span class="text-[11px] font-medium text-gray-700">{{ $h->handling_type }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $h->handling_date->format('d/m') }}</span>
                                    @if($h->participants->count() > 0)
                                        <span class="w-px h-3 bg-gray-200"></span>
                                        @foreach($h->participants as $p)
                                            <span class="inline-flex items-center gap-1 text-[10px] text-gray-500">
                                                <span class="w-3.5 h-3.5 rounded-full bg-gray-100 flex items-center justify-center">
                                                    <span class="text-[6px] font-bold text-gray-500">{{ strtoupper(substr($p->user->name ?? '?', 0, 1)) }}</span>
                                                </span>
                                                {{ $p->user->name }}
                                                @if($p->role)
                                                    <span class="text-[9px] text-gray-400 bg-gray-50 px-1 rounded border border-gray-100">{{ $p->role }}</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Detail link --}}
                    <div class="flex justify-end">
                        <a href="{{ route('violations.show', $v->id) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-blue-600 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition shadow-sm">
                            Detail Pelanggaran
                            <i class="fa-solid fa-arrow-right text-[9px]"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
        </div>
        @else
            <div class="py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-face-smile text-gray-300 text-2xl"></i>
                </div>
                <h4 class="text-sm font-medium text-gray-500 mb-1">Belum Ada Pelanggaran</h4>
                <p class="text-xs text-gray-400">Siswa ini belum memiliki catatan pelanggaran</p>
            </div>
        @endif
    </div>

    {{-- ===== SP LETTERS ===== --}}
    @if($student->spLetters->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-file-lines text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Surat Peringatan</h3>
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
                            <i class="fa-solid fa-file-lines" style="color: {{ $sp->spThreshold?->color ?? '#3b82f6' }}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ $sp->spThreshold?->name ?? 'SP' }}
                                @if($sp->status === 'draft')
                                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium bg-yellow-50 text-yellow-600 rounded-full border border-yellow-200 ml-1.5">Draft</span>
                                @elseif($sp->status === 'issued')
                                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium bg-emerald-50 text-emerald-600 rounded-full border border-emerald-200 ml-1.5">Terbit</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $sp->letter_number }} • {{ $sp->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('sp-letters.show', $sp->id) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-slate-200 rounded-lg hover:bg-gray-50 transition">
                            <i class="fa-solid fa-eye"></i>
                            <span class="hidden sm:inline">Lihat</span>
                        </a>
                        <a href="{{ route('sp-letters.print', $sp->id) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                            <i class="fa-solid fa-print"></i>
                            <span class="hidden sm:inline">Cetak</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ===== INFO DETAILS ===== --}}
    <div x-data="{ showEditModal: false }">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm flex-shrink-0">
                    <i class="fa-solid fa-circle-info text-white text-sm"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 truncate">Informasi Lengkap</h3>
                    <p class="text-xs text-gray-400 truncate">Data diri siswa</p>
                </div>
            </div>
            <button @click="showEditModal = true"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition flex-shrink-0 shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i>
                Update Data
            </button>
        </div>
        <div class="p-6">
            @php
                // Identitas: kiri
                $identityLeft = [
                    ['NISN', $student->nisn, 'fa-id-card'],
                    ['NIS', $student->student_number, 'fa-hashtag'],
                ];
                // Identitas: kanan
                $identityRight = [
                    ['Jenis Kelamin', $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : '—'), 'fa-venus-mars'],
                    ['Tempat Lahir', $student->place_of_birth, 'fa-map-pin'],
                    ['Tanggal Lahir', $student->date_of_birth ? $student->date_of_birth->format('d M Y') : '—', 'fa-cake-candles'],
                ];
                // Akademik
                $academic = [
                    ['Kelas', $student->class_name, 'fa-building'],
                    ['Jurusan', $student->department_name ?? $student->department_code, 'fa-graduation-cap'],
                    ['Tahun Akademik', $student->academic_year_name, 'fa-calendar'],
                ];
                // Kontak
                $contact = [
                    ['Telepon', $student->phone_number, 'fa-phone'],
                    ['HP Orang Tua/Wali', $student->parent_phone, 'fa-user'],
                    ['Email', $student->email, 'fa-envelope'],
                ];
                // Sistem
                $system = [
                    ['Terakhir Sinkron', $student->synced_at ? $student->synced_at->format('d M Y H:i') : '—', 'fa-cloud-arrow-down'],
                ];
            @endphp

            <div class="space-y-5">
                {{-- Row: Identitas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Identitas Kiri --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-blue-100 flex items-center justify-center">
                                <i class="fa-solid fa-id-card text-blue-600 text-[10px]"></i>
                            </div>
                            <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Identitas</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($identityLeft as $f)
                                @if($f[1])
                                <div class="flex items-center justify-between bg-gray-50 rounded-xl border border-gray-100 px-4 py-3">
                                    <span class="text-xs text-gray-400">{{ $f[0] }}</span>
                                    <span class="text-sm font-semibold text-gray-900 {{ $f[0] === 'NISN' || $f[0] === 'NIS' ? 'font-mono' : '' }}">{{ $f[1] }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    {{-- Identitas Kanan --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-blue-100 flex items-center justify-center">
                                <i class="fa-solid fa-user text-blue-600 text-[10px]"></i>
                            </div>
                            <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Pribadi</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($identityRight as $f)
                                @if($f[1])
                                <div class="flex items-center justify-between bg-gray-50 rounded-xl border border-gray-100 px-4 py-3">
                                    <span class="text-xs text-gray-400">{{ $f[0] }}</span>
                                    <span class="text-sm font-semibold text-gray-900 max-w-[55%] text-right truncate">{{ $f[1] }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Row: Akademik --}}
                @php $academicHasValue = collect($academic)->filter(fn($f) => $f[1] !== '—' && $f[1] !== null)->count() > 0; @endphp
                @if($academicHasValue)
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-violet-100 flex items-center justify-center">
                            <i class="fa-solid fa-graduation-cap text-violet-600 text-[10px]"></i>
                        </div>
                        <span class="text-[11px] font-bold text-violet-600 uppercase tracking-wider">Akademik</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        @foreach($academic as $f)
                            @if($f[1])
                            <div class="bg-gray-50 rounded-xl border border-gray-100 px-4 py-3">
                                <p class="text-xs text-gray-400">{{ $f[0] }}</p>
                                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $f[1] }}</p>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Row: Kontak --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-emerald-100 flex items-center justify-center">
                            <i class="fa-solid fa-address-book text-emerald-600 text-[10px]"></i>
                        </div>
                        <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Kontak</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($contact as $f)
                            <div class="bg-gray-50 rounded-xl border border-gray-100 px-4 py-3">
                                <p class="text-xs text-gray-400">{{ $f[0] }}</p>
                                <p class="text-sm font-medium text-gray-900 mt-0.5 font-mono">{{ $f[1] ?: '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Alamat --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-blue-100 flex items-center justify-center">
                            <i class="fa-solid fa-location-dot text-blue-600 text-[10px]"></i>
                        </div>
                        <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Alamat</span>
                    </div>
                    <div class="bg-gray-50 rounded-xl border border-gray-100 px-4 py-3">
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $student->address ?: '—' }}</p>
                    </div>
                </div>

                {{-- Row: Sistem --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-gray-200 flex items-center justify-center">
                            <i class="fa-solid fa-gear text-gray-500 text-[10px]"></i>
                        </div>
                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Sistem</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($system as $f)
                            @if($f[1])
                            <div class="bg-gray-50 rounded-xl border border-gray-100 px-4 py-3">
                                <p class="text-xs text-gray-400">{{ $f[0] }}</p>
                                <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $f[1] }}</p>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-500 to-sky-400 px-7 py-6 sticky top-0 z-10">
                    <div class="pointer-events-none absolute -top-10 -right-10 w-44 h-44 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
                        style="background-image: radial-gradient(circle at 25% 40%, #fff 1.5px, transparent 1.5px); background-size: 20px 20px;"></div>
                    <div class="relative z-10 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center ring-1 ring-white/25 backdrop-blur-sm">
                                <i class="fa-solid fa-pen-to-square text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-white">Update Data Siswa</h3>
                                <p class="text-xs text-white/75">Perbarui data diri {{ $student->full_name }}</p>
                            </div>
                        </div>
                        <button @click="showEditModal = false"
                            class="w-9 h-9 rounded-xl bg-white/10 ring-1 ring-white/25 flex items-center justify-center text-white hover:bg-white/25 transition flex-shrink-0">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <form action="{{ route('students.update', $student->id) }}" method="POST" class="p-7">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="full_name" value="{{ $student->full_name }}" required
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                            <input type="text" name="nisn" value="{{ $student->nisn }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NIS</label>
                            <input type="text" name="student_number" value="{{ $student->student_number }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                            <select name="gender"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">—</option>
                                <option value="L" @selected($student->gender === 'L')>Laki-laki</option>
                                <option value="P" @selected($student->gender === 'P')>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                            <input type="text" name="place_of_birth" value="{{ $student->place_of_birth }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date" name="date_of_birth" value="{{ $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '' }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea name="address" rows="2"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">{{ $student->address }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                            <input type="text" name="phone_number" value="{{ $student->phone_number }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Orang Tua/Wali</label>
                            <input type="text" name="parent_phone" value="{{ $student->parent_phone }}" placeholder="08xxxxxxxxxx"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ $student->email }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                            <input type="text" name="class_name" value="{{ $student->class_name }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kelas</label>
                            <input type="text" name="class_level" value="{{ $student->class_level }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Jurusan</label>
                            <input type="text" name="department_code" value="{{ $student->department_code }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jurusan</label>
                            <input type="text" name="department_name" value="{{ $student->department_name }}"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                        <button type="button" @click="showEditModal = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-slate-200 rounded-xl hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm inline-flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection
