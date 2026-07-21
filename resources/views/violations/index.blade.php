@extends('layouts.app')

@section('title', 'Data Pelanggaran')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Data Pelanggaran</h1>
        <p class="text-sm text-gray-500">Riwayat pelanggaran siswa</p>
    </div>
    <a href="{{ route('violations.create') }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        <span>Input Pelanggaran</span>
    </a>
</div>

{{-- Filter --}}
<form method="GET" class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <input type="text" name="search" placeholder="Cari NISN/Nama..." value="{{ request('search') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Dari tanggal"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex space-x-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Sampai tanggal"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Filter</button>
            @if(request()->anyFilled(['search','category_id','date_from','date_to']))
                <a href="{{ route('violations.index') }}" class="px-3 py-2 border border-gray-300 text-gray-600 text-sm rounded-lg hover:bg-gray-50">Reset</a>
            @endif
        </div>
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggaran</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Poin</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Verifikasi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Oleh</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($violations as $v)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $v->violation_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $v->student->full_name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $v->student->nisn ?? '' }} - {{ $v->student->class_name ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $v->violationType?->category?->color ?? '#6b7280' }}"></span>
                                <span class="text-sm text-gray-700">{{ $v->violationType->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                +{{ $v->points }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($v->is_verified)
                                <span class="text-xs text-green-600 font-medium">✓ Terverifikasi</span>
                            @else
                                <span class="text-xs text-yellow-600 font-medium">Belum</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $v->recorder->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('violations.show', $v->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                            <p>Belum ada pelanggaran tercatat</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($violations->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $violations->links() }}
        </div>
    @endif
</div>
@endsection
