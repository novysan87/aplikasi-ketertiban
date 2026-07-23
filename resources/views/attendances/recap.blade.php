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
                <span class="text-gray-700 font-medium">Rekap</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Rekap Presensi</h1>
            <p class="text-sm text-gray-500 mt-1">
                @if($type == 'daily') Rekap per jam pelajaran
                @elseif($type == 'weekly') Rekap per hari (Senin - Sabtu)
                @else Rekap per minggu @endif
            </p>
        </div>
        <a href="{{ route('attendances.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
            <i class="fa-solid fa-plus text-xs"></i>
            Input Presensi
        </a>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
        <div class="flex border-b border-gray-200">
            <a href="{{ route('attendances.recap', ['type' => 'daily', 'class_name' => $className, 'date' => request('date', now()->toDateString())]) }}"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition {{ $type == 'daily' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700' }}">
                <i class="fa-solid fa-clock mr-1.5"></i> Harian
            </a>
            <a href="{{ route('attendances.recap', ['type' => 'weekly', 'class_name' => $className, 'week_start' => request('week_start', now()->startOfWeek()->toDateString())]) }}"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition {{ $type == 'weekly' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700' }}">
                <i class="fa-solid fa-calendar-week mr-1.5"></i> Mingguan
            </a>
            <a href="{{ route('attendances.recap', ['type' => 'monthly', 'class_name' => $className, 'month' => request('month', now()->month), 'year' => request('year', now()->year)]) }}"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition {{ $type == 'monthly' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700' }}">
                <i class="fa-solid fa-calendar-alt mr-1.5"></i> Bulanan
            </a>
        </div>

        {{-- Filter --}}
        <div class="p-5">
            <form method="GET">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
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

                    @if($type == 'daily')
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal</label>
                        <input type="date" name="date" value="{{ $date ?? now()->toDateString() }}"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                    @elseif($type == 'weekly')
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Awal Minggu</label>
                        <input type="date" name="week_start" value="{{ $start ? $start->toDateString() : now()->startOfWeek()->toDateString() }}"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                    @else
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Bulan</label>
                        <select name="month"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected(($month ?? now()->month) == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tahun</label>
                        <select name="year"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            @foreach(range(now()->year, now()->year - 2, -1) as $y)
                                <option value="{{ $y }}" @selected(($year ?? now()->year) == $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                            <i class="fa-solid fa-filter mr-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== REKAP HARIAN ===== --}}
    @if($type == 'daily')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-clock text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Rekap Harian — {{ \Carbon\Carbon::parse($date ?? now())->translatedFormat('l, d F Y') }}</h3>
                    <p class="text-xs text-gray-400">{{ $students->count() }} siswa @if($className) • Kelas {{ $className }} @endif</p>
                </div>
            </div>
        </div>

        @if($students->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-50/80 z-10">Siswa</th>
                            @foreach($lessonHours as $hour)
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Jam {{ $hour }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($students as $student)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-2.5 whitespace-nowrap sticky left-0 bg-white z-10">
                                    <p class="text-sm font-semibold text-gray-900">{{ $student->full_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $student->class_name }}</p>
                                </td>
                                @foreach($lessonHours as $hour)
                                    @php
                                        $key = $student->id . '-' . $hour;
                                        $att = $attendances->get($key);
                                        $status = $att ? $att->status : '-';
                                        $color = match($status) {
                                            'hadir' => 'text-emerald-600 bg-emerald-50',
                                            'alpha' => 'text-red-600 bg-red-50',
                                            'sakit' => 'text-purple-600 bg-purple-50',
                                            'izin' => 'text-blue-600 bg-blue-50',
                                            'terlambat' => 'text-yellow-600 bg-yellow-50',
                                            default => 'text-gray-300',
                                        };
                                        $icon = match($status) {
                                            'hadir' => 'H',
                                            'alpha' => 'A',
                                            'sakit' => 'S',
                                            'izin' => 'I',
                                            'terlambat' => 'T',
                                            default => '-',
                                        };
                                    @endphp
                                    <td class="px-2 py-2.5 text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold {{ $color }}">
                                            {{ $icon }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 text-xs text-gray-400 text-center flex items-center justify-center gap-4">
                <span><span class="inline-block w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 text-center text-xs font-bold">H</span> Hadir</span>
                <span><span class="inline-block w-7 h-7 rounded-lg bg-red-50 text-red-600 text-center text-xs font-bold">A</span> Alpha</span>
                <span><span class="inline-block w-7 h-7 rounded-lg bg-purple-50 text-purple-600 text-center text-xs font-bold">S</span> Sakit</span>
                <span><span class="inline-block w-7 h-7 rounded-lg bg-blue-50 text-blue-600 text-center text-xs font-bold">I</span> Izin</span>
                <span><span class="inline-block w-7 h-7 rounded-lg bg-yellow-50 text-yellow-600 text-center text-xs font-bold">T</span> Terlambat</span>
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-clock text-gray-300 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-500 mb-0.5">Pilih Kelas & Tanggal</p>
                <p class="text-xs text-gray-400">Pilih kelas dan tanggal untuk melihat rekap harian</p>
            </div>
        @endif
    </div>

    {{-- ===== REKAP MINGGUAN ===== --}}
    @elseif($type == 'weekly')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-calendar-week text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">
                            Rekap Mingguan — {{ \Carbon\Carbon::parse($start)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d M Y') }}
                        </h3>
                        <p class="text-xs text-gray-400">{{ $students->count() }} siswa @if($className) • Kelas {{ $className }} @endif</p>
                    </div>
                </div>
                <a href="{{ route('attendances.export-weekly', ['class_name' => $className, 'week_start' => request('week_start', $start->toDateString())]) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition shadow-sm">
                    <i class="fa-solid fa-download text-xs"></i>
                    Export
                </a>
            </div>
        </div>

        @if($students->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-50/80 border-r-2 border-gray-300 z-10" rowspan="2">Siswa</th>
                            @foreach($recap as $row)
                                @php
                                    $sep = !$loop->last ? 'border-r-2 border-gray-300' : '';
                                @endphp
                                <th colspan="4" class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200 {{ $sep }}">
                                    {{ $row['label'] }}<br>
                                    <span class="text-[10px] text-gray-400 font-normal">{{ \Carbon\Carbon::parse($row['date'])->format('d/m') }}</span>
                                </th>
                            @endforeach
                            <th colspan="4" class="px-3 py-2 text-center text-xs font-semibold text-gray-800 uppercase tracking-wider border-b border-gray-200 border-l-2 border-gray-400 bg-gray-100/80">
                                TOTAL<br>
                                <span class="text-[10px] text-gray-500 font-normal">Hari</span>
                            </th>
                        </tr>
                        <tr class="bg-gray-50/80">
                            @foreach($recap as $row)
                                @php
                                    $sep = !$loop->last ? 'border-r-2 border-gray-300' : '';
                                @endphp
                                <th class="px-2 py-1.5 text-center text-[10px] font-bold text-emerald-600 uppercase tracking-wider">H</th>
                                <th class="px-2 py-1.5 text-center text-[10px] font-bold text-purple-600 uppercase tracking-wider">S</th>
                                <th class="px-2 py-1.5 text-center text-[10px] font-bold text-blue-600 uppercase tracking-wider">I</th>
                                <th class="px-2 py-1.5 text-center text-[10px] font-bold text-red-600 uppercase tracking-wider {{ $sep }}">A</th>
                            @endforeach
                            <th class="px-2 py-1.5 text-center text-[10px] font-bold text-emerald-700 uppercase tracking-wider bg-gray-100/80 border-l-2 border-gray-400">H</th>
                            <th class="px-2 py-1.5 text-center text-[10px] font-bold text-purple-700 uppercase tracking-wider bg-gray-100/80">S</th>
                            <th class="px-2 py-1.5 text-center text-[10px] font-bold text-blue-700 uppercase tracking-wider bg-gray-100/80">I</th>
                            <th class="px-2 py-1.5 text-center text-[10px] font-bold text-red-700 uppercase tracking-wider bg-gray-100/80">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($students as $student)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-2.5 whitespace-nowrap sticky left-0 bg-white border-r-2 border-gray-200 z-10">
                                    <p class="text-sm font-semibold text-gray-900">{{ $student->full_name }}</p>
                                </td>
                                @foreach($recap as $row)
                                    @php
                                        $s = $row['students'][$student->id] ?? null;
                                        $sep = !$loop->last ? 'border-r-2 border-gray-200' : '';
                                    @endphp
                                    @if($s)
                                        <td class="px-2 py-2.5 text-center text-xs font-bold text-emerald-600">{{ $s['hadir'] }}</td>
                                        <td class="px-2 py-2.5 text-center text-xs font-bold text-purple-600">{{ $s['sakit'] }}</td>
                                        <td class="px-2 py-2.5 text-center text-xs font-bold text-blue-600">{{ $s['izin'] }}</td>
                                        <td class="px-2 py-2.5 text-center text-xs font-bold {{ $s['alpha'] > 0 ? 'text-red-600' : 'text-gray-400' }} {{ $sep }}">{{ $s['alpha'] }}</td>
                                    @else
                                        <td class="px-2 py-2.5 text-center text-xs text-gray-300">—</td>
                                        <td class="px-2 py-2.5 text-center text-xs text-gray-300">—</td>
                                        <td class="px-2 py-2.5 text-center text-xs text-gray-300">—</td>
                                        <td class="px-2 py-2.5 text-center text-xs text-gray-300 {{ $sep }}">—</td>
                                    @endif
                                @endforeach
                                {{-- TOTAL --}}
                                @php
                                    $t = $totals[$student->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
                                @endphp
                                <td class="px-2 py-2.5 text-center text-xs font-bold text-emerald-700 bg-gray-50 border-l-2 border-gray-300">{{ $t['hadir'] }}</td>
                                <td class="px-2 py-2.5 text-center text-xs font-bold text-purple-700 bg-gray-50">{{ $t['sakit'] }}</td>
                                <td class="px-2 py-2.5 text-center text-xs font-bold text-blue-700 bg-gray-50">{{ $t['izin'] }}</td>
                                <td class="px-2 py-2.5 text-center text-xs font-bold {{ $t['alpha'] > 0 ? 'text-red-700' : 'text-gray-400' }} bg-gray-50">{{ $t['alpha'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 text-xs text-gray-400 text-center flex items-center justify-center gap-4">
                <span class="text-emerald-600 font-bold">H</span> Hadir
                <span class="text-purple-600 font-bold">S</span> Sakit
                <span class="text-blue-600 font-bold">I</span> Izin
                <span class="text-red-600 font-bold">A</span> Alpha
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-calendar-week text-gray-300 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-500 mb-0.5">Pilih Kelas & Minggu</p>
                <p class="text-xs text-gray-400">Pilih kelas dan minggu untuk melihat rekap mingguan</p>
            </div>
        @endif
    </div>

    {{-- ===== REKAP BULANAN ===== --}}
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-calendar-alt text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">
                            Rekap Bulanan — {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
                        </h3>
                        <p class="text-xs text-gray-400">{{ $students->count() }} siswa @if($className) • Kelas {{ $className }} @endif</p>
                    </div>
                </div>
                <a href="{{ route('attendances.export-monthly', ['class_name' => $className, 'month' => $month, 'year' => $year]) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition shadow-sm">
                    <i class="fa-solid fa-download text-xs"></i>
                    Export
                </a>
            </div>
        </div>

        @if($students->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-50/80 border-r-2 border-gray-300 z-10" rowspan="2">Siswa</th>
                            @foreach($recap as $row)
                                @php
                                    $sep = !$loop->last ? 'border-r-2 border-gray-300' : '';
                                @endphp
                                <th colspan="4" class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200 {{ $sep }}">
                                    {{ $row['label'] }}<br>
                                    <span class="text-[10px] text-gray-400 font-normal">{{ \Carbon\Carbon::parse($row['start'])->format('d/m') }}-{{ \Carbon\Carbon::parse($row['end'])->format('d/m') }}</span>
                                </th>
                            @endforeach
                            <th colspan="4" class="px-3 py-2 text-center text-xs font-semibold text-gray-800 uppercase tracking-wider border-b border-gray-200 border-l-2 border-gray-400 bg-gray-100/80">
                                TOTAL<br>
                                <span class="text-[10px] text-gray-500 font-normal">Hari</span>
                            </th>
                        </tr>
                        <tr class="bg-gray-50/80">
                            @foreach($recap as $row)
                                @php
                                    $sep = !$loop->last ? 'border-r-2 border-gray-300' : '';
                                @endphp
                                <th class="px-2 py-1.5 text-center text-[10px] font-bold text-emerald-600 uppercase tracking-wider">H</th>
                                <th class="px-2 py-1.5 text-center text-[10px] font-bold text-purple-600 uppercase tracking-wider">S</th>
                                <th class="px-2 py-1.5 text-center text-[10px] font-bold text-blue-600 uppercase tracking-wider">I</th>
                                <th class="px-2 py-1.5 text-center text-[10px] font-bold text-red-600 uppercase tracking-wider {{ $sep }}">A</th>
                            @endforeach
                            <th class="px-2 py-1.5 text-center text-[10px] font-bold text-emerald-700 uppercase tracking-wider bg-gray-100/80 border-l-2 border-gray-400">H</th>
                            <th class="px-2 py-1.5 text-center text-[10px] font-bold text-purple-700 uppercase tracking-wider bg-gray-100/80">S</th>
                            <th class="px-2 py-1.5 text-center text-[10px] font-bold text-blue-700 uppercase tracking-wider bg-gray-100/80">I</th>
                            <th class="px-2 py-1.5 text-center text-[10px] font-bold text-red-700 uppercase tracking-wider bg-gray-100/80">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($students as $student)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-2.5 whitespace-nowrap sticky left-0 bg-white border-r-2 border-gray-200 z-10">
                                    <p class="text-sm font-semibold text-gray-900">{{ $student->full_name }}</p>
                                </td>
                                @foreach($recap as $row)
                                    @php
                                        $s = $row['students'][$student->id] ?? null;
                                        $sep = !$loop->last ? 'border-r-2 border-gray-200' : '';
                                    @endphp
                                    @if($s)
                                        <td class="px-2 py-2.5 text-center text-xs font-bold text-emerald-600">{{ $s['hadir'] }}</td>
                                        <td class="px-2 py-2.5 text-center text-xs font-bold text-purple-600">{{ $s['sakit'] }}</td>
                                        <td class="px-2 py-2.5 text-center text-xs font-bold text-blue-600">{{ $s['izin'] }}</td>
                                        <td class="px-2 py-2.5 text-center text-xs font-bold {{ $s['alpha'] > 0 ? 'text-red-600' : 'text-gray-400' }} {{ $sep }}">{{ $s['alpha'] }}</td>
                                    @else
                                        <td class="px-2 py-2.5 text-center text-xs text-gray-300">—</td>
                                        <td class="px-2 py-2.5 text-center text-xs text-gray-300">—</td>
                                        <td class="px-2 py-2.5 text-center text-xs text-gray-300">—</td>
                                        <td class="px-2 py-2.5 text-center text-xs text-gray-300 {{ $sep }}">—</td>
                                    @endif
                                @endforeach
                                {{-- TOTAL --}}
                                @php
                                    $t = $totals[$student->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
                                @endphp
                                <td class="px-2 py-2.5 text-center text-xs font-bold text-emerald-700 bg-gray-50 border-l-2 border-gray-300">{{ $t['hadir'] }}</td>
                                <td class="px-2 py-2.5 text-center text-xs font-bold text-purple-700 bg-gray-50">{{ $t['sakit'] }}</td>
                                <td class="px-2 py-2.5 text-center text-xs font-bold text-blue-700 bg-gray-50">{{ $t['izin'] }}</td>
                                <td class="px-2 py-2.5 text-center text-xs font-bold {{ $t['alpha'] > 0 ? 'text-red-700' : 'text-gray-400' }} bg-gray-50">{{ $t['alpha'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 text-xs text-gray-400 text-center flex items-center justify-center gap-4">
                <span class="text-emerald-600 font-bold">H</span> Hadir
                <span class="text-purple-600 font-bold">S</span> Sakit
                <span class="text-blue-600 font-bold">I</span> Izin
                <span class="text-red-600 font-bold">A</span> Alpha
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-calendar-alt text-gray-300 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-500 mb-0.5">Pilih Kelas & Bulan</p>
                <p class="text-xs text-gray-400">Pilih kelas dan bulan untuk melihat rekap bulanan</p>
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
