@extends('layouts.app')

@section('title', 'Ambang SP')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-1.5">
                <a href="{{ route('settings.index') }}" class="hover:text-blue-600 transition font-medium">Pengaturan</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700 font-semibold">Ambang SP</span>
            </nav>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Ambang Surat Peringatan</h1>
            <p class="text-sm text-slate-500 mt-1">Atur batas poin untuk setiap tingkat Surat Peringatan</p>
        </div>
        <button type="button" x-data @click="window.dispatchEvent(new CustomEvent('open-modal', {detail: 'create-threshold'}))"
            class="btn-primary flex-shrink-0">
            <i class="fa-solid fa-plus text-xs"></i>
            Tambah Ambang SP
        </button>
    </div>

    {{-- Threshold Cards --}}
    @if($thresholds->count() > 0)
        <form action="{{ route('settings.thresholds.update') }}" method="POST" class="space-y-4 mb-6">
            @csrf @method('PUT')

            @foreach($thresholds as $t)
                <input type="hidden" name="thresholds[{{ $loop->index }}][id]" value="{{ $t->id }}">
                <div class="group bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-200 hover:shadow-lg hover:border-slate-300">
                    {{-- Top gradient strip --}}
                    <div class="h-1.5 w-full" style="background: linear-gradient(90deg, {{ $t->color }}, {{ $t->color }}88)"></div>

                    <div class="p-5">
                        {{-- Header --}}
                        <div class="flex items-center justify-between gap-4 mb-5">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md flex-shrink-0"
                                    style="background: linear-gradient(135deg, {{ $t->color }}, {{ $t->color }}cc); color: #fff">
                                    <i class="fa-solid fa-file-lines text-base"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-[15px] font-extrabold tracking-tight" style="color: {{ $t->color }}">{{ $t->name }}</h3>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold"
                                            style="background-color: {{ $t->color }}15; color: {{ $t->color }}">
                                            <i class="fa-solid fa-bolt text-[9px]"></i> ≥ {{ $t->min_points }} poin
                                        </span>
                                        @if(! $t->is_active)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-400">
                                                <i class="fa-solid fa-pause text-[8px]"></i> Nonaktif
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1 truncate max-w-md">{{ $t->default_description ?: 'Tidak ada deskripsi' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                {{-- Toggle aktif --}}
                                <label class="relative inline-flex items-center cursor-pointer p-1.5" title="{{ $t->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                    <input type="checkbox" name="thresholds[{{ $loop->index }}][is_active]" value="1"
                                        {{ $t->is_active ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:border-transparent"
                                        style="{{ $t->is_active ? 'background-color: ' . $t->color : '' }}"></div>
                                </label>

                                {{-- Hapus --}}
                                <form action="{{ route('settings.thresholds.destroy', $t->id) }}" method="POST" id="delete-threshold-{{ $t->id }}">
                                    @csrf @method('DELETE')
                                    <button type="button"
                                        x-data
                                        x-on:click="if(await window.confirmSwal({title:'Hapus {{ $t->name }}?',text:'Yakin ingin menghapus ambang SP ini?',icon:'question',confirmText:'Ya, Hapus!',cancelText:'Batal'})) document.getElementById('delete-threshold-{{ $t->id }}').submit()"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition"
                                        title="Hapus">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Fields --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 border-t border-slate-100">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.12em] mb-1.5">Nama</label>
                                <input type="text" name="thresholds[{{ $loop->index }}][name]" value="{{ $t->name }}"
                                    class="input">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.12em] mb-1.5">Min Poin</label>
                                <div class="relative">
                                    <input type="number" name="thresholds[{{ $loop->index }}][min_points]" value="{{ $t->min_points }}" min="0"
                                        class="input pr-8">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-300 pointer-events-none">poin</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.12em] mb-1.5">Deskripsi</label>
                                <input type="text" name="thresholds[{{ $loop->index }}][default_description]" value="{{ $t->default_description }}" placeholder="Contoh: SP 1 — poin mencapai 50"
                                    class="input">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn-primary w-full justify-center py-3.5">
                <i class="fa-solid fa-floppy-disk text-sm"></i>
                Simpan Semua Perubahan
            </button>
        </form>
    @else
        {{-- Empty state --}}
        <div class="bg-white rounded-2xl shadow-sm border border-dashed border-slate-300 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 border border-blue-100 flex items-center justify-center mx-auto mb-4 shadow-sm">
                <i class="fa-solid fa-chart-simple text-blue-400 text-2xl"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-600 mb-1">Belum Ada Ambang SP</h4>
            <p class="text-xs text-slate-400 mb-5 max-w-sm mx-auto">Tambahkan ambang Surat Peringatan untuk memantau level pelanggaran siswa</p>
            <button type="button" x-data @click="window.dispatchEvent(new CustomEvent('open-modal', {detail: 'create-threshold'}))"
                class="btn-primary">
                <i class="fa-solid fa-plus text-xs"></i>
                Tambah Ambang SP
            </button>
        </div>
    @endif

    {{-- Create Modal --}}
    <div x-data="{ open: false, color: '#8b5cf6' }"
        x-on:open-modal.window="if($event.detail === 'create-threshold') { open = true; color = '#8b5cf6'; }"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
                {{-- Modal header --}}
                <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center shadow-sm backdrop-blur-sm">
                            <i class="fa-solid fa-plus text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Tambah Ambang SP</h3>
                            <p class="text-xs text-blue-100/80">Batas poin Surat Peringatan baru</p>
                        </div>
                    </div>
                    <button @click="open = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-100 hover:text-white hover:bg-white/10 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form action="{{ route('settings.thresholds.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Nama Threshold <span class="text-red-500">*</span></label>
                            <input type="text" name="name" placeholder="SP 4, SP 5, dll" required
                                class="input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Minimal Poin <span class="text-red-500">*</span></label>
                            <input type="number" name="min_points" min="0" value="200" required
                                class="input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Deskripsi</label>
                            <input type="text" name="default_description" placeholder="Contoh: SP 4 — poin mencapai 200"
                                class="input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Warna Identitas</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="color" x-model="color"
                                    class="w-11 h-11 rounded-xl border border-slate-200 cursor-pointer p-1 bg-white shadow-sm">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <template x-for="c in ['#eab308','#f97316','#ef4444','#8b5cf6','#2563eb','#10b981','#ec4899','#06b6d4']" :key="c">
                                        <button type="button" @click="color = c"
                                            class="w-7 h-7 rounded-lg border border-slate-200 transition-transform hover:scale-110"
                                            :style="'background-color:' + c"
                                            :class="color === c ? 'ring-2 ring-offset-2 ring-blue-400 scale-110' : ''"
                                            :title="c"></button>
                                    </template>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-2" x-text="'Warna dipilih: ' + color"></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" @click="open = false"
                            class="btn-outline">
                            Batal
                        </button>
                        <button type="submit"
                            class="btn-primary">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Info Card: tangga SP dinamis --}}
    @if($thresholds->count() > 0)
    <div class="mt-6 bg-gradient-to-br from-blue-50 via-indigo-50/60 to-white border border-blue-100 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center flex-shrink-0 shadow-md shadow-blue-500/25">
                <i class="fa-solid fa-circle-info text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-extrabold text-slate-800 mb-3">Tangga Surat Peringatan</h4>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach($thresholds as $idx => $th)
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-sm"
                                style="background-color: {{ $th->color }}12; color: {{ $th->color }}; {{ $th->is_active ? '' : 'opacity:0.45;' }}">
                                <i class="fa-solid fa-bolt text-[9px]"></i>
                                {{ $th->name }} · ≥{{ $th->min_points }} poin
                                @if(! $th->is_active) <span class="text-[9px] font-bold uppercase">(nonaktif)</span> @endif
                            </span>
                            @if(! $loop->last)
                                <i class="fa-solid fa-arrow-right-long text-slate-300 text-xs"></i>
                            @endif
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-slate-400 mt-3 leading-relaxed">
                    <i class="fa-solid fa-star mr-1 text-amber-400"></i>SP diterbitkan otomatis saat poin siswa mencapai ambang minimal level tertinggi yang belum dimiliki.
                    Toggle untuk menonaktifkan level, atau hapus level yang tidak dipakai.
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
