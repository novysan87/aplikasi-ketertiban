@extends('layouts.app')

@section('title', 'Input Presensi')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-400 mb-1">
                <a href="{{ route('attendances.index') }}" class="hover:text-gray-600 transition">Presensi</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-medium">Input</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Input Presensi Per Kelas</h1>
            <p class="text-sm text-gray-500 mt-1">Pilih kelas, tanggal, dan isi kehadiran per jam pelajaran</p>
        </div>
    </div>

    {{-- Pilih Kelas + Tanggal --}}
    <div class="rounded-2xl bg-gradient-to-br from-white to-gray-50/80 border border-gray-200 shadow-sm mb-6 transition-all duration-200 hover:shadow-md">
        <form method="GET" class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                        <i class="fa-solid fa-school text-gray-400 mr-1"></i> Kelas <span class="text-red-500">*</span>
                    </label>
                    <select name="class_name" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm">
                        <option value="">Pilih kelas...</option>
                        @foreach($classNames as $cn)
                            @php
                                $stat = $classAttendanceStatus[$cn] ?? ['has_data' => false, 'recorded' => 0, 'total' => 0];
                                $label = $stat['has_data']
                                    ? '✅ ' . $cn . ' (' . $stat['recorded'] . '/' . $stat['total'] . ')'
                                    : '⬜ ' . $cn;
                            @endphp
                            <option value="{{ $cn }}" @selected($className == $cn)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="mt-1.5 flex items-center gap-3 text-[10px] text-gray-400">
                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-gray-300"></span> Belum</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-emerald-400"></span> Terisi</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                        <i class="fa-solid fa-calendar text-gray-400 mr-1"></i> Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" value="{{ $date }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm">
                </div>
                <div class="flex items-center pt-[22px]">
                    <button type="submit"
                        class="w-full px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                        Lanjutkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($errors->any() && $students->count() > 0)
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-red-700">Gagal menyimpan presensi</p>
                    <ul class="mt-1 text-xs text-red-600 list-disc list-inside">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if($students->count() > 0)
        <form action="{{ route('attendances.store') }}" method="POST" x-data="{ saved: false }">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="class_name" value="{{ $className }}">
            <input type="hidden" name="auto_violation" value="1">

            {{-- Info bar --}}
            <div class="rounded-2xl bg-gradient-to-br from-slate-50 to-white border border-gray-200 shadow-sm overflow-hidden mb-5 transition-all duration-200 hover:shadow-md">
                <div class="px-5 py-3.5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-users text-white text-xs"></i>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-gray-800">{{ $students->count() }} siswa</span>
                            <span class="mx-2 text-gray-300">•</span>
                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
                            <span class="mx-2 text-gray-300">•</span>
                            <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg">{{ $className }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('attendances.recap', ['class_name' => $className]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-violet-600 bg-violet-50 border border-violet-200 rounded-lg hover:bg-violet-100 transition">
                            <i class="fa-solid fa-chart-simple"></i> Rekap
                        </a>
                    </div>
                </div>
            </div>

            {{-- Grid table --}}
            <div class="rounded-2xl bg-white border border-gray-200 shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-white">
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest sticky left-0 bg-gradient-to-r from-gray-50 to-white z-10 min-w-[160px]">
                                    <i class="fa-solid fa-user text-gray-300 mr-1.5"></i> Siswa
                                </th>
                                <th class="px-1 py-3.5 text-center text-[9px] font-bold text-gray-300 uppercase tracking-wider min-w-[90px]">
                                    <div class="inline-flex items-center gap-0.5 bg-gray-100/80 px-2 py-1 rounded-lg">
                                        <span class="text-gray-400">Set</span>
                                        <i class="fa-solid fa-chevron-down text-[8px] text-gray-300"></i>
                                    </div>
                                </th>
                                @foreach($lessonHours as $lh)
                                    <th class="px-2 py-3.5 text-center min-w-[76px]">
                                        <div class="inline-flex flex-col items-center">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jam</span>
                                            <span class="text-sm font-black text-gray-700 -mt-0.5">{{ $lh }}</span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($students as $s)
                                <tr class="hover:bg-gradient-to-r hover:from-blue-50/30 hover:to-white transition-all duration-100 group">
                                    {{-- Student name --}}
                                    <td class="px-4 py-2.5 whitespace-nowrap sticky left-0 bg-white group-hover:bg-gradient-to-r group-hover:from-blue-50/30 group-hover:to-white z-10 transition-all duration-100">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:from-blue-100 group-hover:to-blue-200 transition-all duration-200">
                                                <span class="text-[11px] font-bold text-gray-500 group-hover:text-blue-600 transition-colors">{{ strtoupper(substr($s->full_name, 0, 1)) }}</span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-gray-900 truncate max-w-[140px]">{{ $s->full_name }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Per-row bulk buttons --}}
                                    <td class="px-1 py-2.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-0.5 bg-gray-50/80 rounded-lg p-1 border border-gray-100">
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'hadir')" title="Semua Hadir"
                                                class="w-6 h-6 rounded-md text-[9px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-300 hover:bg-emerald-200 hover:scale-110 transition-all duration-150 shadow-sm">H</button>
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'sakit')" title="Semua Sakit"
                                                class="w-6 h-6 rounded-md text-[9px] font-bold bg-purple-100 text-purple-700 border border-purple-300 hover:bg-purple-200 hover:scale-110 transition-all duration-150 shadow-sm">S</button>
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'izin')" title="Semua Izin"
                                                class="w-6 h-6 rounded-md text-[9px] font-bold bg-blue-100 text-blue-700 border border-blue-300 hover:bg-blue-200 hover:scale-110 transition-all duration-150 shadow-sm">I</button>
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'alpha')" title="Semua Alpha"
                                                class="w-6 h-6 rounded-md text-[9px] font-bold bg-red-100 text-red-700 border border-red-300 hover:bg-red-200 hover:scale-110 transition-all duration-150 shadow-sm">A</button>
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'terlambat')" title="Semua Terlambat"
                                                class="w-6 h-6 rounded-md text-[9px] font-bold bg-yellow-100 text-yellow-700 border border-yellow-300 hover:bg-yellow-200 hover:scale-110 transition-all duration-150 shadow-sm">T</button>
                                        </div>
                                    </td>

                                    {{-- Lesson hours cells --}}
                                    @foreach($lessonHours as $lh)
                                        @php
                                            $key = $s->id . '-' . $lh;
                                            $currentStatus = isset($existing[$key]) ? $existing[$key]->status : 'hadir';
                                            $statusColors = [
                                                'hadir' => 'bg-emerald-100 border-emerald-300 text-emerald-700 hover:bg-emerald-200',
                                                'sakit' => 'bg-purple-100 border-purple-300 text-purple-700 hover:bg-purple-200',
                                                'izin' => 'bg-blue-100 border-blue-300 text-blue-700 hover:bg-blue-200',
                                                'alpha' => 'bg-red-100 border-red-300 text-red-700 hover:bg-red-200',
                                                'terlambat' => 'bg-yellow-100 border-yellow-300 text-yellow-700 hover:bg-yellow-200',
                                            ];
                                            $statusLabels = [
                                                'hadir' => 'H', 'sakit' => 'S', 'izin' => 'I',
                                                'alpha' => 'A', 'terlambat' => 'T',
                                            ];
                                            $statusFull = [
                                                'hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin',
                                                'alpha' => 'Alpha', 'terlambat' => 'Terlambat',
                                            ];
                                        @endphp
                                        <td class="px-2 py-2 text-center">
                                            <input type="hidden"
                                                name="attendances[{{ $s->id }}][{{ $lh }}][student_id]"
                                                value="{{ $s->id }}">
                                            <input type="hidden"
                                                name="attendances[{{ $s->id }}][{{ $lh }}][lesson_hour]"
                                                value="{{ $lh }}">
                                            <input type="hidden"
                                                name="attendances[{{ $s->id }}][{{ $lh }}][status]"
                                                id="status-{{ $s->id }}-{{ $lh }}"
                                                value="{{ $currentStatus }}">

                                            <button type="button"
                                                onclick="rotateStatus({{ $s->id }}, {{ $lh }})"
                                                class="status-btn w-10 h-10 rounded-xl text-sm font-black border-2 transition-all duration-150 shadow-sm hover:shadow-md hover:scale-110 active:scale-95 {{ $statusColors[$currentStatus] }}"
                                                id="btn-{{ $s->id }}-{{ $lh }}"
                                                title="{{ $statusFull[$currentStatus] }}">
                                                {{ $statusLabels[$currentStatus] }}
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Bottom actions --}}
            <div class="mt-6 space-y-4">
                {{-- Legend + Submit --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-gradient-to-br from-white to-gray-50/50 border border-gray-200 rounded-2xl p-5 shadow-sm">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-[11px] text-gray-500">
                        @php
                            $legends = [
                                'hadir' => ['emerald', 'H', 'Hadir'],
                                'sakit' => ['purple', 'S', 'Sakit'],
                                'izin' => ['blue', 'I', 'Izin'],
                                'alpha' => ['red', 'A', 'Alpha'],
                                'terlambat' => ['yellow', 'T', 'Terlambat'],
                            ];
                        @endphp
                        @foreach($legends as $key => $lg)
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-md bg-{{ $lg[0] }}-100 border border-{{ $lg[0] }}-300 text-[9px] font-bold text-{{ $lg[0] }}-700 flex items-center justify-center">{{ $lg[1] }}</span>
                                <span>{{ $lg[2] }}</span>
                            </span>
                        @endforeach
                        <span class="w-px h-4 bg-gray-200 hidden sm:inline-block"></span>
                        <span class="text-gray-400"><i class="fa-solid fa-rotate mr-1"></i>Klik untuk ganti status</span>
                    </div>
                    <button type="submit"
                        class="px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        Simpan Presensi
                    </button>
                </div>

                {{-- Info --}}
                <div class="rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50/50 border border-emerald-200 shadow-sm p-4">
                    <div class="flex items-start gap-3 text-xs text-emerald-700">
                        <div class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <i class="fa-solid fa-circle-info text-emerald-500 text-[10px]"></i>
                        </div>
                        <span>Siswa dengan status <strong>Alpha (A)</strong> minimal 1 jam akan otomatis dicatat sebagai pelanggaran. Klik tombol status untuk <strong>rotasi</strong>: H → S → I → A → T → H.</span>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
const STATUS_CYCLE = ['hadir', 'sakit', 'izin', 'alpha', 'terlambat'];
const STATUS_COLORS = {
    'hadir': 'bg-emerald-100 border-emerald-300 text-emerald-700 hover:bg-emerald-200',
    'sakit': 'bg-purple-100 border-purple-300 text-purple-700 hover:bg-purple-200',
    'izin': 'bg-blue-100 border-blue-300 text-blue-700 hover:bg-blue-200',
    'alpha': 'bg-red-100 border-red-300 text-red-700 hover:bg-red-200',
    'terlambat': 'bg-yellow-100 border-yellow-300 text-yellow-700 hover:bg-yellow-200',
};
const STATUS_LABELS = {
    'hadir': 'H', 'sakit': 'S', 'izin': 'I',
    'alpha': 'A', 'terlambat': 'T',
};
const STATUS_FULL = {
    'hadir': 'Hadir', 'sakit': 'Sakit', 'izin': 'Izin',
    'alpha': 'Alpha', 'terlambat': 'Terlambat',
};

function studentBulkSet(studentId, status) {
    for (let lh = 1; lh <= 10; lh++) {
        const hidden = document.getElementById('status-' + studentId + '-' + lh);
        const btn = document.getElementById('btn-' + studentId + '-' + lh);
        if (hidden && btn) {
            hidden.value = status;
            btn.className = 'status-btn w-10 h-10 rounded-xl text-sm font-black border-2 transition-all duration-150 shadow-sm hover:shadow-md hover:scale-110 active:scale-95 ' + STATUS_COLORS[status];
            btn.textContent = STATUS_LABELS[status];
            btn.title = STATUS_FULL[status];
        }
    }
}

function rotateStatus(studentId, lessonHour) {
    const hiddenInput = document.getElementById('status-' + studentId + '-' + lessonHour);
    const btn = document.getElementById('btn-' + studentId + '-' + lessonHour);
    const current = hiddenInput.value;
    const nextIdx = (STATUS_CYCLE.indexOf(current) + 1) % STATUS_CYCLE.length;
    const next = STATUS_CYCLE[nextIdx];

    hiddenInput.value = next;
    btn.className = 'status-btn w-10 h-10 rounded-xl text-sm font-black border-2 transition-all duration-150 shadow-sm hover:shadow-md hover:scale-110 active:scale-95 ' + STATUS_COLORS[next];
    btn.textContent = STATUS_LABELS[next];
    btn.title = STATUS_FULL[next];
}
</script>
@endpush
