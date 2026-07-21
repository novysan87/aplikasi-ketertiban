@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Siswa</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar siswa aktif — sinkron dari Database Kesiswaan</p>
        </div>
        <div class="flex items-center space-x-2 text-sm">
            <span class="text-gray-500">{{ $students->total() }} siswa</span>
            @if(request()->anyFilled(['search','class_level','class_name','department']))
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-blue-100 text-red-700 rounded-full">
                    {{ $students->total() }} ditemukan
                </span>
            @endif
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <form method="GET">
            <div class="p-5 space-y-4">
                {{-- Baris 1: Search --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Pencarian</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari NISN, NIS, atau Nama siswa..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                </div>

                {{-- Baris 2: Dropdown --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Tingkat Kelas</label>
                        <select name="class_level"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition bg-white">
                            <option value="">Semua Tingkat</option>
                            @foreach($classLevels as $level)
                                <option value="{{ $level }}" @selected(request('class_level') == $level)>Kelas {{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Kelas</label>
                        <select name="class_name"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition bg-white">
                            <option value="">Semua Kelas</option>
                            @foreach($classNames as $cn)
                                <option value="{{ $cn }}" @selected(request('class_name') == $cn)>{{ $cn }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Jurusan</label>
                        <select name="department"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition bg-white">
                            <option value="">Semua Jurusan</option>
                            @foreach($departments as $code => $name)
                                <option value="{{ $code }}" @selected(request('department') == $code)>{{ $name }} ({{ $code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm flex items-center justify-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>Cari</span>
                        </button>
                        @if(request()->anyFilled(['search','class_level','class_name','department']))
                            <a href="{{ route('students.index') }}"
                                class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span>Reset</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NISN</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jurusan</th>
                        <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelanggaran</th>
                        <th class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Poin</th>
                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($students as $s)
                        @php $pts = $s->total_points; @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm font-mono text-gray-700">{{ $s->nisn }}</td>
                            <td class="px-4 py-3.5">
                                <div class="text-sm font-medium text-gray-900">{{ $s->full_name }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-700">{{ $s->class_name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-gray-500">{{ $s->department_code ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-center text-sm text-gray-700">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                    {{ $s->violations_count ?? 0 }}x
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($pts >= 100)
                                    <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">{{ $pts }}</span>
                                @elseif($pts >= 50)
                                    <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">{{ $pts }}</span>
                                @elseif($pts > 0)
                                    <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-700">{{ $pts }}</span>
                                @else
                                    <span class="inline-flex px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('students.show', $s->id) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-red-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM6 10h.01M18 10h.01"/>
                                </svg>
                                <p class="text-sm text-gray-500 mb-1">Tidak ada siswa ditemukan</p>
                                <p class="text-xs text-gray-400">Coba ubah filter pencarian atau lakukan sinkronisasi data</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $students->appends(request()->query())->links() }}
            </div>
        @endif

        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-between text-xs text-gray-500">
            <span>Menampilkan {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} dari {{ $students->total() }} siswa</span>
            <span class="font-medium">{{ $students->total() }} total</span>
        </div>
    </div>
</div>
@endsection
