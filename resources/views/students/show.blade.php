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
        background: linear-gradient(90deg, #dbeafe 0%, #93c5fd 40%, #60a5fa 70%, #3b82f6 100%);
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
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition shadow-sm">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Kembali
        </a>
    </div>

    {{-- ===== HERO CARD ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-blue-500 via-blue-400 to-sky-300 h-24 sm:h-32"></div>
        <div class="relative px-6 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end -mt-12 sm:-mt-16 mb-5 gap-4">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white shadow-lg border-2 border-white flex items-center justify-center flex-shrink-0">
                    @if($student->photo_path)
                        <img src="{{ Storage::url($student->photo_path) }}" alt="{{ $student->full_name }}"
                            class="w-full h-full rounded-2xl object-cover">
                    @else
                        <span class="text-3xl sm:text-4xl font-bold text-blue-600">{{ strtoupper(substr($student->full_name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0 pt-5 sm:pt-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 truncate leading-tight tracking-tight">{{ $student->full_name }}</h2>
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-mono font-semibold bg-gray-100 text-blue-700 rounded-full border border-blue-200">
                                    <i class="fa-solid fa-id-card text-blue-400"></i>
                                    {{ $student->nisn ?? '—' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200">
                                    <i class="fa-solid fa-building text-emerald-400"></i>
                                    {{ $student->class_name ?? '—' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium bg-purple-50 text-purple-700 rounded-full border border-purple-200">
                                    <i class="fa-solid fa-graduation-cap text-purple-400"></i>
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
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    {{ $currentSpLevel->name }} — {{ $currentSpLevel->min_points }}+ poin
                </div>
            @endif

            {{-- Gradient Stat Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-4 shadow-sm">
                    <div class="absolute right-0 top-0 w-12 h-12 opacity-15">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">Total Poin</p>
                    <p class="text-xl font-bold text-white mt-1">{{ $totalPoints }}</p>
                </div>
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-orange-500 to-red-600 p-4 shadow-sm">
                    <div class="absolute right-0 top-0 w-12 h-12 opacity-15">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">Pelanggaran</p>
                    <p class="text-xl font-bold text-white mt-1">{{ $violationCount }}<span class="text-sm font-medium text-white/60">x</span></p>
                </div>
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-violet-500 to-violet-700 p-4 shadow-sm">
                    <div class="absolute right-0 top-0 w-12 h-12 opacity-15">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">SP Terbit</p>
                    <p class="text-xl font-bold text-white mt-1">{{ $activeSpLetters }}</p>
                </div>
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-teal-500 to-teal-700 p-4 shadow-sm">
                    <div class="absolute right-0 top-0 w-12 h-12 opacity-15">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">Terakhir</p>
                    <p class="text-sm font-bold text-white mt-1">
                        @if($lastViolation) {{ $lastViolation->violation_date->diffForHumans() }} @else — @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SP PROGRESS ===== --}}
    @if($spThresholds->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fa-solid fa-chart-line text-blue-500 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Progress Ambang SP</h3>
                <p class="text-xs text-gray-400">{{ $totalPoints }} / {{ $nextSpThreshold ? $nextSpThreshold->min_points : $spThresholds->last()->min_points }} poin</p>
            </div>
        </div>

        @php
            $maxPoints = $nextSpThreshold ? $nextSpThreshold->min_points : $spThresholds->last()->min_points;
            $progressPercent = $totalPoints >= $spThresholds->last()->min_points ? 100 : min(100, ($totalPoints / $maxPoints) * 100);
        @endphp
        <div class="relative h-3 bg-gray-100 rounded-full overflow-hidden mb-4">
            <div class="sp-progress-bar h-full rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
        </div>

        <div class="relative flex justify-between">
            @foreach($spThresholds as $threshold)
                @php $tsReached = $totalPoints >= $threshold->min_points; @endphp
                <div class="flex flex-col items-center" style="flex:1">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="w-3 h-3 rounded-full {{ $tsReached ? 'shadow-sm' : 'bg-gray-300' }}" style="background-color: {{ $tsReached ? $threshold->color : '' }}"></span>
                        <span class="text-xs font-semibold {{ $tsReached ? '' : 'text-gray-400' }}" style="color: {{ $tsReached ? $threshold->color : '' }}">{{ $threshold->min_points }}</span>
                    </div>
                    <span class="text-[10px] {{ $tsReached ? 'font-medium' : 'text-gray-400' }}" style="color: {{ $tsReached ? $threshold->color : '' }}">{{ $threshold->name }}</span>
                </div>
            @endforeach
        </div>

        @if($currentSpLevel)
            <div class="mt-5 p-4 rounded-xl flex items-start gap-3" style="background-color: {{ $currentSpLevel->color }}10; border: 1px solid {{ $currentSpLevel->color }}20;">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: {{ $currentSpLevel->color }}20;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: {{ $currentSpLevel->color }}"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold" style="color: {{ $currentSpLevel->color }}">{{ $currentSpLevel->name }} Sudah Tercapai</p>
                    <p class="text-xs text-gray-500 mt-0.5">Siswa telah mencapai ambang {{ $currentSpLevel->min_points }} poin. Surat peringatan telah diterbitkan.</p>
                </div>
            </div>
        @elseif($nextSpThreshold && $violationCount > 0)
            <div class="mt-5 p-4 rounded-xl bg-blue-50 border border-blue-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-circle-info text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-blue-700">Menuju {{ $nextSpThreshold->name }}</p>
                    <p class="text-xs text-blue-500/70 mt-0.5">{{ $nextSpThreshold->min_points - $totalPoints }} poin lagi untuk mencapai {{ $nextSpThreshold->name }}</p>
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- ===== VIOLATIONS TIMELINE ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Riwayat Pelanggaran</h3>
                    <p class="text-xs text-gray-400">{{ $violationCount }} catatan pelanggaran</p>
                </div>
            </div>
        </div>

        @forelse($student->violations as $v)
            <div class="violation-timeline relative pl-[52px] pr-6 py-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }} hover:bg-gray-50/50 transition">
                <div class="absolute left-[15px] top-[15px] w-[16px] h-[16px] rounded-full border-[3px] bg-white flex items-center justify-center"
                    style="border-color: {{ $v->violationType?->category?->color ?? '#9ca3af' }}">
                    <span class="w-[6px] h-[6px] rounded-full" style="background-color: {{ $v->violationType?->category?->color ?? '#9ca3af' }}"></span>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-gray-900">{{ $v->violationType->name ?? '—' }}</span>
                            @if($v->is_verified)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-medium bg-emerald-50 text-emerald-600 rounded-full border border-emerald-200">
                                    <span class="w-1 h-1 bg-emerald-500 rounded-full"></span>
                                    Terverifikasi
                                </span>
                            @endif
                        </div>
                        @if($v->description)
                            <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $v->description }}</p>
                        @endif
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-solid fa-calendar text-gray-300"></i>
                                {{ $v->violation_date->format('d M Y') }}
                                @if($v->violation_time) {{ \Carbon\Carbon::parse($v->violation_time)->format('H:i') }} @endif
                            </span>
                            @if($v->location)
                                <span class="inline-flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-gray-300"></i>
                                    {{ $v->location }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-solid fa-user-pen text-gray-300"></i>
                                {{ $v->recorder->name ?? '—' }}
                            </span>
                        </div>

                        @if($v->evidences && $v->evidences->count() > 0)
                            <div class="flex gap-2 mt-2.5">
                                @foreach($v->evidences->take(3) as $ev)
                                    <a href="{{ Storage::url($ev->file_path) }}" target="_blank"
                                        class="w-12 h-12 rounded-lg border border-gray-200 overflow-hidden hover:ring-2 hover:ring-blue-300 transition-all">
                                        <img src="{{ Storage::url($ev->file_path) }}" class="w-full h-full object-cover" alt="Bukti">
                                    </a>
                                @endforeach
                                @if($v->evidences->count() > 3)
                                    <div class="w-12 h-12 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-[10px] text-gray-400 font-medium">
                                        +{{ $v->evidences->count() - 3 }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-center flex-shrink-0 gap-2.5 min-w-[72px]">
                        <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-lg shadow-sm
                            {{ $v->points >= 50 ? 'bg-red-500 text-white' : ($v->points >= 15 ? 'bg-yellow-500 text-white' : 'bg-blue-500 text-white') }}">
                            +{{ $v->points }}
                        </span>
                        @if($v->sanction)
                            <span class="text-[10px] text-gray-400 leading-tight text-center line-clamp-2 max-w-[72px]">{{ $v->sanction }}</span>
                        @endif
                        <a href="{{ route('violations.show', $v->id) }}"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 hover:border-blue-300 transition-all shadow-sm w-full">
                            Detail
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-face-smile text-gray-300 text-2xl"></i>
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
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
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
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
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
            <div class="relative bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-pen-to-square text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Update Data Siswa</h3>
                            <p class="text-xs text-gray-400">Perbarui data diri {{ $student->full_name }}</p>
                        </div>
                    </div>
                    <button @click="showEditModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form action="{{ route('students.update', $student->id) }}" method="POST" class="p-6">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="full_name" value="{{ $student->full_name }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                            <input type="text" name="nisn" value="{{ $student->nisn }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NIS</label>
                            <input type="text" name="student_number" value="{{ $student->student_number }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                            <select name="gender"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">—</option>
                                <option value="L" @selected($student->gender === 'L')>Laki-laki</option>
                                <option value="P" @selected($student->gender === 'P')>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                            <input type="text" name="place_of_birth" value="{{ $student->place_of_birth }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date" name="date_of_birth" value="{{ $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '' }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea name="address" rows="2"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">{{ $student->address }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                            <input type="text" name="phone_number" value="{{ $student->phone_number }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ $student->email }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                            <input type="text" name="class_name" value="{{ $student->class_name }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kelas</label>
                            <input type="text" name="class_level" value="{{ $student->class_level }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Jurusan</label>
                            <input type="text" name="department_code" value="{{ $student->department_code }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jurusan</label>
                            <input type="text" name="department_name" value="{{ $student->department_name }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                        <button type="button" @click="showEditModal = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
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
