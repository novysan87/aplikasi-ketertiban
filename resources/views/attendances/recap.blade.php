@extends('layouts.app')

@section('title', 'Rekap Presensi')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-400 mb-1">
                <a href="{{ route('attendances.index') }}" class="hover:text-gray-600 transition">Presensi</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-medium">Rekap Bulanan</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Rekap Presensi Bulanan</h1>
            <p class="text-sm text-gray-500 mt-1">Rekapitulasi kehadiran siswa per jam pelajaran</p>
        </div>
        <a href="{{ route('attendances.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
            <i class="fa-solid fa-plus text-xs"></i>
            Input Presensi
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6">
        <form method="GET">
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Kelas</label>
                        <select name="class_name"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Semua Kelas</option>
                            @foreach($classNames as $cn)
                                <option value="{{ $cn }}" @selected($className == $cn)>{{ $cn }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Bulan</label>
                        <select name="month"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($month == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tahun</label>
                        <div class="flex gap-2">
                            <select name="year"
                                class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                @foreach(range(now()->year, now()->year - 2, -1) as $y)
                                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                                <i class="fa-solid fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-chart-simple text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">
                        Rekap {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
                    </h3>
                    <p class="text-xs text-gray-400">{{ count($recap) }} siswa</p>
                </div>
            </div>
        </div>

        @if(count($recap) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Siswa</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-emerald-600 uppercase tracking-wider">✅ Hadir</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-purple-600 uppercase tracking-wider">🟣 Sakit</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-blue-600 uppercase tracking-wider">🔵 Izin</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-red-600 uppercase tracking-wider">❌ Alpha</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-yellow-600 uppercase tracking-wider">🟡 Terlambat</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recap as $r)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <p class="text-sm font-semibold text-gray-900">{{ $r->student->full_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $r->student->class_name }}</p>
                                </td>
                                <td class="px-3 py-3 text-center text-sm font-bold text-emerald-600">{{ $r->hadir }}</td>
                                <td class="px-3 py-3 text-center text-sm font-bold text-purple-600">{{ $r->sakit }}</td>
                                <td class="px-3 py-3 text-center text-sm font-bold text-blue-600">{{ $r->izin }}</td>
                                <td class="px-3 py-3 text-center text-sm font-bold {{ $r->alpha > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $r->alpha }}</td>
                                <td class="px-3 py-3 text-center text-sm font-bold {{ $r->terlambat > 0 ? 'text-yellow-600' : 'text-gray-400' }}">{{ $r->terlambat }}</td>
                                <td class="px-3 py-3 text-center text-sm font-bold text-gray-700">{{ $r->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 text-xs text-gray-400 text-center">
                Menampilkan rekap {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
                @if($className) • Kelas {{ $className }} @endif
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-chart-simple text-gray-300 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-500 mb-0.5">Belum Ada Data Presensi</p>
                <p class="text-xs text-gray-400">Belum ada catatan presensi untuk bulan ini</p>
            </div>
        @endif
    </div>
</div>
@endsection
