@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Ringkasan pelanggaran siswa</p>
    </div>

    {{-- Gradient Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 p-5 shadow-sm stat-card-glow transition-all duration-200">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10 transition-all duration-500 ease-out group-hover:scale-150 group-hover:opacity-25">
                <i class="fa-solid fa-triangle-exclamation text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Pelanggaran Hari Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['today_violations'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">data terbaru</p>
            </div>
        </div>
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-5 shadow-sm stat-card-glow transition-all duration-200">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10 transition-all duration-500 ease-out group-hover:scale-150 group-hover:opacity-25">
                <i class="fa-solid fa-list-check text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Total Pelanggaran</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_violations'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">semua waktu</p>
            </div>
        </div>
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-5 shadow-sm stat-card-glow transition-all duration-200">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10 transition-all duration-500 ease-out group-hover:scale-150 group-hover:opacity-25">
                <i class="fa-solid fa-users text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Siswa Aktif</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_students'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">terdata</p>
            </div>
        </div>
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-700 p-5 shadow-sm stat-card-glow transition-all duration-200">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10 transition-all duration-500 ease-out group-hover:scale-150 group-hover:opacity-25">
                <i class="fa-solid fa-file-lines text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">SP Draft</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['active_sp'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">menunggu tindak lanjut</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Grafik Tren Pelanggaran --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" x-data="trendToggle()">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center text-white shadow-sm">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Tren Pelanggaran Siswa</h3>
                    <p class="text-[11px] text-gray-400">Jumlah pelanggaran per hari</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-gray-500">Total <span x-text="period"></span> hari: <strong class="text-gray-800" x-text="period === 7 ? total7 : (period === 14 ? total14 : total30)"></strong></span>
                <span class="text-xs font-semibold text-gray-500">Hari ini: <strong class="text-gray-800">{{ $dailyCurrent }}</strong></span>
                @if ($trendPercent > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold text-red-600 ring-1 ring-red-200">
                        <i class="fa-solid fa-arrow-trend-up"></i> +{{ $trendPercent }}% vs kemarin
                    </span>
                @elseif ($trendPercent < 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-600 ring-1 ring-emerald-200">
                        <i class="fa-solid fa-arrow-trend-down"></i> {{ $trendPercent }}% vs kemarin
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2.5 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200">
                        <i class="fa-solid fa-minus"></i> Stabil vs kemarin
                    </span>
                @endif
                <div class="flex items-center gap-1 p-1 rounded-xl bg-gray-100">
                    <button type="button" @click="setPeriod(7)" :class="period === 7 ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 text-xs font-bold rounded-lg transition">7 Hari</button>
                    <button type="button" @click="setPeriod(14)" :class="period === 14 ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 text-xs font-bold rounded-lg transition">14 Hari</button>
                    <button type="button" @click="setPeriod(30)" :class="period === 30 ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 text-xs font-bold rounded-lg transition">30 Hari</button>
                </div>
            </div>
        </div>
        <div class="p-5">
            <div class="relative h-64">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Grafik Jenis Pelanggaran --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white shadow-sm">
                    <i class="fa-solid fa-chart-column"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Pelanggaran per Jenis</h3>
                    <p class="text-[11px] text-gray-400">Jenis pelanggaran paling banyak</p>
                </div>
            </div>
            <div class="flex items-center gap-1 rounded-xl bg-gray-100 p-1" x-data="{ typePeriod: 'today' }">
                <button type="button" @click="typePeriod = 'today'; setTypePeriod('today')" class="rounded-lg px-3 py-1.5 text-[11px] font-bold transition"
                    :class="typePeriod === 'today' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">Hari Ini</button>
                <button type="button" @click="typePeriod = 'week'; setTypePeriod('week')" class="rounded-lg px-3 py-1.5 text-[11px] font-bold transition"
                    :class="typePeriod === 'week' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">7 Hari</button>
                <button type="button" @click="typePeriod = 'month'; setTypePeriod('month')" class="rounded-lg px-3 py-1.5 text-[11px] font-bold transition"
                    :class="typePeriod === 'month' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">Bulan Ini</button>
            </div>
        </div>
        <div class="p-5">
            <div class="relative h-64">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Calendar --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
            x-data="calendarApp()" x-init="init()">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25">
                        <i class="fa-solid fa-calendar text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Kalender Pelanggaran</h2>
                        <div class="flex items-center gap-2">
                            <p class="text-base font-extrabold text-slate-700 tracking-tight" x-text="monthLabel + ' ' + year"></p>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 border border-blue-100 text-[10px] font-bold text-blue-600">
                                <i class="fa-solid fa-triangle-exclamation text-[8px]"></i>
                                <span x-text="monthTotal + ' pelanggaran'"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <button @click="currentMonth()"
                        class="px-3 py-2 rounded-xl text-xs font-bold text-blue-600 bg-blue-50 border border-blue-100 hover:bg-blue-100 transition shadow-sm">
                        Hari Ini
                    </button>
                    <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-100">
                        <button @click="prevMonth()" class="p-2 rounded-lg hover:bg-white hover:shadow-sm text-slate-500 hover:text-blue-600 transition" title="Bulan sebelumnya">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <button @click="nextMonth()" class="p-2 rounded-lg hover:bg-white hover:shadow-sm text-slate-500 hover:text-blue-600 transition" title="Bulan berikutnya">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-5">
                {{-- Day headers --}}
                <div class="grid grid-cols-7 mb-2">
                    <template x-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']">
                        <div class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.12em] py-1.5" x-text="day"></div>
                    </template>
                </div>

                {{-- Calendar grid --}}
                <div class="grid grid-cols-7 gap-1.5">
                    <template x-for="(day, idx) in days" :key="idx">
                        <div
                            class="relative min-h-[76px] sm:min-h-[84px] rounded-xl border transition-all duration-150 p-2 group"
                            :class="day.isToday
                                ? 'border-blue-400 bg-gradient-to-b from-blue-50 to-white ring-2 ring-blue-200/70 shadow-sm'
                                : day.isCurrentMonth
                                    ? 'border-slate-100 hover:border-blue-200 hover:shadow-md hover:-translate-y-0.5 hover:bg-slate-50/50'
                                    : 'border-slate-50 bg-slate-50/40 text-slate-300'">
                            {{-- Date number --}}
                            <div class="text-xs font-bold"
                                :class="day.isToday ? 'text-blue-600' : (day.isCurrentMonth ? 'text-slate-700' : 'text-slate-300')"
                                x-text="day.day">
                            </div>
                            {{-- Label hari ini --}}
                            <div x-show="day.isToday" class="mt-0.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-blue-600 text-white text-[9px] font-bold shadow-sm shadow-blue-500/30">
                                    <i class="fa-solid fa-location-dot text-[7px]"></i> Hari Ini
                                </span>
                            </div>
                            {{-- Violation badge --}}
                            <template x-if="day.count > 0 && day.isCurrentMonth">
                                <a :href="'{{ route('violations.index') }}?date_from=' + day.dateStr + '&date_to=' + day.dateStr"
                                    class="absolute bottom-1.5 right-1.5 inline-flex items-center justify-center min-w-[28px] h-[28px] text-xs font-extrabold text-white rounded-full shadow-lg transition-transform hover:scale-110"
                                    :class="day.count >= 3 ? 'bg-gradient-to-br from-red-500 to-rose-600 shadow-red-500/30' : (day.count >= 2 ? 'bg-gradient-to-br from-orange-400 to-orange-500 shadow-orange-400/30' : 'bg-gradient-to-br from-blue-400 to-blue-500 shadow-blue-400/30')"
                                    x-tooltip="day.count + ' pelanggaran'"
                                    x-text="day.count">
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Legend --}}
            <div class="px-6 py-3 border-t border-slate-100 bg-gradient-to-r from-slate-50/80 to-blue-50/40 flex items-center gap-4 text-[11px] text-slate-500 flex-wrap">
                <span class="inline-flex items-center gap-1.5 font-semibold">
                    <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-blue-400 to-blue-500 shadow-sm"></span> 1 pelanggaran
                </span>
                <span class="inline-flex items-center gap-1.5 font-semibold">
                    <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-orange-400 to-orange-500 shadow-sm"></span> 2 pelanggaran
                </span>
                <span class="inline-flex items-center gap-1.5 font-semibold">
                    <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-red-500 to-rose-600 shadow-sm"></span> 3+ pelanggaran
                </span>
                <span class="ml-auto inline-flex items-center gap-1 text-slate-400">
                    <i class="fa-solid fa-hand-pointer text-[10px]"></i> Klik badge untuk lihat detail
                </span>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="space-y-6">
            {{-- SP Thresholds --}}
            @if($spThresholds->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-chart-bar text-white text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Ambang SP</h2>
                            <p class="text-xs text-gray-400">Siswa per level</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    @foreach($spThresholds as $i => $threshold)
                        @php
                            // Batas atas level ini = ambang minimal level berikutnya
                            $nextMin = $spThresholds[$i + 1]->min_points ?? null;
                            $studentsAtRisk = \App\Models\Student::where('is_active', true)->get()->filter(function($s) use ($threshold, $nextMin) {
                                $pts = $s->total_points;
                                if ($nextMin !== null) {
                                    return $pts >= $threshold->min_points && $pts < $nextMin;
                                }
                                return $pts >= $threshold->min_points;
                            });
                            $count = $studentsAtRisk->count();
                        @endphp
                        <div class="relative overflow-hidden rounded-xl p-4"
                            style="background-color: {{ $threshold->color }}08; border: 1px solid {{ $threshold->color }}20;">
                            <div class="flex items-center justify-between relative z-10">
                                <div>
                                    <p class="text-sm font-bold" style="color: {{ $threshold->color }}">{{ $threshold->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $threshold->min_points }}{{ $nextMin !== null ? '–'.($nextMin - 1) : '+' }} poin</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold" style="color: {{ $threshold->color }}">{{ $count }}</p>
                                    <p class="text-[10px] text-gray-400">siswa</p>
                                </div>
                            </div>
                            @if($count > 0)
                            <div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ min(100, ($count / max(1, $stats['total_students'])) * 100) }}%; background-color: {{ $threshold->color }}"></div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Top 5 Students --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-crown text-white text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Poin Tertinggi</h2>
                            <p class="text-xs text-gray-400">Top 5 siswa</p>
                        </div>
                    </div>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($topStudents as $student)
                        <div class="flex items-center justify-between px-6 py-3.5 hover:bg-gray-50/50 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0
                                    {{ $loop->first ? 'bg-yellow-100 text-yellow-700' : ($loop->iteration <= 3 ? 'bg-gray-100 text-gray-600' : 'bg-gray-50 text-gray-400') }}">
                                    <i class="fa-solid {{ $loop->first ? 'fa-crown' : ($loop->iteration <= 3 ? 'fa-medal' : 'fa-hashtag') }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate max-w-[140px]">{{ $student->full_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $student->class_name }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold {{ $student->total_points >= 100 ? 'text-red-600' : ($student->total_points >= 50 ? 'text-yellow-600' : 'text-blue-600') }}">
                                {{ $student->total_points }}
                            </span>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-database text-gray-300 text-lg"></i>
                            </div>
                            <p class="text-sm text-gray-500">Belum ada data</p>
                        </div>
                    @endforelse
                </div>
                @if(count($topStudents) > 0)
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
                    <a href="{{ route('students.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                        Lihat semua siswa
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function calendarApp() {
        return {
            year: {{ now()->year }},
            month: {{ now()->month }},
            days: [],
            violations: @json($calendarData),

            get monthLabel() {
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return months[this.month - 1] || '';
            },

            get monthTotal() {
                return Object.values(this.violations || {}).reduce((a, b) => a + b, 0);
            },

            init() {
                this.render();
            },

            render() {
                const firstDay = new Date(this.year, this.month - 1, 1);
                const lastDay = new Date(this.year, this.month, 0);
                const startPad = firstDay.getDay();
                const daysInMonth = lastDay.getDate();

                // Prev month days for padding
                const prevLastDay = new Date(this.year, this.month - 1, 0);
                const prevDaysInMonth = prevLastDay.getDate();

                const today = new Date();
                const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');

                this.days = [];

                // Previous month trailing days
                for (let i = startPad - 1; i >= 0; i--) {
                    const pd = prevDaysInMonth - i;
                    const m = this.month === 1 ? 12 : this.month - 1;
                    const y = this.month === 1 ? this.year - 1 : this.year;
                    const dateStr = y + '-' + String(m).padStart(2, '0') + '-' + String(pd).padStart(2, '0');
                    this.days.push({
                        day: pd,
                        isCurrentMonth: false,
                        isToday: false,
                        count: 0,
                        dateStr: dateStr
                    });
                }

                // Current month days
                for (let d = 1; d <= daysInMonth; d++) {
                    const dateStr = this.year + '-' + String(this.month).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                    const isToday = dateStr === todayStr;
                    this.days.push({
                        day: d,
                        isCurrentMonth: true,
                        isToday: isToday,
                        count: this.violations[dateStr] || 0,
                        dateStr: dateStr
                    });
                }

                // Next month leading days (to complete last row)
                const remaining = 7 - (this.days.length % 7);
                if (remaining < 7) {
                    for (let d = 1; d <= remaining; d++) {
                        const m = this.month === 12 ? 1 : this.month + 1;
                        const y = this.month === 12 ? this.year + 1 : this.year;
                        const dateStr = y + '-' + String(m).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                        this.days.push({
                            day: d,
                            isCurrentMonth: false,
                            isToday: false,
                            count: 0,
                            dateStr: dateStr
                        });
                    }
                }
            },

            prevMonth() {
                if (this.month === 1) {
                    this.month = 12;
                    this.year--;
                } else {
                    this.month--;
                }
                this.fetchAndRender();
            },

            nextMonth() {
                if (this.month === 12) {
                    this.month = 1;
                    this.year++;
                } else {
                    this.month++;
                }
                this.fetchAndRender();
            },

            currentMonth() {
                const now = new Date();
                this.year = now.getFullYear();
                this.month = now.getMonth() + 1;
                this.fetchAndRender();
            },

            fetchAndRender() {
                fetch('{{ route('calendar.data') }}?year=' + this.year + '&month=' + this.month)
                    .then(r => r.json())
                    .then(data => {
                        this.violations = data;
                        this.render();
                    });
            }
        };
    }
</script>

<script src="/vendor/chartjs/chart.umd.min.js"></script>
<script>
    function trendToggle() {
        return {
            period: 7,
            total7: {{ $trendTotal7 }},
            total14: {{ $trendTotal14 }},
            total30: {{ $trendTotal30 }},
            labels7: @json($dailyLabels7),
            data7: @json($dailyData7),
            labels14: @json($dailyLabels14),
            data14: @json($dailyData14),
            labels30: @json($dailyLabels30),
            data30: @json($dailyData30),
            chart: null,

            init() {
                const ctx = document.getElementById('trendChart');
                if (!ctx || typeof Chart === 'undefined') return;
                const g = ctx.getContext('2d');
                const grad = g.createLinearGradient(0, 0, 0, 260);
                grad.addColorStop(0, 'rgba(37, 99, 235, 0.28)');
                grad.addColorStop(1, 'rgba(37, 99, 235, 0.02)');

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.labels7,
                        datasets: [
                            {
                                label: 'Pelanggaran',
                                data: this.data7,
                                borderColor: '#2563eb',
                                backgroundColor: grad,
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBackgroundColor: '#2563eb',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                cornerRadius: 10,
                                titleFont: { weight: 'bold' },
                                callbacks: {
                                    label: (ctx) => ctx.parsed.y + ' pelanggaran'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, color: '#94a3b8' },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                ticks: { color: '#94a3b8', maxRotation: 0, autoSkip: true, maxTicksLimit: 15 },
                                grid: { display: false }
                            }
                        }
                    }
                });
            },

            setPeriod(n) {
                this.period = n;
                if (!this.chart) return;
                this.chart.data.labels = n === 7 ? this.labels7 : (n === 14 ? this.labels14 : this.labels30);
                this.chart.data.datasets[0].data = n === 7 ? this.data7 : (n === 14 ? this.data14 : this.data30);
                this.chart.update();
            },
        };
    }

    {{-- Grafik batang: pelanggaran per jenis --}}
    const typeCtx = document.getElementById('typeChart');
    let typeChart = null;
    if (typeCtx && typeof Chart !== 'undefined') {
        typeChart = new Chart(typeCtx, {
            type: 'bar',
            data: {
                labels: @json($typeNames),
                datasets: [
                    { label: 'Hari Ini', data: @json($typeToday), backgroundColor: 'rgba(249, 115, 22, 0.85)', borderRadius: 8, barThickness: 18 },
                    { label: '7 Hari', data: @json($typeWeek), backgroundColor: 'rgba(59, 130, 246, 0.85)', borderRadius: 8, barThickness: 18, hidden: true },
                    { label: 'Bulan Ini', data: @json($typeMonth), backgroundColor: 'rgba(139, 92, 246, 0.85)', borderRadius: 8, barThickness: 18, hidden: true }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: { label: (ctx) => ctx.parsed.x + ' pelanggaran' }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0, color: '#94a3b8' }, grid: { color: '#f1f5f9' } },
                    y: { ticks: { color: '#64748b', font: { size: 11 } }, grid: { display: false } }
                }
            }
        });
    }
    window.setTypePeriod = function (p) {
        if (!typeChart) return;
        const map = { today: 0, week: 1, month: 2 };
        Object.keys(map).forEach(k => {
            typeChart.data.datasets[map[k]].hidden = k !== p;
        });
        typeChart.update();
    };
</script>
@endpush
@endsection
