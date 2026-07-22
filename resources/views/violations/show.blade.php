@extends('layouts.app')

@section('title', 'Detail Pelanggaran')

@push('styles')
<style>
    .evidence-grid img {
        transition: transform 0.3s ease;
    }
    .evidence-grid a:hover img {
        transform: scale(1.05);
    }
    .evidence-grid a:hover .evidence-overlay {
        opacity: 1;
    }
    .detail-row:last-of-type {
        border-bottom: none !important;
    }
    .status-dot {
        position: absolute;
        left: 0;
        top: 4px;
    }
</style>
@endpush

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-400 mb-1">
                <a href="{{ route('violations.index') }}" class="hover:text-gray-600 transition">Data Pelanggaran</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-medium truncate max-w-[200px]">Detail</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Detail Pelanggaran</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if(!$violation->is_verified)
                <form action="{{ route('violations.verify', $violation->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-xl hover:bg-green-700 transition shadow-sm">
                        <i class="fa-solid fa-check-circle text-xs"></i>
                        <span>Verifikasi</span>
                    </button>
                </form>
            @endif
            <form action="{{ route('violations.destroy', $violation->id) }}" method="POST" x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus pelanggaran ini?'})) $el.submit()">
                @csrf @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-xl hover:bg-red-50 transition shadow-sm">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span class="hidden sm:inline">Hapus</span>
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- ===== LEFT COLUMN ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Student Profile Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="relative">
                    {{-- Gradient strip di bagian atas card --}}
                    <div class="bg-gradient-to-r from-blue-500 via-blue-400 to-sky-300 h-16 sm:h-20"></div>
                    {{-- Nama & badge --}}
                    <div class="px-6 sm:px-8 pt-[22px] sm:pt-[26px]">
                        <div class="inline-flex items-center px-4 py-2 sm:px-5 sm:py-2.5 bg-white shadow-sm border border-gray-200 rounded-xl max-w-full">
                            <h2 class="text-base sm:text-lg font-bold text-gray-900 truncate">{{ $violation->student->full_name ?? '-' }}</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-2.5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-mono font-medium bg-gray-100 text-gray-600 rounded-lg border border-gray-200">{{ $violation->student->nisn ?? '—' }}</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium bg-emerald-50 text-emerald-600 rounded-lg border border-emerald-200">{{ $violation->student->class_name ?? '—' }}</span>
                            @if($violation->student->department_name)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium bg-purple-50 text-purple-600 rounded-lg border border-purple-200">{{ $violation->student->department_name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- Stat row — dengan jarak dari avatar --}}
                <div class="px-6 sm:px-8 pt-6 sm:pt-8 pb-5 sm:pb-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    {{-- Total Poin --}}
                    <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-orange-500 to-red-600 p-4 shadow-sm">
                        <div class="absolute right-0 top-0 w-16 h-16 opacity-20">
                            <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">Total Poin</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $violation->student->total_points }}</p>
                        <p class="text-[10px] text-white/50 mt-0.5">akumulasi</p>
                    </div>
                    {{-- Poin Pelanggaran --}}
                    <div class="relative overflow-hidden rounded-xl p-4 shadow-sm"
                        style="background: linear-gradient(135deg, {{ $violation->points >= 50 ? '#dc2626' : ($violation->points >= 15 ? '#d97706' : '#2563eb') }}, {{ $violation->points >= 50 ? '#b91c1c' : ($violation->points >= 15 ? '#b45309' : '#1d4ed8') }});">
                        <div class="absolute right-0 top-0 w-16 h-16 opacity-10">
                            <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">Poin Pelanggaran</p>
                        <p class="text-2xl font-bold text-white mt-1">+{{ $violation->points }}</p>
                        <p class="text-[10px] text-white/50 mt-0.5">pelanggaran ini</p>
                    </div>
                    {{-- Kategori --}}
                    <div class="relative overflow-hidden rounded-xl p-4 shadow-sm"
                        style="background: linear-gradient(135deg, {{ $violation->violationType?->category?->color ?? '#6b7280' }}, {{ $violation->violationType?->category?->color ?? '#6b7280' }}dd);">
                        <div class="absolute right-0 top-0 w-16 h-16 opacity-10">
                            <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        </div>
                        <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">Kategori</p>
                        <p class="text-base font-bold text-white mt-1 truncate">{{ $violation->violationType?->category?->name ?? '-' }}</p>
                        <p class="text-[10px] text-white/50 mt-0.5">tingkat pelanggaran</p>
                    </div>
                    {{-- Status Penanganan --}}
                    @php
                        $handleColors = [
                            'unhandled' => ['from' => '#dc2626', 'to' => '#b91c1c'],
                            'in_progress' => ['from' => '#d97706', 'to' => '#b45309'],
                            'resolved' => ['from' => '#16a34a', 'to' => '#15803d'],
                        ];
                        $handleLabels = [
                            'unhandled' => 'Belum Ditangani',
                            'in_progress' => 'Dalam Proses',
                            'resolved' => 'Selesai',
                        ];
                        $hcolor = $handleColors[$violation->handling_status] ?? ['from' => '#6b7280', 'to' => '#4b5563'];
                    @endphp
                    <div class="relative overflow-hidden rounded-xl p-4 shadow-sm"
                        style="background: linear-gradient(135deg, {{ $hcolor['from'] }}, {{ $hcolor['to'] }});">
                        <div class="absolute right-0 top-0 w-16 h-16 opacity-10">
                            <i class="fa-solid fa-hand-holding-heart text-white text-4xl"></i>
                        </div>
                        <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">Penanganan</p>
                        <p class="text-base font-bold text-white mt-1">{{ $handleLabels[$violation->handling_status] ?? '-' }}</p>
                        <p class="text-[10px] text-white/50 mt-0.5">
                            {{ $violation->handlings->count() }} catatan
                            @if($violation->handled_at)
                                • {{ $violation->handled_at->format('d/m') }}
                            @endif
                        </p>
                    </div>
                </div>
                </div>
            </div>

            {{-- Detail Info Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-gavel text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Informasi Pelanggaran</h3>
                            <p class="text-xs text-gray-400">Detail lengkap pelanggaran yang tercatat</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full"
                        style="background-color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}15; color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}"></span>
                        {{ $violation->violationType?->category?->name ?? '-' }}
                    </span>
                </div>

                {{-- Content Grid --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Jenis Pelanggaran --}}
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-gavel text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Jenis Pelanggaran</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">{{ $violation->violationType->name ?? '-' }}</p>
                        </div>

                        {{-- Poin --}}
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center
                                    {{ $violation->points >= 50 ? 'bg-red-50' : ($violation->points >= 15 ? 'bg-yellow-50' : 'bg-blue-50') }}">
                                    <i class="fa-solid fa-star {{ $violation->points >= 50 ? 'text-red-500' : ($violation->points >= 15 ? 'text-yellow-500' : 'text-blue-500') }} text-xs"></i>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Poin Pelanggaran</span>
                            </div>
                            <p class="text-sm font-bold {{ $violation->points >= 50 ? 'text-red-600' : ($violation->points >= 15 ? 'text-yellow-600' : 'text-blue-600') }}">
                                +{{ $violation->points }}
                            </p>
                        </div>

                        {{-- Tanggal --}}
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-calendar text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Tanggal</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $violation->violation_date->format('d M Y') }}
                                @if($violation->violation_time)
                                    <span class="text-gray-400">• {{ \Carbon\Carbon::parse($violation->violation_time)->format('H:i') }}</span>
                                @endif
                            </p>
                        </div>

                        {{-- Sanksi --}}
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-scale-balanced text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Sanksi</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900">{{ $violation->sanction ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Row 2 --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        {{-- Lokasi --}}
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-location-dot text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Lokasi</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900">{{ $violation->location ?? '—' }}</p>
                        </div>

                        {{-- Pencatat --}}
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-user-pen text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Dicatat Oleh</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-[10px] font-bold text-gray-500">{{ strtoupper(substr($violation->recorder->name ?? '?', 0, 1)) }}</span>
                                </div>
                                <p class="text-sm font-medium text-gray-900">{{ $violation->recorder->name ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Deskripsi --}}
            @if($violation->description)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 sm:px-6 py-3.5 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-note-sticky text-blue-500 text-xs sm:text-sm"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Catatan / Deskripsi</h3>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 sm:py-5">
                    <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5">
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $violation->description }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ===== RIGHT COLUMN ===== --}}
        <div class="space-y-5">

            {{-- Status Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-circle-info text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Status</h3>
                            <p class="text-xs text-gray-400">Riwayat siklus pelanggaran</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    {{-- Verification Badge --}}
                    <div class="mb-5 p-4 rounded-xl {{ $violation->is_verified ? 'bg-gradient-to-br from-green-50 to-emerald-50/50 border border-green-200' : 'bg-gradient-to-br from-yellow-50 to-amber-50/50 border border-yellow-200' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $violation->is_verified ? 'bg-green-100' : 'bg-yellow-100' }} flex items-center justify-center flex-shrink-0">
                                @if($violation->is_verified)
                                    <i class="fa-solid fa-check-circle text-green-600 text-lg"></i>
                                @else
                                    <i class="fa-solid fa-clock text-yellow-600 text-lg"></i>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-bold {{ $violation->is_verified ? 'text-green-800' : 'text-yellow-800' }}">
                                    {{ $violation->is_verified ? 'Terverifikasi' : 'Menunggu Verifikasi' }}
                                </p>
                                @if($violation->is_verified)
                                    <p class="text-xs text-green-600 mt-0.5">
                                        oleh <span class="font-semibold">{{ $violation->verifier->name ?? '-' }}</span>
                                        @if($violation->verified_at)
                                            <span class="text-green-400">• {{ $violation->verified_at->format('d M Y H:i') }}</span>
                                        @endif
                                    </p>
                                @else
                                    <p class="text-xs text-yellow-600 mt-0.5">Belum diverifikasi oleh petugas BK</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="relative">
                        {{-- Garis vertikal --}}
                        <div class="absolute left-[15px] top-3 bottom-3 w-0.5 bg-gray-200"></div>

                        <div class="space-y-5">
                            {{-- Item: Verifikasi --}}
                            <div class="relative pl-9">
                                <div class="absolute left-0 top-[3px] w-[30px] flex justify-center">
                                    <div class="w-[13px] h-[13px] rounded-full border-[3px] flex items-center justify-center"
                                        style="border-color: {{ $violation->is_verified ? '#22c55e' : '#eab308' }}; background: white;">
                                        <span class="w-[5px] h-[5px] rounded-full" style="background-color: {{ $violation->is_verified ? '#22c55e' : '#eab308' }}"></span>
                                    </div>
                                </div>
                                <p class="text-sm font-semibold {{ $violation->is_verified ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ $violation->is_verified ? 'Diverifikasi' : 'Menunggu Verifikasi' }}
                                </p>
                                @if($violation->is_verified)
                                    <p class="text-xs text-gray-400">{{ $violation->verified_at ? $violation->verified_at->format('d M Y H:i') : '' }}</p>
                                @endif
                            </div>

                            {{-- Item: Dibuat --}}
                            <div class="relative pl-9">
                                <div class="absolute left-0 top-[3px] w-[30px] flex justify-center">
                                    <div class="w-[13px] h-[13px] rounded-full border-[3px] border-blue-500 bg-white">
                                    </div>
                                </div>
                                <p class="text-sm font-semibold text-gray-800">Dibuat</p>
                                <p class="text-xs text-gray-400">{{ $violation->created_at->format('d M Y H:i') }}</p>
                            </div>

                            {{-- Item: Diubah --}}
                            @if($violation->updated_at != $violation->created_at)
                            <div class="relative pl-9">
                                <div class="absolute left-0 top-[3px] w-[30px] flex justify-center">
                                    <div class="w-[13px] h-[13px] rounded-full border-[3px] border-gray-300 bg-white">
                                    </div>
                                </div>
                                <p class="text-sm font-semibold text-gray-500">Terakhir Diubah</p>
                                <p class="text-xs text-gray-400">{{ $violation->updated_at->format('d M Y H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Evidence Photos --}}
            @if($violation->evidences->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 sm:px-6 py-3.5 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-camera text-blue-500 text-xs sm:text-sm"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 truncate">Bukti Foto</h3>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium bg-gray-100 text-gray-500 rounded-full whitespace-nowrap flex-shrink-0">
                        <i class="fa-solid fa-image mr-1"></i>
                        {{ $violation->evidences->count() }}
                    </span>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="evidence-grid grid grid-cols-2 gap-2.5">
                        @foreach($violation->evidences as $evidence)
                            <a href="{{ $evidence->url }}" target="_blank" rel="noopener noreferrer"
                                class="group relative block aspect-square rounded-xl overflow-hidden border border-gray-200 bg-gray-50 shadow-sm">
                                <img src="{{ $evidence->url }}"
                                    class="w-full h-full object-cover"
                                    alt="{{ $evidence->original_name }}">
                                <div class="evidence-overlay absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <span class="inline-flex items-center gap-1 text-white text-[11px] font-medium bg-black/50 px-3 py-1.5 rounded-lg backdrop-blur-sm">
                                        <i class="fa-solid fa-expand"></i>
                                        Perbesar
                                    </span>
                                </div>
                                @if($loop->first && $violation->evidences->count() > 1)
                                    <span class="absolute top-2 left-2 text-[10px] font-medium text-white bg-black/40 px-2 py-0.5 rounded-md backdrop-blur-sm">{{ $loop->iteration }} of {{ $violation->evidences->count() }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Link to Student Profile --}}
            <a href="{{ route('students.show', $violation->student_id) }}"
                class="flex items-center justify-between gap-3 p-4 bg-blue-50/60 border border-blue-100 rounded-2xl hover:bg-blue-50 transition group">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-user-graduate text-blue-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-blue-700">Lihat Profil Siswa</p>
                        <p class="text-[11px] text-blue-500 mt-0.5 truncate">{{ $violation->student->full_name ?? '' }}</p>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right text-blue-400 group-hover:text-blue-600 transition flex-shrink-0"></i>
            </a>

            {{-- Penanganan Section --}}
            <div x-data="{ showHandlingModal: false }" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 sm:px-6 py-3.5 border-b border-gray-100 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-hand-holding-heart text-white text-xs sm:text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 truncate">Penanganan</h3>
                            <p class="text-xs text-gray-400">{{ $violation->handlings->count() }} catatan</p>
                        </div>
                    </div>
                    <button @click="showHandlingModal = true"
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition flex-shrink-0 shadow-sm">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span class="hidden sm:inline">Tambah</span>
                    </button>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($violation->handlings as $h)
                        @php
                            $typeColors = [
                                'Teguran Lisan' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'fa-comment'],
                                'Teguran Tertulis' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'fa-file-pen'],
                                'Pembinaan BK' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-700', 'icon' => 'fa-hand-holding-heart'],
                                'Panggilan Orang Tua' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'icon' => 'fa-phone-volume'],
                                'Home Visit' => ['bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'icon' => 'fa-house-chimney'],
                                'Skorsing' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => 'fa-ban'],
                                'Tugas Sosial' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'fa-handshake-angle'],
                            ];
                            $tc = $typeColors[$h->handling_type] ?? ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'fa-clipboard-list'];
                        @endphp
                        <div class="relative pl-4 pr-4 sm:pl-5 sm:pr-5 py-4 hover:bg-gray-50/50 transition">
                            {{-- Left accent bar --}}
                            <div class="absolute left-0 top-0 bottom-0 w-[3px] {{ $tc['bg'] }}"></div>

                            <div class="space-y-3">
                                {{-- Header row --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-xl {{ $tc['bg'] }} flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <i class="fa-solid {{ $tc['icon'] }} {{ $tc['text'] }} text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-bold text-gray-900">{{ $h->handling_type }}</p>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold rounded-md {{ $tc['bg'] }} {{ $tc['text'] }}">
                                                    <i class="fa-regular fa-clock"></i>
                                                    {{ $h->handling_date->format('d/m') }}
                                                </span>
                                            </div>
                                            @if($h->location || $h->creator)
                                                <p class="text-[11px] text-gray-400 mt-0.5">
                                                    @if($h->location)<i class="fa-solid fa-location-dot mr-1"></i>{{ $h->location }}@endif
                                                    @if($h->location && $h->creator) <span class="text-gray-300">•</span> @endif
                                                    @if($h->creator)oleh {{ $h->creator->name }}@endif
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    <form action="{{ route('violations.handling.destroy', [$violation->id, $h->id]) }}" method="POST"
                                        x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus catatan penanganan ini?'})) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition flex-shrink-0">
                                            <i class="fa-solid fa-trash-can text-[11px]"></i>
                                        </button>
                                    </form>
                                </div>

                                {{-- Description --}}
                                @if($h->description)
                                    <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-3.5 ml-[42px]">
                                        <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $h->description }}</p>
                                    </div>
                                @endif

                                {{-- Participants --}}
                                @if($h->participants->count() > 0)
                                    <div class="ml-[42px]">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Yang Menangani:</span>
                                            @foreach($h->participants as $p)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] bg-white border border-gray-200 rounded-full shadow-sm">
                                                    <span class="w-4 h-4 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                        <span class="text-[7px] font-bold text-gray-500">{{ strtoupper(substr($p->user->name ?? '?', 0, 1)) }}</span>
                                                    </span>
                                                    <span class="font-medium text-gray-700">{{ $p->user->name ?? '-' }}</span>
                                                    @if($p->role)
                                                        <span class="text-[10px] text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">{{ $p->role }}</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Evidence + Metadata row --}}
                                <div class="flex items-center justify-between ml-[42px]">
                                    <div class="flex items-center gap-2">
                                        @if($h->evidence)
                                            <a href="{{ \Storage::url($h->evidence) }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-blue-600 bg-blue-50/80 px-3 py-1.5 rounded-lg border border-blue-100 hover:bg-blue-100 transition shadow-sm">
                                                <i class="fa-solid fa-paperclip text-[10px]"></i>
                                                Lihat Bukti
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-blue-400"></i>
                                            </a>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-gray-300">
                                        @if($h->created_at != $h->updated_at)
                                            Diubah {{ $h->updated_at->diffForHumans() }}
                                        @else
                                            {{ $h->created_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-hand-holding-heart text-gray-300 text-lg"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-500 mb-0.5">Belum ada penanganan</p>
                            <p class="text-xs text-gray-400">Tambahkan penanganan pertama untuk pelanggaran ini</p>
                        </div>
                    @endforelse
                </div>

                @if($violation->handlings->count() > 0 && $violation->isInProgress())
                    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                        <form action="{{ route('violations.resolve', $violation->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-green-700 bg-green-50 border border-green-200 rounded-xl hover:bg-green-100 transition">
                                <i class="fa-solid fa-check-circle text-xs"></i>
                                Tandai Selesai Ditangani
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Add Handling Modal --}}
                <div x-show="showHandlingModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4 py-4">
                        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                        <div class="relative bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-sm">
                                        <i class="fa-solid fa-hand-holding-heart text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">Tambah Penanganan</h3>
                                        <p class="text-xs text-gray-400">{{ $violation->student->full_name ?? '-' }}</p>
                                    </div>
                                </div>
                                <button @click="showHandlingModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <form action="{{ route('violations.handling.store', $violation->id) }}" method="POST" enctype="multipart/form-data" class="p-6"
                                x-data="{ participants: [] }">
                                @csrf

                                <div class="space-y-4">
                                    {{-- Jenis Penanganan --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Penanganan <span class="text-red-500">*</span></label>
                                        <select name="handling_type" required
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                            <option value="">Pilih jenis</option>
                                            <option value="Teguran Lisan">Teguran Lisan</option>
                                            <option value="Teguran Tertulis">Teguran Tertulis</option>
                                            <option value="Pembinaan BK">Pembinaan BK</option>
                                            <option value="Panggilan Orang Tua">Panggilan Orang Tua</option>
                                            <option value="Home Visit">Home Visit</option>
                                            <option value="Skorsing">Skorsing</option>
                                            <option value="Tugas Sosial">Tugas Sosial</option>
                                            <option value="SP-1">SP-1</option>
                                            <option value="SP-2">SP-2</option>
                                            <option value="SP-3">SP-3</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>

                                    {{-- Tanggal --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Penanganan <span class="text-red-500">*</span></label>
                                        <input type="date" name="handling_date" value="{{ date('Y-m-d') }}" required
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    </div>

                                    {{-- Lokasi --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                        <input type="text" name="location" placeholder="Ruang BK, Ruang Guru, dll"
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    </div>

                                    {{-- Deskripsi --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / Deskripsi</label>
                                        <textarea name="description" rows="3" placeholder="Catatan penanganan..."
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"></textarea>
                                    </div>

                                    {{-- Bukti --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Bukti (opsional)</label>
                                        <input type="file" name="evidence" accept="image/*,.pdf,.doc,.docx"
                                            class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                                    </div>

                                    {{-- Participants --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-sm font-medium text-gray-700">Yang Menangani</label>
                                            <button type="button" @click="participants.push({user_id: '', role: ''})"
                                                class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition inline-flex items-center gap-1">
                                                <i class="fa-solid fa-plus"></i> Tambah Penanggungjawab
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <template x-for="(p, i) in participants" :key="i">
                                                <div class="flex items-center gap-2">
                                                    <select :name="'participants['+i+'][user_id]'" x-model="p.user_id"
                                                        class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                                        <option value="">Pilih petugas...</option>
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                                        @endforeach
                                                    </select>
                                                    <select :name="'participants['+i+'][role]'" x-model="p.role"
                                                        class="w-[140px] px-3 py-2 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                                        <option value="">Peran</option>
                                                        <option value="Wakil Ketua Tim">Wakil Ketua Tim</option>
                                                        <option value="Anggota Tim">Anggota Tim</option>
                                                        <option value="Wali Kelas">Wali Kelas</option>
                                                        <option value="Guru BK">Guru BK</option>
                                                        <option value="Saksi">Saksi</option>
                                                    </select>
                                                    <button type="button" @click="participants.splice(i, 1)"
                                                        class="p-2 text-gray-300 hover:text-red-500 transition flex-shrink-0">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                                    <button type="button" @click="showHandlingModal = false"
                                        class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm inline-flex items-center gap-2">
                                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Back to List --}}
            <a href="{{ route('violations.index') }}"
                class="flex items-center justify-center gap-2 p-3 text-sm text-gray-500 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 hover:text-gray-700 transition group">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Kembali ke Data Pelanggaran
            </a>
        </div>
    </div>
</div>
@endsection
