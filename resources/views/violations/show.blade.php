@extends('layouts.app')

@section('title', 'Detail Pelanggaran')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('violations.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Pelanggaran</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Informasi lengkap pelanggaran siswa</p>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            @if(!$violation->is_verified)
                <form action="{{ route('violations.verify', $violation->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition shadow-sm">
                        <i class="fa-solid fa-check-circle mr-1.5"></i>
                        Verifikasi
                    </button>
                </form>
            @endif
            <form action="{{ route('violations.destroy', $violation->id) }}" method="POST" x-data onsubmit="event.preventDefault(); Swal.fire({title: 'Konfirmasi', text: 'Hapus pelanggaran ini?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280', confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) { .target.submit(); } })">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-200 rounded-xl hover:bg-blue-50 transition">
                    <i class="fa-solid fa-trash-can mr-1.5"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Info Siswa + Pelanggaran --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Card Siswa --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-5 flex items-center space-x-4">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0"
                        style="background-color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}20; color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}">
                        {{ strtoupper(substr($violation->student->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 truncate">{{ $violation->student->full_name ?? '-' }}</h2>
                        <p class="text-sm text-gray-500">{{ $violation->student->nisn ?? '' }} <span class="mx-1.5">•</span> {{ $violation->student->class_name ?? '' }}</p>
                        <p class="text-xs text-gray-400">{{ $violation->student->department_name ?? '' }}</p>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <p class="text-xs text-gray-500">Total Poin</p>
                        <p class="text-2xl font-bold" style="color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}">{{ $violation->student->total_points }}</p>
                    </div>
                </div>
            </div>

            {{-- Card Detail Pelanggaran --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Detail Pelanggaran</h3>
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full"
                        style="background-color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}15; color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}">
                        <span class="w-1.5 h-1.5 rounded-full mr-1.5" style="background-color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}"></span>
                        {{ $violation->violationType?->category?->name ?? '-' }}
                    </span>
                </div>
                <div class="p-5">
                    {{-- Info bars --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-gavel text-blue-500 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Jenis Pelanggaran</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $violation->violationType->name ?? '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-star text-blue-500 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Poin</span>
                            </div>
                            <span class="text-lg font-bold text-blue-600">+{{ $violation->points }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-scale-balanced text-blue-500 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Sanksi</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 text-right max-w-[50%]">{{ $violation->sanction ?? '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-calendar text-blue-500 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Tanggal</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $violation->violation_date->format('d F Y') }} @if($violation->violation_time){{ \Carbon\Carbon::parse($violation->violation_time)->format('H:i') }} WIB @endif</span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-location-dot text-blue-500 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Lokasi</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $violation->location ?? '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-user-pen text-blue-500 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Dicatat oleh</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $violation->recorder->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Deskripsi --}}
            @if($violation->description)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Deskripsi / Catatan</h3>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $violation->description }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Sidebar: Status + Evidences --}}
        <div class="space-y-6">
            {{-- Status Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Status</h3>
                </div>
                <div class="p-5 space-y-4">
                    {{-- Verifikasi --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Verifikasi</span>
                        @if($violation->is_verified)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                <i class="fa-solid fa-check-circle mr-1"></i>
                                Terverifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-yellow-100 text-yellow-700 rounded-full">
                                <i class="fa-solid fa-clock mr-1"></i>
                                Belum
                            </span>
                        @endif
                    </div>

                    @if($violation->is_verified)
                    <div class="text-xs text-gray-400 flex items-center space-x-1">
                        <i class="fa-solid fa-check text-green-500"></i>
                        <span>oleh {{ $violation->verifier->name ?? '-' }} • {{ $violation->verified_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif

                    {{-- Waktu input --}}
                    <div class="pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400">Dibuat pada</p>
                        <p class="text-sm text-gray-700">{{ $violation->created_at->format('d F Y H:i') }}</p>
                    </div>
                    @if($violation->updated_at != $violation->created_at)
                    <div>
                        <p class="text-xs text-gray-400">Terakhir diubah</p>
                        <p class="text-sm text-gray-700">{{ $violation->updated_at->format('d F Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Evidence Photos --}}
            @if($violation->evidences->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Bukti Foto</h3>
                    <span class="text-xs text-gray-400">{{ $violation->evidences->count() }} file</span>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($violation->evidences as $evidence)
                            <a href="{{ $evidence->url }}" target="_blank"
                                class="group relative block aspect-square rounded-xl overflow-hidden border border-gray-200 bg-gray-50 transition-all">
                                <img src="{{ $evidence->url }}" class="w-full h-full object-cover scale-100 transition-transform duration-300" alt="{{ $evidence->original_name }}">
                                <div class="absolute inset-0 bg-black/0 bg-black/0 transition-colors"></div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
