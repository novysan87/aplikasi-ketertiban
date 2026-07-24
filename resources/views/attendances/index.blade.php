@extends('layouts.app')

@section('title', 'Presensi Siswa')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Presensi Siswa</h1>
            <p class="text-sm text-gray-500 mt-1">Pencatatan kehadiran siswa per jam pelajaran</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('attendances.recap') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-violet-600 bg-violet-50 border border-violet-200 rounded-xl hover:bg-violet-100 transition shadow-sm">
                <i class="fa-solid fa-chart-simple text-xs"></i>
                Rekap Bulanan
            </a>
            <a href="{{ route('attendances.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                <i class="fa-solid fa-plus text-xs"></i>
                Input Presensi
            </a>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10"><i class="fa-solid fa-clipboard-check text-white text-6xl"></i></div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Siswa Dipresensi Hari Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $todayStudents }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">siswa tercatat</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-500 to-red-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10"><i class="fa-solid fa-xmark text-white text-6xl"></i></div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Siswa Alpha Hari Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $todayAlphaStudents }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">siswa tanpa keterangan</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10"><i class="fa-solid fa-user text-white text-6xl"></i></div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Siswa Bulan Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $monthStudents }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">pernah dipresensi</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10"><i class="fa-solid fa-chart-bar text-white text-6xl"></i></div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Siswa Alpha Bulan Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $monthAlphaStudents }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">pernah alpha</p>
            </div>
        </div>
    </div>

    {{-- Kalender Presensi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden"
        x-data="attendanceCalendar({{ json_encode($calendarData) }})" x-init="init">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-500 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-calendar text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Kalender Presensi</h3>
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
                    <div @click="day.total > 0 && day.isCurrentMonth ? window.location.href = '{{ route('attendances.create', ['date' => '']) }}' + day.dateStr : null"
                        class="relative min-h-[70px] sm:min-h-[80px] rounded-xl border transition-all duration-150 p-1.5"
                        :class="day.total > 0 && day.isCurrentMonth ? 'cursor-pointer' : 'cursor-default'"
                        :class="day.isToday
                            ? day.total > 0
                                ? (day.alpha > 0 ? 'border-red-300 bg-red-50 ring-2 ring-red-200' : 'border-emerald-300 bg-emerald-50 ring-2 ring-emerald-200')
                                : 'border-blue-300 bg-blue-50 ring-2 ring-blue-200'
                            : day.isCurrentMonth && day.total > 0
                                ? (day.alpha > 0
                                    ? 'border-red-200 bg-red-50/70 hover:bg-red-100 hover:border-red-300'
                                    : 'border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100 hover:border-emerald-300')
                                : day.isCurrentMonth
                                    ? 'border-gray-100 hover:border-gray-200 hover:bg-gray-50'
                                    : 'border-gray-50 bg-gray-50/30 text-gray-300 cursor-default'">
                        {{-- Date number + total --}}
                        <div class="flex items-start justify-between">
                            <div class="text-xs font-semibold"
                                :class="day.isToday ? 'text-blue-600' : (day.isCurrentMonth ? 'text-gray-700' : 'text-gray-300')"
                                x-text="day.day">
                            </div>
                            <template x-if="day.total > 0 && day.isCurrentMonth">
                                <span class="text-[10px] font-bold"
                                    :class="day.alpha > 0 ? 'text-red-500' : 'text-emerald-600'">
                                    <i class="fa-solid" :class="day.alpha > 0 ? 'fa-circle-exclamation' : 'fa-check-circle'"></i>
                                </span>
                            </template>
                        </div>
                        {{-- Info bar --}}
                        <template x-if="day.total > 0 && day.isCurrentMonth">
                            <div class="mt-1">
                                <div class="flex items-center justify-between text-[9px] text-gray-400">
                                    <span x-text="day.total + ' siswa'"></span>
                                    <template x-if="day.alpha > 0">
                                        <span class="text-red-400 font-medium" x-text="day.alpha + ' alpha'"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
        {{-- Legend --}}
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center gap-4 text-[11px] text-gray-500">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-emerald-200 border border-emerald-400"></span> Semua hadir
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-red-50 border border-red-300"></span> Ada alpha
            </span>
            <span class="ml-auto text-gray-400">&#128073; Klik kotak untuk input presensi</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function attendanceCalendar(initialData) {
        initialData = initialData || {};
        return {
            year: {{ now()->year }},
            month: {{ now()->month }},
            days: [],

            get monthLabel() {
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return months[this.month - 1] || '';
            },

            init() {
                this.attendanceData = Object.keys(initialData).length > 0 ? initialData : {};
                this.render();
            },

            render() {
                const firstDay = new Date(this.year, this.month - 1, 1);
                const lastDay = new Date(this.year, this.month, 0);
                const startPad = firstDay.getDay();
                const daysInMonth = lastDay.getDate();
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
                    this.days.push({ day: pd, isCurrentMonth: false, isToday: false, total: 0, alpha: 0, dateStr: dateStr });
                }

                // Current month days
                for (let d = 1; d <= daysInMonth; d++) {
                    const dateStr = this.year + '-' + String(this.month).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                    const isToday = dateStr === todayStr;
                    const data = this.attendanceData?.[dateStr] || { total: 0, alpha: 0 };
                    this.days.push({
                        day: d,
                        isCurrentMonth: true,
                        isToday: isToday,
                        total: data.total,
                        alpha: data.alpha,
                        dateStr: dateStr
                    });
                }

                // Next month padding
                const remaining = 7 - (this.days.length % 7);
                if (remaining < 7) {
                    for (let d = 1; d <= remaining; d++) {
                        const m = this.month === 12 ? 1 : this.month + 1;
                        const y = this.month === 12 ? this.year + 1 : this.year;
                        const dateStr = y + '-' + String(m).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                        this.days.push({ day: d, isCurrentMonth: false, isToday: false, total: 0, alpha: 0, dateStr: dateStr });
                    }
                }
            },

            prevMonth() {
                if (this.month === 1) { this.month = 12; this.year--; }
                else { this.month--; }
                this.fetchAndRender();
            },

            nextMonth() {
                if (this.month === 12) { this.month = 1; this.year++; }
                else { this.month++; }
                this.fetchAndRender();
            },

            fetchAndRender() {
                fetch('{{ route('attendances.calendar-data') }}?year=' + this.year + '&month=' + this.month)
                    .then(r => r.json())
                    .then(data => {
                        this.attendanceData = data;
                        this.render();
                    });
            }
        };
    }
</script>
@endpush
