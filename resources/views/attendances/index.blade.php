@extends('layouts.app')

@section('title', 'Presensi Siswa')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Presensi Siswa</h1>
            <p class="text-sm text-gray-500 mt-1">Pantau dan kelola kehadiran siswa per jam pelajaran</p>
        </div>
        <a href="{{ route('attendances.create') }}"
            class="btn-primary flex-shrink-0">
            <i class="fa-solid fa-plus text-xs"></i>
            Input Presensi
        </a>
    </div>

    {{-- Stats Hari Ini --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-5 shadow-sm stat-card-glow transition-all duration-200">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10 transition-all duration-500 ease-out group-hover:scale-150 group-hover:opacity-25">
                <i class="fa-regular fa-calendar-check text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Siswa Hari Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $todayStudents }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">tercatat hari ini</p>
            </div>
        </div>
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-5 shadow-sm stat-card-glow transition-all duration-200">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10 transition-all duration-500 ease-out group-hover:scale-150 group-hover:opacity-25">
                <i class="fa-solid fa-circle-exclamation text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Alpha Hari Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $todayAlphaStudents }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">perlu perhatian</p>
            </div>
        </div>
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 p-5 shadow-sm stat-card-glow transition-all duration-200">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10 transition-all duration-500 ease-out group-hover:scale-150 group-hover:opacity-25">
                <i class="fa-regular fa-calendar-days text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Siswa Bulan Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $monthStudents }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">sepanjang bulan berjalan</p>
            </div>
        </div>
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 p-5 shadow-sm stat-card-glow transition-all duration-200">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10 transition-all duration-500 ease-out group-hover:scale-150 group-hover:opacity-25">
                <i class="fa-solid fa-triangle-exclamation text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Alpha Bulan Ini</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $monthAlphaStudents }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">akumulasi bulan berjalan</p>
            </div>
        </div>
    </div>

    {{-- Kalender Presensi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
        x-data="attendanceCalendar({{ json_encode($calendarData) }})" x-init="init">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-500 flex items-center justify-center text-white shadow-md shadow-emerald-500/25">
                    <i class="fa-solid fa-calendar text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Kalender Presensi</h3>
                    <div class="flex items-center gap-2">
                        <p class="text-base font-extrabold text-slate-700 tracking-tight" x-text="monthLabel + ' ' + year"></p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-[10px] font-bold text-emerald-600">
                            <i class="fa-solid fa-user-check text-[8px]"></i>
                            <span x-text="monthTotal + ' presensi'"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button @click="currentMonth()"
                    class="px-3 py-2 rounded-xl text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition shadow-sm">
                    Hari Ini
                </button>
                <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-100">
                    <button @click="prevMonth()" class="p-2 rounded-lg hover:bg-white hover:shadow-sm text-slate-500 hover:text-emerald-600 transition" title="Bulan sebelumnya">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <button @click="nextMonth()" class="p-2 rounded-lg hover:bg-white hover:shadow-sm text-slate-500 hover:text-emerald-600 transition" title="Bulan berikutnya">
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
                    <a :href="day.total > 0 && day.isCurrentMonth ? '{{ route('attendances.create', ['date' => '']) }}' + day.dateStr : null"
                        class="relative min-h-[76px] sm:min-h-[84px] rounded-xl border transition-all duration-150 p-2 block group/cal"
                        :class="[
                            day.isToday
                                ? 'border-blue-400 bg-gradient-to-b from-blue-50 to-white ring-2 ring-blue-200/70 shadow-sm'
                                : day.isCurrentMonth && day.total > 0
                                    ? 'border-emerald-100 hover:border-emerald-300 hover:shadow-md hover:-translate-y-0.5 hover:bg-emerald-50/40'
                                    : day.isCurrentMonth
                                        ? 'border-slate-100 hover:border-slate-300 hover:shadow-sm hover:bg-slate-50/60'
                                        : 'border-slate-50 bg-slate-50/40 text-slate-300',
                            day.total > 0 && day.isCurrentMonth ? 'cursor-pointer' : 'cursor-default'
                        ]">
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
                        {{-- Badge jumlah siswa --}}
                        <template x-if="day.total > 0 && day.isCurrentMonth">
                            <span class="absolute bottom-1.5 right-1.5 inline-flex items-center justify-center min-w-[28px] h-[28px] text-xs font-extrabold text-white rounded-full shadow-lg transition-transform hover:scale-110"
                                :class="day.alpha > 0 ? 'bg-gradient-to-br from-red-500 to-rose-600 shadow-red-500/30' : 'bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-emerald-500/30'"
                                x-tooltip="day.total + ' siswa, ' + day.alpha + ' alpha'"
                                x-text="day.total">
                            </span>
                        </template>
                        {{-- Indikator alpha mini --}}
                        <template x-if="day.alpha > 0 && day.isCurrentMonth">
                            <div class="absolute bottom-1 left-1.5 flex items-center gap-0.5">
                                <span class="inline-flex items-center gap-0.5 px-1 py-0.5 rounded bg-red-50 border border-red-100 text-[8px] font-bold text-red-500">
                                    <i class="fa-solid fa-user-slash"></i>
                                    <span x-text="day.alpha"></span>
                                </span>
                            </div>
                        </template>
                    </a>
                </template>
            </div>
        </div>
        {{-- Legend --}}
        <div class="px-6 py-3 border-t border-slate-100 bg-gradient-to-r from-slate-50/80 to-emerald-50/40 flex items-center gap-4 text-[11px] text-slate-500 flex-wrap">
            <span class="inline-flex items-center gap-1.5 font-semibold">
                <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-sm"></span> Hadir semua
            </span>
            <span class="inline-flex items-center gap-1.5 font-semibold">
                <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-red-500 to-rose-600 shadow-sm"></span> Ada alpha
            </span>
            <span class="ml-auto inline-flex items-center gap-1 text-slate-400">
                <i class="fa-solid fa-hand-pointer text-[10px]"></i> Klik kotak untuk input presensi
            </span>
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

            get monthTotal() {
                return Object.values(this.attendanceData || {}).reduce((a, b) => a + (b.total || 0), 0);
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

            currentMonth() {
                const now = new Date();
                this.year = now.getFullYear();
                this.month = now.getMonth() + 1;
                this.fetchAndRender();
            },

            fetchAndRender() {
                fetch('{{ route('attendances.calendar-data') }}?year=' + this.year + '&month=' + this.month)
                    .then(r => r.json())
                    .then(data => {
                        this.attendanceData = data;
                        this.render();
                    })
                    .catch(err => console.error('Calendar fetch error:', err));
            }
        };
    }
</script>
@endpush
