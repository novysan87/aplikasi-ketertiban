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
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6">
        <form method="GET" class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Kelas <span class="text-red-500">*</span></label>
                    <select name="class_name" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        <option value="">Pilih kelas...</option>
                        @foreach($classNames as $cn)
                            <option value="{{ $cn }}" @selected($className == $cn)>{{ $cn }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ $date }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm inline-flex items-center justify-center gap-2">
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
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-4">
                <div class="px-6 py-3 flex flex-wrap items-center justify-between gap-3 bg-gradient-to-br from-gray-50 to-gray-100/50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-users text-gray-400"></i>
                        <span class="text-sm font-semibold text-gray-700">{{ $students->count() }} siswa</span>
                        <span class="w-px h-4 bg-gray-200"></span>
                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
                        <span class="w-px h-4 bg-gray-200"></span>
                        <span class="text-xs text-gray-500">Kelas {{ $className }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('attendances.recap', ['class_name' => $className]) }}"
                            class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition inline-flex items-center gap-1">
                            <i class="fa-solid fa-chart-simple"></i> Rekap
                        </a>
                    </div>
                </div>
            </div>

            {{-- Grid table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-50/80 z-10 min-w-[150px]">Siswa</th>
                                <th class="px-1 py-3 text-center text-[9px] font-semibold text-gray-300 uppercase tracking-wider min-w-[80px]">Set</th>
                                @foreach($lessonHours as $lh)
                                    <th class="px-2 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider min-w-[70px]">
                                        Jam ke-{{ $lh }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($students as $s)
                                <tr class="hover:bg-gray-50/50 transition">
                                    {{-- Student name --}}
                                    <td class="px-4 py-2.5 whitespace-nowrap sticky left-0 bg-white z-10">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                <span class="text-[10px] font-bold text-gray-500">{{ strtoupper(substr($s->full_name, 0, 1)) }}</span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-gray-900 truncate max-w-[130px]">{{ $s->full_name }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Per-row bulk buttons --}}
                                    <td class="px-1 py-2.5 text-center whitespace-nowrap">
                                        <div class="flex items-center gap-px">
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'hadir')" title="Semua Hadir"
                                                class="w-5 h-5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-300 hover:bg-emerald-200 transition">H</button>
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'sakit')" title="Semua Sakit"
                                                class="w-5 h-5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 border border-purple-300 hover:bg-purple-200 transition">S</button>
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'izin')" title="Semua Izin"
                                                class="w-5 h-5 rounded text-[9px] font-bold bg-blue-100 text-blue-700 border border-blue-300 hover:bg-blue-200 transition">I</button>
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'alpha')" title="Semua Alpha"
                                                class="w-5 h-5 rounded text-[9px] font-bold bg-red-100 text-red-700 border border-red-300 hover:bg-red-200 transition">A</button>
                                            <button type="button" onclick="studentBulkSet('{{ $s->id }}', 'terlambat')" title="Semua Terlambat"
                                                class="w-5 h-5 rounded text-[9px] font-bold bg-yellow-100 text-yellow-700 border border-yellow-300 hover:bg-yellow-200 transition">T</button>
                                        </div>
                                    </td>

                                    {{-- Lesson hours cells --}}
                                    @foreach($lessonHours as $lh)
                                        @php
                                            $key = $s->id . '-' . $lh;
                                            $currentStatus = isset($existing[$key]) ? $existing[$key]->status : 'hadir';
                                            $statusColors = [
                                                'hadir' => 'bg-emerald-100 border-emerald-300 text-emerald-700',
                                                'sakit' => 'bg-purple-100 border-purple-300 text-purple-700',
                                                'izin' => 'bg-blue-100 border-blue-300 text-blue-700',
                                                'alpha' => 'bg-red-100 border-red-300 text-red-700',
                                                'terlambat' => 'bg-yellow-100 border-yellow-300 text-yellow-700',
                                            ];
                                            $statusLabels = [
                                                'hadir' => 'H',
                                                'sakit' => 'S',
                                                'izin' => 'I',
                                                'alpha' => 'A',
                                                'terlambat' => 'T',
                                            ];
                                            $statusFull = [
                                                'hadir' => 'Hadir',
                                                'sakit' => 'Sakit',
                                                'izin' => 'Izin',
                                                'alpha' => 'Alpha',
                                                'terlambat' => 'Terlambat',
                                            ];
                                            $nextStatus = [
                                                'hadir' => 'sakit',
                                                'sakit' => 'izin',
                                                'izin' => 'alpha',
                                                'alpha' => 'terlambat',
                                                'terlambat' => 'hadir',
                                            ];
                                        @endphp
                                        <td class="px-2 py-2.5 text-center">
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
                                                class="status-btn w-9 h-9 rounded-lg text-xs font-bold border-2 transition-all duration-150 shadow-sm hover:shadow-md hover:scale-105 {{ $statusColors[$currentStatus] }}"
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

            {{-- Submit --}}
            <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-emerald-100 border border-emerald-300 text-[9px] font-bold text-emerald-700 flex items-center justify-center">H</span> Hadir</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-purple-100 border border-purple-300 text-[9px] font-bold text-purple-700 flex items-center justify-center">S</span> Sakit</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-blue-100 border border-blue-300 text-[9px] font-bold text-blue-700 flex items-center justify-center">I</span> Izin</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-red-100 border border-red-300 text-[9px] font-bold text-red-700 flex items-center justify-center">A</span> Alpha</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-yellow-100 border border-yellow-300 text-[9px] font-bold text-yellow-700 flex items-center justify-center">T</span> Terlambat</span>
                    <span class="w-px h-4 bg-gray-200"></span>
                    <span>Klik tombol untuk ganti status</span>
                </div>
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Simpan Presensi
                </button>
            </div>

            {{-- Info --}}
            <div class="mt-4 bg-gradient-to-br from-emerald-50 to-teal-50/50 border border-emerald-100 rounded-2xl p-4">
                <div class="flex items-start gap-3 text-xs text-emerald-700">
                    <i class="fa-solid fa-circle-info mt-0.5"></i>
                    <span>Siswa dengan status <strong>Alpha (A)</strong> minimal 1 jam akan otomatis dicatat sebagai pelanggaran.</span>
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
    'hadir': 'bg-emerald-100 border-emerald-300 text-emerald-700',
    'sakit': 'bg-purple-100 border-purple-300 text-purple-700',
    'izin': 'bg-blue-100 border-blue-300 text-blue-700',
    'alpha': 'bg-red-100 border-red-300 text-red-700',
    'terlambat': 'bg-yellow-100 border-yellow-300 text-yellow-700',
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
            btn.className = 'status-btn w-9 h-9 rounded-lg text-xs font-bold border-2 transition-all duration-150 shadow-sm hover:shadow-md hover:scale-105 ' + STATUS_COLORS[status];
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
    btn.className = 'status-btn w-9 h-9 rounded-lg text-xs font-bold border-2 transition-all duration-150 shadow-sm hover:shadow-md hover:scale-105 ' + STATUS_COLORS[next];
    btn.textContent = STATUS_LABELS[next];
    btn.title = STATUS_FULL[next];
}
</script>
@endpush
