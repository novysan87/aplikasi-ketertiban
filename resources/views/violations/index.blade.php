@extends('layouts.app')

@section('title', 'Data Pelanggaran')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Data Pelanggaran</h1>
            <p class="text-sm text-gray-500 mt-1">Riwayat pelanggaran siswa</p>
        </div>
        <a href="{{ route('violations.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
            <i class="fa-solid fa-plus text-xs"></i>
            Input Pelanggaran
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6">
        <form method="GET">
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    {{-- Search --}}
                    <div class="sm:col-span-2 lg:col-span-1 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <input type="text" name="search" placeholder="Cari NISN/Nama..." value="{{ request('search') }}"
                            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                    {{-- Kategori --}}
                    <div>
                        <select name="handling_status"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Semua Status</option>
                            <option value="unhandled" @selected(request('handling_status') === 'unhandled')>Belum Ditangani</option>
                            <option value="in_progress" @selected(request('handling_status') === 'in_progress')>Dalam Proses</option>
                            <option value="resolved" @selected(request('handling_status') === 'resolved')>Selesai</option>
                        </select>
                    </div>
                    {{-- Kategori --}}
                    <div>
                        <select name="category_id"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Tanggal Dari --}}
                    <div>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Dari tanggal"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                    {{-- Tanggal Sampai --}}
                    <div>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Sampai tanggal"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                    {{-- Actions --}}
                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                            <i class="fa-solid fa-filter mr-1.5"></i>Filter
                        </button>
                        @if(request()->anyFilled(['search','handling_status','category_id','date_from','date_to']))
                            <a href="{{ route('violations.index') }}"
                                class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Siswa</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pelanggaran</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Poin</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Verifikasi</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Penanganan</th>
                        <th class="px-5 py-3.5 text-left hidden lg:table-cell text-xs font-semibold text-gray-400 uppercase tracking-wider">Oleh</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($violations as $v)
                        <tr class="hover:bg-gray-50/50 transition">
                            {{-- Tanggal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-calendar text-blue-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $v->violation_date->format('d/m/Y') }}</p>
                                        @if($v->violation_time)
                                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($v->violation_time)->format('H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            {{-- Siswa --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-gray-500">{{ strtoupper(substr($v->student->full_name ?? '?', 0, 1)) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate max-w-[180px]">{{ $v->student->full_name ?? '-' }}</p>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-mono font-medium bg-gray-100 text-gray-600 rounded-full border border-gray-200">{{ $v->student->nisn ?? '—' }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium bg-blue-50 text-blue-600 rounded-full border border-blue-200">{{ $v->student->class_name ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            {{-- Pelanggaran --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $v->violationType?->category?->color ?? '#6b7280' }}"></span>
                                    <span class="text-sm text-gray-700 truncate max-w-[180px]">{{ $v->violationType->name ?? '-' }}</span>
                                </div>
                            </td>
                            {{-- Poin --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg
                                    {{ $v->points >= 50 ? 'bg-red-50 text-red-700' : ($v->points >= 15 ? 'bg-yellow-50 text-yellow-700' : 'bg-blue-50 text-blue-700') }}">
                                    +{{ $v->points }}
                                </span>
                            </td>
                            {{-- Verifikasi --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($v->is_verified)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-green-50 text-green-700 border border-green-200 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                        Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span>
                                        Belum
                                    </span>
                                @endif
                            </td>
                            {{-- Penanganan --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap hidden lg:table-cell">
                                @php
                                    $statusStyles = [
                                        'unhandled' => 'bg-red-50 text-red-700 border-red-200',
                                        'in_progress' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'resolved' => 'bg-green-50 text-green-700 border-green-200',
                                    ];
                                    $statusLabels = [
                                        'unhandled' => 'Belum Ditangani',
                                        'in_progress' => 'Dalam Proses',
                                        'resolved' => 'Selesai',
                                    ];
                                    $statusDots = [
                                        'unhandled' => 'bg-red-500',
                                        'in_progress' => 'bg-yellow-500',
                                        'resolved' => 'bg-green-500',
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium border rounded-full {{ $statusStyles[$v->handling_status] ?? 'bg-gray-50 text-gray-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$v->handling_status] ?? 'bg-gray-500' }}"></span>
                                    {{ $statusLabels[$v->handling_status] ?? ucfirst($v->handling_status) }}
                                </span>
                            </td>
                            {{-- Oleh --}}
                            <td class="px-5 py-4 hidden lg:table-cell">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-[9px] font-bold text-gray-500">{{ strtoupper(substr($v->recorder->name ?? '?', 0, 1)) }}</span>
                                    </div>
                                    <span class="text-sm text-gray-500 truncate max-w-[120px]">{{ $v->recorder->name ?? '-' }}</span>
                                </div>
                            </td>
                            {{-- Aksi --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('violations.show', $v->id) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                                    <i class="fa-solid fa-eye"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-triangle-exclamation text-gray-300 text-xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Belum ada pelanggaran</p>
                                    <p class="text-xs text-gray-400">Data pelanggaran akan muncul di sini setelah dicatat</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($violations->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $violations->appends(request()->query())->links() }}
            </div>
        @endif

        {{-- Summary --}}
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-400">
            <span>Menampilkan {{ $violations->firstItem() ?? 0 }}–{{ $violations->lastItem() ?? 0 }} dari {{ $violations->total() }} pelanggaran</span>
            <span class="font-medium">{{ $violations->total() }} total</span>
        </div>
    </div>
</div>
@endsection
