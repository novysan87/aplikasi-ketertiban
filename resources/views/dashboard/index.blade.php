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
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10">
                <i class="fa-solid fa-triangle-exclamation text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Pelanggaran Hari Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['today_violations'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">data terbaru</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10">
                <i class="fa-solid fa-list-check text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Total Pelanggaran</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_violations'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">semua waktu</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10">
                <i class="fa-solid fa-users text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Siswa Aktif</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_students'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">terdata</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-700 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10">
                <i class="fa-solid fa-file-lines text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">SP Draft</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['active_sp'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">menunggu tindak lanjut</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Calendar --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden"
            x-data="calendarApp()" x-init="init()">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-calendar text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Kalender Pelanggaran</h2>
                        <p class="text-base font-bold text-gray-700" x-text="monthLabel + ' ' + year"></p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="prevMonth()" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <button @click="nextMonth()" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>

            <div class="p-5">
                {{-- Day headers --}}
                <div class="grid grid-cols-7 mb-2">
                    <template x-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']">
                        <div class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider py-2" x-text="day"></div>
                    </template>
                </div>

                {{-- Calendar grid --}}
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="(day, idx) in days" :key="idx">
                        <div
                            class="relative min-h-[70px] sm:min-h-[80px] rounded-xl border transition-all duration-150 p-1.5"
                            :class="day.isToday
                                ? 'border-blue-300 bg-blue-50/50 ring-1 ring-blue-200'
                                : day.isCurrentMonth
                                    ? 'border-gray-100 hover:border-gray-200 hover:bg-gray-50'
                                    : 'border-gray-50 bg-gray-50/30 text-gray-300'">
                            {{-- Date number --}}
                            <div class="text-xs font-semibold"
                                :class="day.isToday ? 'text-blue-600' : (day.isCurrentMonth ? 'text-gray-700' : 'text-gray-300')"
                                x-text="day.day">
                            </div>
                            {{-- Violation badge --}}
                            <template x-if="day.count > 0 && day.isCurrentMonth">
                                <a :href="'{{ route('violations.index') }}?date_from=' + day.dateStr + '&date_to=' + day.dateStr"
                                    class="absolute bottom-1.5 right-1.5 inline-flex items-center justify-center min-w-[26px] h-[26px] text-xs font-bold text-white rounded-full shadow-sm"
                                    :class="day.count >= 3 ? 'bg-red-500' : (day.count >= 2 ? 'bg-orange-400' : 'bg-blue-400')"
                                    x-text="day.count">
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Legend --}}
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center gap-4 text-[11px] text-gray-500">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-blue-400"></span> 1
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-orange-400"></span> 2
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-red-500"></span> 3+
                </span>
                <span class="ml-auto text-gray-400">Klik badge untuk lihat detail</span>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="space-y-6">
            {{-- SP Thresholds --}}
            @if($spThresholds->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
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
                    @foreach($spThresholds as $threshold)
                        @php
                            $studentsAtRisk = \App\Models\Student::where('is_active', true)->get()->filter(function($s) use ($threshold) {
                                $pts = $s->total_points;
                                $maxOk = $threshold->max_points ?? 9999;
                                return $pts >= $threshold->min_points && $pts <= $maxOk;
                            });
                            $count = $studentsAtRisk->count();
                        @endphp
                        <div class="relative overflow-hidden rounded-xl p-4"
                            style="background-color: {{ $threshold->color }}08; border: 1px solid {{ $threshold->color }}20;">
                            <div class="flex items-center justify-between relative z-10">
                                <div>
                                    <p class="text-sm font-bold" style="color: {{ $threshold->color }}">{{ $threshold->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $threshold->min_points }}{{ $threshold->max_points ? '–'.$threshold->max_points : '+' }} poin</p>
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
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
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
@endpush
@endsection
