@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Data Siswa</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar siswa aktif — sinkron dari Database Kesiswaan</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 font-medium">{{ $students->total() }} siswa</span>
            @if(request()->anyFilled(['search','class_level','class_name','department']))
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full">
                    <i class="fa-solid fa-filter text-xs"></i>
                    {{ $students->total() }} ditemukan
                </span>
            @endif
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6">
        <form method="GET">
            <div class="p-5 space-y-4">
                {{-- Search --}}
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari NISN, NIS, atau Nama siswa..."
                        class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                {{-- Dropdowns: Jurusan → Tingkat Kelas → Kelas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Jurusan</label>
                        <select name="department"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Semua Jurusan</option>
                            @foreach($departments as $code => $name)
                                <option value="{{ $code }}" @selected(request('department') == $code)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Tingkat Kelas</label>
                        <select name="class_level"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Semua Tingkat</option>
                            @foreach($classLevels as $level)
                                <option value="{{ $level }}" @selected(request('class_level') == $level)>Kelas {{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Kelas</label>
                        <select name="class_name" id="filter-class-name"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Semua Kelas</option>
                            @foreach($classNames as $cn)
                                <option value="{{ $cn }}" @selected(request('class_name') == $cn)>{{ $cn }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm inline-flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            Cari
                        </button>
                        @if(request()->anyFilled(['search','class_level','class_name','department']))
                            <a href="{{ route('students.index') }}"
                                class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-xmark"></i>
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Siswa</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">NISN</th>
                        <th class="px-5 py-3.5 text-left hidden sm:table-cell text-xs font-semibold text-gray-400 uppercase tracking-wider">Kelas</th>
                        <th class="px-5 py-3.5 text-left hidden lg:table-cell text-xs font-semibold text-gray-400 uppercase tracking-wider">Jurusan</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Pelanggaran</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Poin</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($students as $s)
                        @php $pts = $s->total_points; @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            {{-- Siswa --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-sm font-bold shadow-sm flex-shrink-0">
                                        {{ strtoupper(substr($s->full_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate max-w-[200px]">{{ $s->full_name }}</p>
                                        <div class="flex flex-wrap items-center gap-1 mt-0.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-mono font-medium bg-gray-100 text-gray-500 rounded-md">{{ $s->nisn ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            {{-- NISN --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-gray-700">{{ $s->nisn ?? '—' }}</span>
                            </td>
                            {{-- Kelas --}}
                            <td class="px-5 py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full border border-blue-200">
                                    <i class="fa-solid fa-building text-[10px]"></i>
                                    {{ $s->class_name ?? '—' }}
                                </span>
                            </td>
                            {{-- Jurusan --}}
                            <td class="px-5 py-4 hidden lg:table-cell">
                                <span class="text-sm text-gray-500">{{ $s->department_code ?? '—' }}</span>
                            </td>
                            {{-- Pelanggaran --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full
                                    {{ ($s->violations_count ?? 0) > 0 ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-gray-50 text-gray-400 border border-gray-200' }}">
                                    <i class="fa-solid {{ ($s->violations_count ?? 0) > 0 ? 'fa-exclamation' : 'fa-check' }} text-[10px]"></i>
                                    {{ $s->violations_count ?? 0 }}x
                                </span>
                            </td>
                            {{-- Total Poin --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($pts >= 100)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold bg-red-50 text-red-700 rounded-full">{{ $pts }}</span>
                                @elseif($pts >= 50)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold bg-yellow-50 text-yellow-700 rounded-full">{{ $pts }}</span>
                                @elseif($pts > 0)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold bg-orange-50 text-orange-600 rounded-full">{{ $pts }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs bg-green-50 text-green-600 rounded-full">0</span>
                                @endif
                            </td>
                            {{-- Aksi --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('students.show', $s->id) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                                    <i class="fa-solid fa-eye"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-users-slash text-gray-300 text-xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Tidak ada siswa ditemukan</p>
                                    <p class="text-xs text-gray-400">Coba ubah filter atau lakukan sinkronisasi data</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($students->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $students->appends(request()->query())->links() }}
            </div>
        @endif

        {{-- Summary --}}
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-400">
            <span>Menampilkan {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} dari {{ $students->total() }} siswa</span>
            <span class="font-medium">{{ $students->total() }} total</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Data kelas untuk filter dependen
    const classOptions = @json($classOptions);

    function filterKelas() {
        const dept = document.querySelector('select[name="department"]').value;
        const level = document.querySelector('select[name="class_level"]').value;
        const kelasSelect = document.getElementById('filter-class-name');
        const currentVal = kelasSelect.value;

        // Filter
        let filtered = classOptions;
        if (dept) filtered = filtered.filter(c => c.dept === dept);
        if (level) filtered = filtered.filter(c => c.level === level);

        // Sort unique names
        const names = [...new Set(filtered.map(c => c.name))].sort();

        // Rebuild options
        kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
        names.forEach(n => {
            const opt = document.createElement('option');
            opt.value = n;
            opt.textContent = n;
            if (n === currentVal) opt.selected = true;
            kelasSelect.appendChild(opt);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const deptSelect = document.querySelector('select[name="department"]');
        const levelSelect = document.querySelector('select[name="class_level"]');

        deptSelect.addEventListener('change', filterKelas);
        levelSelect.addEventListener('change', filterKelas);

        // Apply initial filter if level/dept pre-selected
        if (deptSelect.value || levelSelect.value) filterKelas();
    });
</script>
@endpush
@endsection
