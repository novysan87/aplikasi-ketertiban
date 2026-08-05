@extends('layouts.app')

@section('title', 'Verifikasi Wali Murid')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-teal-700 to-slate-900 shadow-lg">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative px-6 py-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur border border-white/20">
                    <i class="fa-solid fa-user-check text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Verifikasi Akun Wali Murid</h1>
                    <p class="text-sm text-slate-300 mt-1">Akun wali yang mendaftar lewat aplikasi mobile &amp; belum terverifikasi otomatis</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 backdrop-blur border border-white/20 text-white text-sm font-semibold">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    {{ $pending->count() }} menunggu
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-xmark text-red-500"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Antrean pending --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">Antrean Verifikasi</h2>
            <span class="text-xs font-medium text-slate-500">Wali daftar via app → cocokkan data → verifikasi di sini</span>
        </div>

        @if($pending->isEmpty())
        <div class="px-6 py-14 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 flex items-center justify-center mb-3">
                <i class="fa-solid fa-check-double text-2xl text-emerald-500"></i>
            </div>
            <p class="text-gray-700 font-medium">Tidak ada antrean 🎉</p>
            <p class="text-sm text-gray-400 mt-1">Semua permintaan verifikasi sudah diproses.</p>
        </div>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($pending as $link)
            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(substr($link->user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ $link->user->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            NISN <span class="font-mono">{{ $link->student->nisn }}</span> · {{ $link->student->full_name }} · {{ $link->student->class_name }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">Daftar: {{ $link->created_at?->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form method="POST" action="{{ route('parents.verification.approve', $link->id) }}" onsubmit="return confirm('Setujui akun wali ini?')">
                        @csrf
                        <button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition">
                            <i class="fa-solid fa-check mr-1"></i> Setujui
                        </button>
                    </form>
                    <form method="POST" action="{{ route('parents.verification.reject', $link->id) }}" onsubmit="return confirm('Tolak akun wali ini?')">
                        @csrf
                        <button class="px-4 py-2 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-semibold transition">
                            <i class="fa-solid fa-xmark mr-1"></i> Tolak
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Riwayat terakhir --}}
    @if($recent->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-gray-900">Terakhir Diproses</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-5 py-3">Wali</th>
                        <th class="px-5 py-3">Siswa</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Diproses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($recent as $link)
                    <tr>
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $link->user->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $link->student->full_name }} <span class="text-gray-400">({{ $link->student->class_name }})</span></td>
                        <td class="px-5 py-3">
                            @if($link->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold"><i class="fa-solid fa-check"></i> Aktif</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-xs font-semibold"><i class="fa-solid fa-xmark"></i> Ditolak</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $link->verified_at?->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
