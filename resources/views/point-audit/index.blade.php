@extends('layouts.app')

@section('title', 'Riwayat Poin')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 shadow-lg">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative px-6 py-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur border border-white/20">
                    <i class="fa-solid fa-clock-rotate-left text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Riwayat Perubahan Poin</h1>
                    <p class="text-sm text-slate-300 mt-1">Jejak audit setiap penambahan &amp; penghapusan poin pelanggaran siswa</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($summary['total']) }}</div>
                <div class="text-xs text-gray-400 uppercase tracking-wider font-medium mt-0.5">Total Catatan</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center"><i class="fa-solid fa-list text-slate-400"></i></div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-emerald-600">{{ number_format($summary['penambahan']) }}</div>
                <div class="text-xs text-gray-400 uppercase tracking-wider font-medium mt-0.5">Penambahan Poin</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center"><i class="fa-solid fa-plus text-emerald-500"></i></div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-rose-600">{{ number_format($summary['pengurangan']) }}</div>
                <div class="text-xs text-gray-400 uppercase tracking-wider font-medium mt-0.5">Penghapusan Poin</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center"><i class="fa-solid fa-minus text-rose-500"></i></div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="sm:col-span-2 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NISN / Nama siswa..."
                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
            </div>
            <div class="relative">
                <i class="fa-solid fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
            </div>
            <div class="relative">
                <i class="fa-solid fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
            </div>
            <div class="relative">
                <i class="fa-solid fa-filter absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <select name="action"
                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition appearance-none">
                    <option value="">Semua Aksi</option>
                    <option value="created" @selected(request('action') === 'created')>Pencatatan (+)</option>
                    <option value="deleted" @selected(request('action') === 'deleted')>Penghapusan (−)</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-4">
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                <i class="fa-solid fa-filter text-xs"></i> Terapkan Filter
            </button>
            @if (request()->anyFilled(['search', 'action', 'date_from', 'date_to']))
                <a href="{{ route('point-audit.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                    <i class="fa-solid fa-rotate-left text-xs"></i> Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Tabel Log --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/70">
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">Waktu</th>
                        <th class="px-4 py-3 font-semibold">Siswa</th>
                        <th class="px-4 py-3 font-semibold">Aksi</th>
                        <th class="px-4 py-3 text-center font-semibold">Δ Poin</th>
                        <th class="px-4 py-3 text-center font-semibold">Sebelum → Sesudah</th>
                        <th class="px-4 py-3 font-semibold">Keterangan</th>
                        <th class="px-4 py-3 font-semibold">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="text-gray-700 font-medium">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $log->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-800">{{ $log->student?->full_name ?? '(siswa terhapus)' }}</div>
                                <div class="text-xs text-gray-400">{{ $log->student?->class?->name }} · {{ $log->student?->nisn }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($log->action === 'created')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                        <i class="fa-solid fa-plus text-[10px]"></i> Pencatatan
                                    </span>
                                @elseif ($log->action === 'deleted')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
                                        <i class="fa-solid fa-trash-can text-[10px]"></i> Penghapusan
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">Penyesuaian</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-lg text-sm font-bold {{ $log->points_delta > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $log->points_delta > 0 ? '+' : '' }}{{ $log->points_delta }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ $log->points_before }} → <span class="font-bold text-gray-800">{{ $log->points_after }}</span>
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <div class="text-gray-700 truncate" title="{{ $log->description }}">{{ $log->description }}</div>
                                @if (! empty($log->metadata['description']))
                                    <div class="text-xs text-gray-400 truncate max-w-[220px]" title="{{ $log->metadata['description'] }}">{{ $log->metadata['description'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-700">{{ $log->actor?->name ?? 'Sistem' }}</div>
                                @if ($log->ip_address)
                                    <div class="text-xs text-gray-400 font-mono">{{ $log->ip_address }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-50 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-clock-rotate-left text-gray-300 text-2xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-500">Belum ada catatan perubahan poin</p>
                                <p class="text-xs text-gray-400 mt-1">Log akan muncul otomatis saat pelanggaran dicatat atau dihapus</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
