@extends('layouts.app')

@section('title', 'Detail Pelanggaran')

@push('styles')
<style>
    .evidence-grid img { transition: transform 0.3s ease; }
    .evidence-grid a:hover img { transform: scale(1.05); }
    .evidence-grid a:hover .evidence-overlay { opacity: 1; }
    .detail-row:last-of-type { border-bottom: none !important; }
    .status-dot { position: absolute; left: 0; top: 4px; }
    .handling-timeline::before {
        content: '';
        position: absolute;
        left: 23px;
        top: 16px;
        bottom: 16px;
        width: 3px;
        background: linear-gradient(to bottom, #f59e0b, #f97316, #fbbf24);
        border-radius: 999px;
        opacity: 0.35;
    }
</style>
@endpush

@section('content')
@php
    $handleColors = [
        'unhandled' => ['from' => '#dc2626', 'to' => '#b91c1c'],
        'in_progress' => ['from' => '#d97706', 'to' => '#b45309'],
        'resolved' => ['from' => '#16a34a', 'to' => '#15803d'],
    ];
    $handleLabels = [
        'unhandled' => 'Belum Ditangani',
        'in_progress' => 'Dalam Proses',
        'resolved' => 'Selesai',
    ];
    $hcolor = $handleColors[$violation->handling_status] ?? ['from' => '#6b7280', 'to' => '#4b5563'];
@endphp
<div>
    {{-- ===== HEADER HERO ===== --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-blue-500 to-sky-400 p-6 sm:p-8 text-white shadow-lg shadow-blue-200/60 mb-6">
        <div class="pointer-events-none absolute -top-20 -right-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-12 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
            style="background-image: radial-gradient(circle at 25% 40%, #fff 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>

        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-2xl bg-white/15 text-2xl sm:text-3xl font-black backdrop-blur-sm ring-1 ring-white/25 shadow-inner">
                    {{ strtoupper(substr($violation->student->full_name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <nav class="flex items-center gap-1.5 text-xs text-white/60 mb-1.5">
                        <a href="{{ route('violations.index') }}" class="hover:text-white transition">Data Pelanggaran</a>
                        <span>/</span>
                        <span class="text-white/80 font-medium truncate max-w-[160px]">Detail Pelanggaran</span>
                    </nav>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight truncate">{{ $violation->student->full_name ?? '-' }}</h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-mono font-semibold bg-white/15 rounded-lg ring-1 ring-white/20 backdrop-blur-sm">{{ $violation->student->nisn ?? '—' }}</span>
                        <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold bg-emerald-400/20 text-emerald-100 rounded-lg ring-1 ring-emerald-300/30">{{ $violation->student->class_name ?? '—' }}</span>
                        @if($violation->student->department_name)
                            <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold bg-purple-400/20 text-purple-100 rounded-lg ring-1 ring-purple-300/30">{{ $violation->student->department_name }}</span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-lg ring-1 backdrop-blur-sm
                            {{ $violation->handling_status === 'resolved' ? 'bg-emerald-400/20 text-emerald-100 ring-emerald-300/30' : ($violation->handling_status === 'in_progress' ? 'bg-yellow-400/20 text-yellow-100 ring-yellow-300/30' : 'bg-red-400/20 text-red-100 ring-red-300/30') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $violation->handling_status === 'resolved' ? 'bg-emerald-300' : ($violation->handling_status === 'in_progress' ? 'bg-yellow-300' : 'bg-red-300') }}"></span>
                            {{ $handleLabels[$violation->handling_status] ?? ucfirst($violation->handling_status ?? 'unhandled') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if(!$violation->is_verified)
                    <form action="{{ route('violations.verify', $violation->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-bold text-white bg-emerald-500 rounded-xl hover:bg-emerald-600 transition shadow-md shadow-emerald-900/20 active:scale-95">
                            <i class="fa-solid fa-check-circle text-xs"></i>
                            Verifikasi
                        </button>
                    </form>
                @endif
                <form action="{{ route('violations.destroy', $violation->id) }}" method="POST" x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus pelanggaran ini?'})) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-sm font-semibold text-white bg-red-500 ring-1 ring-red-400/40 rounded-xl hover:bg-red-600 active:scale-95 transition shadow-lg shadow-red-900/30">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span class="hidden sm:inline">Hapus</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== NOTIFIKASI ORANG TUA/WALI ===== --}}
    @php
        $lastNotif = $violation->notifications()->latest('id')->first();
    @endphp
    <div class="mt-6 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-100">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-400 flex items-center justify-center text-white shadow-sm">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Notifikasi Orang Tua/Wali</h3>
                    <p class="text-[11px] text-gray-400">Kabarkan pelanggaran ini via WhatsApp — pesan otomatis terisi</p>
                </div>
            </div>
            @if ($lastNotif)
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200">
                    <i class="fa-solid fa-circle-check"></i> Sudah dikirim {{ $lastNotif->created_at->format('d M Y H:i') }} oleh {{ $lastNotif->user?->name ?? '-' }}
                </span>
            @endif
        </div>
        <div class="p-5 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gray-50 border border-slate-200 flex items-center justify-center text-gray-400">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium">No. HP Orang Tua/Wali</p>
                    @if ($violation->student?->parent_phone)
                        <p class="text-sm font-bold text-gray-800">{{ $violation->student->parent_phone }}</p>
                    @else
                        <p class="text-sm font-bold text-amber-600">Belum diisi</p>
                    @endif
                </div>
            </div>
            @if ($violation->student?->parent_phone)
                <a href="{{ route('violations.notify-wa', $violation->id) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 px-5 py-3 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-green-500 rounded-2xl hover:from-emerald-600 hover:to-green-600 active:scale-95 transition shadow-lg shadow-emerald-500/25">
                    <i class="fa-brands fa-whatsapp text-base"></i> {{ $lastNotif ? 'Kirim Ulang' : 'Kirim WA ke Orang Tua' }}
                </a>
            @else
                <a href="{{ route('students.show', $violation->student_id) }}"
                    class="inline-flex items-center gap-2 px-5 py-3 text-sm font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-2xl hover:bg-amber-100 transition">
                    <i class="fa-solid fa-pen"></i> Isi HP Orang Tua
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- ===== KOLOM KIRI ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Stat Row --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {{-- Total Poin --}}
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 p-4 shadow-md shadow-orange-200/50">
                    <div class="absolute right-0 top-0 w-16 h-16 opacity-20">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Total Poin</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $violation->student->total_points }}</p>
                    <p class="text-[10px] text-white/50 mt-0.5">akumulasi siswa</p>
                </div>
                {{-- Poin Pelanggaran --}}
                <div class="relative overflow-hidden rounded-2xl p-4 shadow-md"
                    style="background: linear-gradient(135deg, {{ $violation->points >= 50 ? '#dc2626' : ($violation->points >= 15 ? '#d97706' : '#2563eb') }}, {{ $violation->points >= 50 ? '#b91c1c' : ($violation->points >= 15 ? '#b45309' : '#1d4ed8') }});">
                    <div class="absolute right-0 top-0 w-16 h-16 opacity-10">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Poin Pelanggaran</p>
                    <p class="text-2xl font-black text-white mt-1">+{{ $violation->points }}</p>
                    <p class="text-[10px] text-white/50 mt-0.5">pelanggaran ini</p>
                </div>
                {{-- Kategori --}}
                <div class="relative overflow-hidden rounded-2xl p-4 shadow-md"
                    style="background: linear-gradient(135deg, {{ $violation->violationType?->category?->color ?? '#6b7280' }}, {{ $violation->violationType?->category?->color ?? '#6b7280' }}dd);">
                    <div class="absolute right-0 top-0 w-16 h-16 opacity-10">
                        <svg class="w-full h-full text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Kategori</p>
                    <p class="text-base font-black text-white mt-1 truncate">{{ $violation->violationType?->category?->name ?? '-' }}</p>
                    <p class="text-[10px] text-white/50 mt-0.5">tingkat pelanggaran</p>
                </div>
                {{-- Status Penanganan --}}
                <div class="relative overflow-hidden rounded-2xl p-4 shadow-md"
                    style="background: linear-gradient(135deg, {{ $hcolor['from'] }}, {{ $hcolor['to'] }});">
                    <div class="absolute right-0 top-0 w-16 h-16 opacity-10">
                        <i class="fa-solid fa-hand-holding-heart text-white text-4xl"></i>
                    </div>
                    <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Penanganan</p>
                    <p class="text-base font-black text-white mt-1">{{ $handleLabels[$violation->handling_status] ?? '-' }}</p>
                    <p class="text-[10px] text-white/50 mt-0.5">
                        {{ $violation->handlings->count() }} catatan
                        @if($violation->handled_at)
                            • {{ $violation->handled_at->format('d/m') }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- ===== PENANGANAN (fitur utama) ===== --}}
            <div x-data="{ showHandlingModal: false }" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                {{-- Header kartu --}}
                <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600 px-6 py-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-white/15 flex items-center justify-center ring-1 ring-white/25 backdrop-blur-sm">
                            <i class="fa-solid fa-hand-holding-heart text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white">Riwayat Penanganan</h3>
                            <p class="text-xs text-white/75">Alur tindak lanjut pelanggaran siswa</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($violation->handlings->count() > 0 && $violation->isInProgress())
                            <form action="{{ route('violations.resolve', $violation->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-emerald-700 bg-white rounded-xl hover:bg-emerald-50 transition shadow-sm active:scale-95">
                                    <i class="fa-solid fa-check-circle text-xs"></i>
                                    Tandai Selesai
                                </button>
                            </form>
                        @endif
                        <button @click="showHandlingModal = true"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-orange-700 bg-white rounded-xl hover:bg-orange-50 transition shadow-md shadow-orange-900/20 active:scale-95">
                            <i class="fa-solid fa-plus text-xs"></i>
                            Tambah Penanganan
                        </button>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="p-5 sm:p-6">
                    @forelse($violation->handlings as $h)
                        @php
                            $ht = $handlingTypeMap[$h->handling_type] ?? null;
                            $htColor = $ht->color ?? '#f59e0b';
                            $htIcon = $ht->icon ?? 'fa-clipboard-list';
                        @endphp
                        <div class="relative pl-16 pb-6 {{ !$loop->last ? 'border-l-2 border-dashed border-orange-200 ml-[23px]' : '' }}">
                            {{-- Dot ikon --}}
                            <div class="absolute left-0 top-0 w-[46px]">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-md ring-4 ring-orange-50"
                                    style="background: linear-gradient(135deg, {{ $htColor }}, {{ $htColor }}bb);">
                                    <i class="fa-solid {{ $htIcon }} text-white text-base"></i>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-gray-50/80 to-gray-100/40 rounded-2xl border border-gray-100 p-4 hover:shadow-md transition-shadow duration-200">
                                {{-- Header --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-black text-gray-900">{{ $h->handling_type }}</p>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-md"
                                                style="background-color: {{ $htColor }}18; color: {{ $htColor }}">
                                                <i class="fa-regular fa-calendar"></i>
                                                {{ $h->handling_date->format('d M Y') }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-gray-400 mt-1">
                                            @if($h->location)<i class="fa-solid fa-location-dot mr-1 text-orange-400"></i>{{ $h->location }}@endif
                                            @if($h->location && $h->creator) <span class="text-gray-300">•</span> @endif
                                            @if($h->creator)oleh <span class="font-semibold text-gray-500">{{ $h->creator->name }}</span>@endif
                                        </p>
                                    </div>
                                    <form action="{{ route('violations.handling.destroy', [$violation->id, $h->id]) }}" method="POST"
                                        x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus catatan penanganan ini?'})) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition flex-shrink-0">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>

                                {{-- Deskripsi --}}
                                @if($h->description)
                                    <div class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5">
                                        <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $h->description }}</p>
                                    </div>
                                @endif

                                {{-- Peserta --}}
                                @if($h->participants->count() > 0)
                                    <div class="mt-3">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Yang Menangani</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($h->participants as $p)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] bg-white border border-slate-200 rounded-full shadow-sm">
                                                    <span class="w-4 h-4 rounded-full bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center flex-shrink-0">
                                                        <span class="text-[7px] font-bold text-white">{{ strtoupper(substr($p->user->name ?? '?', 0, 1)) }}</span>
                                                    </span>
                                                    <span class="font-semibold text-gray-700">{{ $p->user->name ?? '-' }}</span>
                                                    @if($p->role)
                                                        <span class="text-[10px] text-gray-500 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">{{ $p->role }}</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Footer row --}}
                                <div class="mt-3 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        @if($h->evidence)
                                            <a href="{{ \Storage::url($h->evidence) }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 text-[11px] font-bold text-blue-600 bg-blue-50/80 px-3 py-1.5 rounded-lg border border-blue-100 hover:bg-blue-100 transition shadow-sm">
                                                <i class="fa-solid fa-paperclip text-[10px]"></i>
                                                Lihat Bukti
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-blue-400"></i>
                                            </a>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-gray-300">
                                        @if($h->created_at != $h->updated_at)
                                            Diubah {{ $h->updated_at->diffForHumans() }}
                                        @else
                                            {{ $h->created_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <div class="relative w-24 h-24 mx-auto mb-4">
                                <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 rotate-6"></div>
                                <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-amber-200 to-orange-200 -rotate-3"></div>
                                <div class="absolute inset-2 rounded-2xl bg-white flex items-center justify-center">
                                    <i class="fa-solid fa-hand-holding-heart text-3xl text-amber-400"></i>
                                </div>
                            </div>
                            <p class="text-base font-bold text-gray-700">Belum Ada Penanganan</p>
                            <p class="text-sm text-gray-400 mt-1">Mulai tindak lanjut pelanggaran ini dengan menekan tombol di atas</p>
                            <button @click="showHandlingModal = true"
                                class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl shadow-md shadow-orange-200 hover:-translate-y-0.5 transition-all active:scale-95">
                                <i class="fa-solid fa-plus"></i> Tambah Penanganan Pertama
                            </button>
                        </div>
                    @endforelse
                </div>

                {{-- Modal Tambah Penanganan --}}
                <script>
                    window.handlingTypeOptions = @json($handlingTypes->map(fn($t) => ['name' => $t->name, 'icon' => $t->icon, 'color' => $t->color]));
                </script>
                <div x-show="showHandlingModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4 py-6">
                        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
                        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl mx-4 max-h-[92vh] overflow-y-auto">
                            {{-- Header hero --}}
                            <div class="relative overflow-hidden bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600 px-7 py-6 sticky top-0 z-10">
                                <div class="pointer-events-none absolute -top-10 -right-10 w-44 h-44 rounded-full bg-white/10 blur-2xl"></div>
                                <div class="pointer-events-none absolute -bottom-14 -left-8 w-40 h-40 rounded-full bg-orange-300/20 blur-3xl"></div>
                                <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
                                    style="background-image: radial-gradient(circle at 25% 40%, #fff 1.5px, transparent 1.5px); background-size: 20px 20px;"></div>
                                <div class="relative z-10 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center ring-1 ring-white/25 backdrop-blur-sm">
                                            <i class="fa-solid fa-hand-holding-heart text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-black text-white">Tambah Penanganan</h3>
                                            <p class="text-xs text-white/75">{{ $violation->student->full_name ?? '-' }} • {{ $violation->student->class_name ?? '' }}</p>
                                        </div>
                                    </div>
                                    <button @click="showHandlingModal = false"
                                        class="w-9 h-9 rounded-xl bg-white/10 ring-1 ring-white/25 flex items-center justify-center text-white hover:bg-white/25 transition flex-shrink-0">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <form action="{{ route('violations.handling.store', $violation->id) }}" method="POST" enctype="multipart/form-data" class="p-7"
                                x-data="{
                                    participants: [],
                                    selectedType: '',
                                    evidenceName: '',
                                    handlingTypes: window.handlingTypeOptions || [],
                                    get selectedMeta() {
                                        return this.handlingTypes.find(t => t.name === this.selectedType) || null;
                                    }
                                }">
                                @csrf

                                <div class="space-y-6">
                                    {{-- Jenis Penanganan --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Penanganan <span class="text-red-500">*</span></label>
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-md ring-4 ring-amber-50 shrink-0 transition-all duration-300"
                                                :style="selectedMeta ? 'background: linear-gradient(135deg, ' + selectedMeta.color + ', ' + selectedMeta.color + 'bb);' : 'background: linear-gradient(135deg, #d1d5db, #9ca3af);'">
                                                <i :class="'fa-solid ' + (selectedMeta ? selectedMeta.icon : 'fa-hand-holding-heart')" class="text-white text-lg"></i>
                                            </div>
                                            <select name="handling_type" x-model="selectedType" required
                                                class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-2xl text-sm font-semibold bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/15 focus:border-amber-500 transition">
                                                <option value="">— Pilih jenis penanganan —</option>
                                                @foreach($handlingTypes as $htOpt)
                                                    <option value="{{ $htOpt->name }}">{{ $htOpt->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <p class="mt-1.5 text-[11px] text-gray-400">Ikon & warna menyesuaikan jenis yang dipilih</p>
                                    </div>

                                    {{-- Tanggal + Lokasi --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Penanganan <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <i class="fa-solid fa-calendar-day absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                                <input type="date" name="handling_date" value="{{ date('Y-m-d') }}" required
                                                    class="w-full pl-11 pr-4 py-3 border-2 border-slate-200 rounded-2xl text-sm font-semibold bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/15 focus:border-amber-500 transition">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Lokasi</label>
                                            <div class="relative">
                                                <i class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                                <input type="text" name="location" placeholder="Ruang BK, Ruang Guru, dll"
                                                    class="w-full pl-11 pr-4 py-3 border-2 border-slate-200 rounded-2xl text-sm font-semibold bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/15 focus:border-amber-500 transition">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Deskripsi --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Catatan / Deskripsi</label>
                                        <textarea name="description" rows="4" placeholder="Tuliskan detail penanganan yang dilakukan..."
                                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-2xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/15 focus:border-amber-500 transition resize-none"></textarea>
                                    </div>

                                    {{-- Bukti --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Bukti Pendukung <span class="font-normal text-gray-400">(opsional)</span></label>
                                        <label class="flex flex-col items-center justify-center gap-2 px-6 py-7 rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50/50 cursor-pointer hover:border-amber-400 hover:bg-amber-50/40 transition group">
                                            <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center group-hover:scale-110 transition-transform">
                                                <i class="fa-solid fa-cloud-arrow-up text-amber-500 text-lg"></i>
                                            </div>
                                            <p class="text-sm font-bold text-gray-600" x-text="evidenceName || 'Klik untuk unggah bukti'"></p>
                                            <p class="text-[11px] text-gray-400">JPG, PNG, PDF, DOC — maks 10 MB</p>
                                            <input type="file" name="evidence" accept="image/*,.pdf,.doc,.docx" class="hidden"
                                                @change="evidenceName = $event.target.files[0]?.name || ''">
                                        </label>
                                    </div>

                                    {{-- Participants --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-bold text-gray-700">Yang Menangani</label>
                                            <button type="button" @click="participants.push({user_id: '', role: ''})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 transition">
                                                <i class="fa-solid fa-plus"></i> Tambah Penanggungjawab
                                            </button>
                                        </div>
                                        <div class="space-y-2.5">
                                            <template x-for="(p, i) in participants" :key="i">
                                                <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-gray-50/50 p-2.5">
                                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center text-white shrink-0">
                                                        <i class="fa-solid fa-user text-xs"></i>
                                                    </div>
                                                    <select :name="'participants['+i+'][user_id]'" x-model="p.user_id"
                                                        class="flex-1 px-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                                                        <option value="">Pilih petugas...</option>
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                                        @endforeach
                                                    </select>
                                                    <select :name="'participants['+i+'][role]'" x-model="p.role"
                                                        class="w-[130px] px-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                                                        <option value="">Peran</option>
                                                        @foreach($handlingRoles as $roleOpt)
                                                            <option value="{{ $roleOpt }}">{{ $roleOpt }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" @click="participants.splice(i, 1)"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition shrink-0">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                    </button>
                                                </div>
                                            </template>
                                            <p x-show="participants.length === 0" class="text-[11px] text-gray-400 pl-1">
                                                Belum ada — tambahkan petugas yang menangani (opsional)
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-gray-100">
                                    <button type="button" @click="showHandlingModal = false"
                                        class="px-6 py-3 text-sm font-bold text-gray-600 bg-white border-2 border-slate-200 rounded-2xl hover:bg-gray-50 transition">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-7 py-3 text-sm font-black text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl shadow-lg shadow-orange-200 hover:-translate-y-0.5 hover:brightness-105 transition-all inline-flex items-center gap-2 active:scale-95">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Penanganan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Detail Info Card --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-gavel text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Informasi Pelanggaran</h3>
                            <p class="text-xs text-gray-400">Detail lengkap pelanggaran yang tercatat</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full"
                        style="background-color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}15; color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $violation->violationType?->category?->color ?? '#6b7280' }}"></span>
                        {{ $violation->violationType?->category?->name ?? '-' }}
                    </span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-gavel text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Jenis Pelanggaran</span>
                            </div>
                            <p class="text-sm font-bold text-gray-900">{{ $violation->violationType->name ?? '-' }}</p>
                        </div>

                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $violation->points >= 50 ? 'bg-red-50' : ($violation->points >= 15 ? 'bg-yellow-50' : 'bg-blue-50') }}">
                                    <i class="fa-solid fa-star {{ $violation->points >= 50 ? 'text-red-500' : ($violation->points >= 15 ? 'text-yellow-500' : 'text-blue-500') }} text-xs"></i>
                                </div>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Poin Pelanggaran</span>
                            </div>
                            <p class="text-sm font-black {{ $violation->points >= 50 ? 'text-red-600' : ($violation->points >= 15 ? 'text-yellow-600' : 'text-blue-600') }}">
                                +{{ $violation->points }}
                            </p>
                        </div>

                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-calendar text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Tanggal</span>
                            </div>
                            <p class="text-sm font-bold text-gray-900">
                                {{ $violation->violation_date->format('d M Y') }}
                                @if($violation->violation_time)
                                    <span class="text-gray-400">• {{ \Carbon\Carbon::parse($violation->violation_time)->format('H:i') }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-scale-balanced text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Sanksi</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">{{ $violation->sanction ?? '—' }}</p>
                        </div>

                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-location-dot text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Lokasi</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">{{ $violation->location ?? '—' }}</p>
                        </div>

                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <i class="fa-solid fa-user-pen text-blue-500 text-xs"></i>
                                </div>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Dicatat Oleh</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($violation->recorder)
                                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-[10px] font-bold text-gray-500">{{ strtoupper(substr($violation->recorder->name, 0, 1)) }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $violation->recorder->name }}</p>
                                @else
                                    <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fa-solid fa-rotate text-[10px] text-blue-500"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-blue-600">E-Jurnal (Sinkron Otomatis)</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Deskripsi --}}
            @if($violation->description)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-note-sticky text-white text-sm"></i>
                        </div>
                        <h3 class="text-sm font-black text-gray-900">Catatan / Deskripsi</h3>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5">
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $violation->description }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ===== KOLOM KANAN ===== --}}
        <div class="space-y-5">

            {{-- Status Card --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-circle-info text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Status</h3>
                            <p class="text-xs text-gray-400">Riwayat siklus pelanggaran</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="mb-5 p-4 rounded-2xl {{ $violation->is_verified ? 'bg-gradient-to-br from-green-50 to-emerald-50/50 border border-green-200' : 'bg-gradient-to-br from-yellow-50 to-amber-50/50 border border-yellow-200' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $violation->is_verified ? 'bg-green-100' : 'bg-yellow-100' }} flex items-center justify-center flex-shrink-0">
                                @if($violation->is_verified)
                                    <i class="fa-solid fa-check-circle text-green-600 text-lg"></i>
                                @else
                                    <i class="fa-solid fa-clock text-yellow-600 text-lg"></i>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-black {{ $violation->is_verified ? 'text-green-800' : 'text-yellow-800' }}">
                                    {{ $violation->is_verified ? 'Terverifikasi' : 'Menunggu Verifikasi' }}
                                </p>
                                @if($violation->is_verified)
                                    <p class="text-xs text-green-600 mt-0.5">
                                        oleh <span class="font-semibold">{{ $violation->verifier->name ?? '-' }}</span>
                                        @if($violation->verified_at)
                                            <span class="text-green-400">• {{ $violation->verified_at->format('d M Y H:i') }}</span>
                                        @endif
                                    </p>
                                @else
                                    <p class="text-xs text-yellow-600 mt-0.5">Belum diverifikasi oleh petugas BK</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute left-[15px] top-3 bottom-3 w-0.5 bg-gray-200"></div>
                        <div class="space-y-5">
                            <div class="relative pl-9">
                                <div class="absolute left-0 top-[3px] w-[30px] flex justify-center">
                                    <div class="w-[13px] h-[13px] rounded-full border-[3px] flex items-center justify-center"
                                        style="border-color: {{ $violation->is_verified ? '#22c55e' : '#eab308' }}; background: white;">
                                        <span class="w-[5px] h-[5px] rounded-full" style="background-color: {{ $violation->is_verified ? '#22c55e' : '#eab308' }}"></span>
                                    </div>
                                </div>
                                <p class="text-sm font-bold {{ $violation->is_verified ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ $violation->is_verified ? 'Diverifikasi' : 'Menunggu Verifikasi' }}
                                </p>
                                @if($violation->is_verified)
                                    <p class="text-xs text-gray-400">{{ $violation->verified_at ? $violation->verified_at->format('d M Y H:i') : '' }}</p>
                                @endif
                            </div>

                            <div class="relative pl-9">
                                <div class="absolute left-0 top-[3px] w-[30px] flex justify-center">
                                    <div class="w-[13px] h-[13px] rounded-full border-[3px] border-blue-500 bg-white"></div>
                                </div>
                                <p class="text-sm font-bold text-gray-800">Dibuat</p>
                                <p class="text-xs text-gray-400">{{ $violation->created_at->format('d M Y H:i') }}</p>
                            </div>

                            @if($violation->updated_at != $violation->created_at)
                            <div class="relative pl-9">
                                <div class="absolute left-0 top-[3px] w-[30px] flex justify-center">
                                    <div class="w-[13px] h-[13px] rounded-full border-[3px] border-gray-300 bg-white"></div>
                                </div>
                                <p class="text-sm font-bold text-gray-500">Terakhir Diubah</p>
                                <p class="text-xs text-gray-400">{{ $violation->updated_at->format('d M Y H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Evidence Photos --}}
            @if($violation->evidences->count() > 0)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-camera text-white text-sm"></i>
                        </div>
                        <h3 class="text-sm font-black text-gray-900 truncate">Bukti Foto</h3>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-bold bg-gray-100 text-gray-500 rounded-full whitespace-nowrap flex-shrink-0">
                        <i class="fa-solid fa-image mr-1"></i>
                        {{ $violation->evidences->count() }}
                    </span>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="evidence-grid grid grid-cols-2 gap-2.5">
                        @foreach($violation->evidences as $evidence)
                            <a href="{{ $evidence->url }}" target="_blank" rel="noopener noreferrer"
                                class="group relative block aspect-square rounded-xl overflow-hidden border border-slate-200 bg-gray-50 shadow-sm">
                                <img src="{{ $evidence->url }}"
                                    class="w-full h-full object-cover"
                                    alt="{{ $evidence->original_name }}">
                                <div class="evidence-overlay absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <span class="inline-flex items-center gap-1 text-white text-[11px] font-medium bg-black/50 px-3 py-1.5 rounded-lg backdrop-blur-sm">
                                        <i class="fa-solid fa-expand"></i>
                                        Perbesar
                                    </span>
                                </div>
                                @if($loop->first && $violation->evidences->count() > 1)
                                    <span class="absolute top-2 left-2 text-[10px] font-medium text-white bg-black/40 px-2 py-0.5 rounded-md backdrop-blur-sm">{{ $loop->iteration }} of {{ $violation->evidences->count() }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Link to Student Profile --}}
            <a href="{{ route('students.show', $violation->student_id) }}"
                class="flex items-center justify-between gap-3 p-4 bg-blue-50/60 border border-blue-100 rounded-3xl hover:bg-blue-50 transition group">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-2xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-user-graduate text-blue-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-blue-700">Lihat Profil Siswa</p>
                        <p class="text-[11px] text-blue-500 mt-0.5 truncate">{{ $violation->student->full_name ?? '' }}</p>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-right text-blue-400 group-hover:text-blue-600 transition flex-shrink-0"></i>
            </a>

            {{-- Back to List --}}
            <a href="{{ route('violations.index') }}"
                class="flex items-center justify-center gap-2 p-3.5 text-sm font-semibold text-gray-500 bg-white border border-slate-200 rounded-3xl hover:bg-gray-50 hover:text-gray-700 transition group">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Kembali ke Data Pelanggaran
            </a>
        </div>
    </div>
</div>
@endsection
