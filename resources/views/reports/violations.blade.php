@extends('layouts.app')

@section('title', 'Laporan Pelanggaran')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Laporan Rekap Pelanggaran</h1>
        <p class="text-sm text-gray-500 mt-1">Cetak rekap pelanggaran per periode untuk BK &amp; Kepala Sekolah</p>
    </div>

    {{-- Form Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 max-w-3xl">
        <form method="GET" action="{{ route('reports.violations.pdf') }}" target="_blank" class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Filter Kelas (opsional)</label>
                    <select name="class_id"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        <option value="">— Semua Kelas —</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                    <i class="fa-solid fa-file-pdf text-xs"></i>
                    Cetak Laporan PDF
                </button>
                <a href="{{ route('violations.export', request()->only(['date_from', 'date_to'])) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition shadow-sm">
                    <i class="fa-solid fa-file-excel text-xs"></i>
                    Export Data Excel
                </a>
            </div>
        </form>
    </div>

    {{-- Preview ringkas --}}
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-3xl">
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-2xl p-5 shadow-sm">
            <div class="text-3xl font-bold">{{ $totalKasus }}</div>
            <div class="text-sm text-blue-100 mt-1">Total Kasus Pelanggaran</div>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl p-5 shadow-sm">
            <div class="text-3xl font-bold">{{ $totalPoin }}</div>
            <div class="text-sm text-amber-100 mt-1">Total Poin</div>
        </div>
        <div class="bg-gradient-to-br from-rose-500 to-rose-700 text-white rounded-2xl p-5 shadow-sm">
            <div class="text-3xl font-bold">{{ $siswaTerlibat }}</div>
            <div class="text-sm text-rose-100 mt-1">Siswa Terlibat</div>
        </div>
    </div>
</div>
@endsection
