@extends('layouts.app')

@section('title', 'Laporan Pelanggaran')

@section('content')
@php $selectedClass = $classes->firstWhere('id', $filters['class_id']); @endphp
<div x-data="reportPage()" class="space-y-6">

    {{-- ===== Hero ===== --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 shadow-lg">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>
        <div class="absolute -right-20 -top-20 w-72 h-72 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative px-6 py-7 lg:px-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur border border-white/20">
                    <i class="fa-solid fa-file-lines text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">Laporan Rekap Pelanggaran</h1>
                    <p class="text-sm text-blue-100 mt-1.5">
                        Rekap pelanggaran per periode untuk BK &amp; Kepala Sekolah — cetak langsung atau unduh PDF.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 mt-3 text-[11px] font-medium">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-blue-50 border border-white/15">
                            <i class="fa-solid fa-calendar-days text-[10px]"></i> Periode {{ $filters['date_from'] ?? 'Awal' }} — {{ $filters['date_to'] ?? 'Sekarang' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-blue-50 border border-white/15">
                            <i class="fa-solid fa-users text-[10px]"></i> {{ $selectedClass?->name ?? 'Semua Kelas' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button form="filter-form" formaction="{{ route('reports.violations.pdf') }}" formtarget="_blank"
                    class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-blue-800 bg-white rounded-xl hover:bg-blue-50 transition shadow-md">
                    <i class="fa-solid fa-file-pdf"></i> Cetak Laporan PDF
                </button>
                <button form="filter-form" formaction="{{ route('violations.export') }}"
                    class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-white bg-emerald-500/90 hover:bg-emerald-500 rounded-xl transition shadow-md border border-white/20">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
    </div>

    {{-- ===== Filter Bar ===== --}}
    <form method="GET" action="{{ route('reports.violations') }}" id="filter-form"
          class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                <div class="relative">
                    <i class="fa-solid fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" x-model="dateFrom"
                        class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                <div class="relative">
                    <i class="fa-solid fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" x-model="dateTo"
                        class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Kelas</label>
                <div class="relative">
                    <i class="fa-solid fa-users absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <select name="class_id" x-model="classId"
                        class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition appearance-none">
                        <option value="">— Semua Kelas —</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected($filters['class_id'] == $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                    <i class="fa-solid fa-filter text-xs"></i> Terapkan Filter
                </button>
            </div>
        </div>

        {{-- Quick presets --}}
        <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-gray-100">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cepat:</span>
            @foreach ([
                '7 Hari Terakhir' => [now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d')],
                '30 Hari Terakhir' => [now()->subDays(29)->format('Y-m-d'), now()->format('Y-m-d')],
                'Semester Ganjil' => [now()->month >= 7 ? now()->year.'-07-01' : now()->subYear()->year.'-07-01', now()->month >= 7 ? now()->year.'-12-31' : now()->subYear()->year.'-12-31'],
                'Semester Genap' => [now()->month >= 7 ? (now()->year + 1).'-01-01' : now()->year.'-01-01', now()->month >= 7 ? (now()->year + 1).'-06-30' : now()->year.'-06-30'],
                'Tahun Ini' => [now()->year.'-01-01', now()->year.'-12-31'],
            ] as $label => [$from, $to])
                <button type="button" @click="applyPreset('{{ $from }}', '{{ $to }}')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition">
                    {{ $label }}
                </button>
            @endforeach
            <button type="button" @click="resetFilter()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>
        </div>
    </form>

    {{-- ===== KPI ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-blue-700 p-5 shadow-sm">
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-white">{{ number_format($stats['totalKasus']) }}</div>
                    <div class="text-xs text-blue-100 mt-1 uppercase tracking-wider font-medium">Total Kasus</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-white"></i>
                </div>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 shadow-sm">
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-white">{{ number_format($stats['totalPoin']) }}</div>
                    <div class="text-xs text-amber-100 mt-1 uppercase tracking-wider font-medium">Total Poin</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center">
                    <i class="fa-solid fa-bolt text-white"></i>
                </div>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-rose-700 p-5 shadow-sm">
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-white">{{ number_format($stats['siswaTerlibat']) }}</div>
                    <div class="text-xs text-rose-100 mt-1 uppercase tracking-wider font-medium">Siswa Terlibat</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center">
                    <i class="fa-solid fa-users text-white"></i>
                </div>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-700 p-5 shadow-sm">
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-lg font-bold text-white leading-tight truncate">{{ $stats['jenisTerbanyak']['jenis'] ?? 'Belum Ada' }}</div>
                    <div class="text-xs text-violet-100 mt-1 uppercase tracking-wider font-medium">Jenis Terbanyak</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-ranking-star text-white"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Rekap: per Jenis & per Kelas ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        {{-- Per Jenis --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-tag text-sm"></i></span>
                    Rekap per Jenis Pelanggaran
                </h3>
                <span class="text-xs font-semibold text-gray-400">{{ $stats['perJenis']->count() }} jenis</span>
            </div>
            @forelse ($stats['perJenis']->take(8) as $item)
                @php $max = max($stats['perJenis']->max('jumlah'), 1); @endphp
                <div class="mb-4 last:mb-0">
                    <div class="flex items-center justify-between text-sm mb-1.5">
                        <span class="font-semibold text-gray-700 truncate pr-3">{{ $item['jenis'] }}</span>
                        <span class="text-xs text-gray-500 shrink-0">
                            <span class="font-bold text-blue-600">{{ $item['jumlah'] }}×</span> · {{ $item['poin'] }} poin
                        </span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all"
                             style="width: {{ round($item['jumlah'] / $max * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-50 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-chart-pie text-gray-300 text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-400">Belum ada data pada periode ini</p>
                </div>
            @endforelse
        </div>

        {{-- Per Kelas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-building-columns text-sm"></i></span>
                    Rekap per Kelas
                </h3>
                <span class="text-xs font-semibold text-gray-400">{{ $stats['perKelas']->count() }} kelas</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
                            <th class="pb-2.5 font-semibold">Kelas</th>
                            <th class="pb-2.5 text-center font-semibold">Kasus</th>
                            <th class="pb-2.5 text-right font-semibold">Poin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($stats['perKelas']->take(10) as $i => $item)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-2.5 font-semibold text-gray-700">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg {{ ['bg-blue-50 text-blue-600','bg-emerald-50 text-emerald-600','bg-amber-50 text-amber-600','bg-rose-50 text-rose-600','bg-violet-50 text-violet-600'][$i % 5] }} flex items-center justify-center text-[10px] font-bold">
                                            {{ strtoupper(substr($item['kelas'], 0, 2)) }}
                                        </span>
                                        {{ $item['kelas'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">{{ $item['jumlah'] }}</span>
                                </td>
                                <td class="py-2.5 text-right font-semibold text-gray-600">{{ $item['poin'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center">
                                    <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-50 flex items-center justify-center mb-3">
                                        <i class="fa-solid fa-building-columns text-gray-300 text-xl"></i>
                                    </div>
                                    <p class="text-sm text-gray-400">Belum ada data pada periode ini</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Rincian Terbaru ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-list-check text-sm"></i></span>
                Rincian Pelanggaran Terbaru
            </h3>
            <a href="{{ route('violations.index', request()->only(['date_from', 'date_to'])) }}"
               class="text-xs font-semibold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1.5">
                Lihat Semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/70">
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">NISN</th>
                        <th class="px-4 py-3 font-semibold">Siswa</th>
                        <th class="px-4 py-3 font-semibold">Kelas</th>
                        <th class="px-4 py-3 font-semibold">Jenis</th>
                        <th class="px-4 py-3 text-center font-semibold">Poin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($recent as $v)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-6 py-3 text-gray-500 whitespace-nowrap">{{ $v->violation_date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $v->student?->nisn }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $v->student?->full_name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-600">{{ $v->student?->class?->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $v->violationType?->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $v->points >= 10 ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700' }}">{{ $v->points }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-50 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-folder-open text-gray-300 text-2xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-500">Belum ada pelanggaran pada periode ini</p>
                                <p class="text-xs text-gray-400 mt-1">Ubah periode atau filter kelas untuk melihat data</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function reportPage() {
        return {
            dateFrom: {{ json_encode($filters['date_from'] ?? '') }},
            dateTo: {{ json_encode($filters['date_to'] ?? '') }},
            classId: {{ json_encode($filters['class_id'] ?? '') }},
            applyPreset(from, to) {
                this.dateFrom = from;
                this.dateTo = to;
                this.$nextTick(() => document.getElementById('filter-form').submit());
            },
            resetFilter() {
                this.dateFrom = '';
                this.dateTo = '';
                this.classId = '';
                this.$nextTick(() => document.getElementById('filter-form').submit());
            },
        };
    }
</script>
@endsection
