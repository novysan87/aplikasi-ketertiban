@extends('layouts.app')

@section('title', 'Wali Kelas')

@section('content')
@php
    $totalClasses = $classes->count();
    $assignedCount = $classes->filter(fn ($c) => $c->homeroom_teacher_id)->count();
    $unassignedCount = $totalClasses - $assignedCount;
@endphp
<div x-data="{ changed: {} }" class="pb-8">
    {{-- ===== HEADER HERO ===== --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-500 to-cyan-400 shadow-xl shadow-emerald-500/20 px-6 py-6 mb-6">
        <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -bottom-20 left-1/3 w-48 h-48 rounded-full bg-cyan-300/20 blur-2xl"></div>
        <div class="absolute top-0 right-24 w-24 h-24 rounded-full border border-white/10"></div>

        <div class="relative flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center shadow-inner ring-1 ring-white/20">
                <i class="fa-solid fa-chalkboard-user text-white text-2xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <nav class="flex items-center gap-1.5 text-xs text-white/60 mb-1">
                    <a href="{{ route('settings.index') }}" class="hover:text-white transition">Pengaturan</a>
                    <span>/</span>
                    <span class="text-white/80 font-medium">Master Data</span>
                </nav>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-white tracking-tight">Wali Kelas</h1>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white/90 tracking-wider uppercase">Penugasan</span>
                </div>
                <p class="text-sm text-white/75 mt-0.5">Tetapkan wali kelas untuk setiap kelas — wali kelas hanya melihat kelas yang diwalikan</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 px-4 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-sm font-semibold text-emerald-700 flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                <i class="fa-solid fa-check text-emerald-500 text-xs"></i>
            </div>
            {{ session('success') }}
        </div>
    @endif

    {{-- ===== STATISTIK ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 p-4">
            <div class="absolute right-0 top-0 w-14 h-14 opacity-10"><i class="fa-solid fa-school text-emerald-600 text-5xl"></i></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Kelas</p>
            <p class="text-2xl font-black text-gray-800 mt-1">{{ $totalClasses }}</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 p-4">
            <div class="absolute right-0 top-0 w-14 h-14 opacity-10"><i class="fa-solid fa-user-check text-emerald-600 text-5xl"></i></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sudah Ada Wali</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $assignedCount }}</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 p-4">
            <div class="absolute right-0 top-0 w-14 h-14 opacity-10"><i class="fa-solid fa-user-slash text-amber-500 text-5xl"></i></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Belum Ada Wali</p>
            <p class="text-2xl font-black text-amber-500 mt-1">{{ $unassignedCount }}</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 p-4">
            <div class="absolute right-0 top-0 w-14 h-14 opacity-10"><i class="fa-solid fa-users text-emerald-600 text-5xl"></i></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Guru Wali Kelas</p>
            <p class="text-2xl font-black text-gray-800 mt-1">{{ $teachers->count() }}</p>
        </div>
    </div>

    @if($teachers->isEmpty())
        <div class="mb-5 px-4 py-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-sm text-amber-700 flex items-center gap-2.5">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Belum ada user dengan peran <strong>Wali Kelas</strong>. Buat dulu di menu <a href="{{ route('users.index') }}" class="font-bold underline">Pengguna</a>.</span>
        </div>
    @endif

    {{-- ===== DAFTAR KELAS ===== --}}
    <div class="bg-white rounded-3xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-building-columns text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Penugasan Wali Kelas</h3>
                    <p class="text-[11px] text-gray-400">Kelas tanpa wali ditandai — wali kelas murni tidak bisa melihat data sebelum ditetapkan</p>
                </div>
            </div>
            <span class="text-[11px] font-semibold text-gray-400">{{ $unassignedCount > 0 ? $unassignedCount . ' kelas belum ada wali' : 'Semua kelas sudah ada wali 🎉' }}</span>
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/70 text-left text-[11px] text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-3 font-bold">Kelas</th>
                        <th class="px-4 py-3 font-bold">Jurusan</th>
                        <th class="px-4 py-3 font-bold">Wali Kelas</th>
                        <th class="px-6 py-3 font-bold text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($classes as $class)
                        <tr class="hover:bg-emerald-50/20 transition {{ $class->homeroom_teacher_id ? '' : 'bg-amber-50/20' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white flex items-center justify-center shadow-sm shrink-0">
                                        <i class="fa-solid fa-users text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ $class->name }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $class->level }} • {{ $class->students()->count() }} siswa</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold bg-gray-100 text-gray-600 rounded-lg border border-gray-200">
                                    {{ $class->department_name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <form action="{{ route('settings.homeroom.update') }}" method="POST" x-data="{ teacherId: {{ $class->homeroom_teacher_id ?? 'null' }} }"
                                    @change="changed[{{ $class->id }}] = true">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="class_id" value="{{ $class->id }}">
                                    <div class="flex items-center gap-2">
                                        <div class="relative flex-1 min-w-[220px]">
                                            <i class="fa-solid fa-chalkboard-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                            <select name="teacher_id" x-model="teacherId"
                                                class="w-full pl-9 pr-8 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition appearance-none shadow-sm">
                                                <option value="">— Belum ada —</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}" @selected($class->homeroom_teacher_id === $t->id)>{{ $t->name }}</option>
                                                @endforeach
                                            </select>
                                            <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none"></i>
                                        </div>
                                    </div>
                                    <div class="mt-2" x-show="changed[{{ $class->id }}]" x-transition style="display: none;">
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl hover:from-emerald-600 hover:to-teal-600 active:scale-95 transition shadow-md shadow-emerald-500/25">
                                            <i class="fa-solid fa-floppy-disk text-[10px]"></i> Simpan
                                        </button>
                                    </div>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                @if($class->homeroomTeacher)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        {{ $class->homeroomTeacher->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                        Belum ada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: kartu --}}
        <div class="md:hidden divide-y divide-gray-50">
            @foreach($classes as $class)
                <div class="p-4 space-y-3 {{ $class->homeroom_teacher_id ? '' : 'bg-amber-50/20' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white flex items-center justify-center shadow-sm shrink-0">
                            <i class="fa-solid fa-users text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900">{{ $class->name }}</p>
                            <p class="text-[11px] text-gray-400">{{ $class->level }} • {{ $class->students()->count() }} siswa • {{ $class->department_name ?? '-' }}</p>
                        </div>
                        @if($class->homeroomTeacher)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full shrink-0">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                Ada wali
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded-full shrink-0">
                                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                Kosong
                            </span>
                        @endif
                    </div>
                    <form action="{{ route('settings.homeroom.update') }}" method="POST" x-data="{ teacherId: {{ $class->homeroom_teacher_id ?? 'null' }} }"
                        @change="changed[{{ $class->id }}] = true">
                        @csrf @method('PUT')
                        <input type="hidden" name="class_id" value="{{ $class->id }}">
                        <div class="relative">
                            <i class="fa-solid fa-chalkboard-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <select name="teacher_id" x-model="teacherId"
                                class="w-full pl-9 pr-8 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition appearance-none shadow-sm">
                                <option value="">— Belum ada —</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" @selected($class->homeroom_teacher_id === $t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none"></i>
                        </div>
                        <div class="mt-2" x-show="changed[{{ $class->id }}]" x-transition style="display: none;">
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl hover:from-emerald-600 hover:to-teal-600 active:scale-95 transition shadow-md shadow-emerald-500/25">
                                <i class="fa-solid fa-floppy-disk text-[10px]"></i> Simpan Wali Kelas
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
