@extends('layouts.app')

@section('title', 'Ambang SP')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-400 mb-1">
                <a href="{{ route('settings.index') }}" class="hover:text-gray-600 transition">Pengaturan</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-medium">Ambang SP</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Ambang Surat Peringatan</h1>
            <p class="text-sm text-gray-500 mt-1">Atur batas poin untuk setiap tingkat Surat Peringatan</p>
        </div>
        <button type="button" x-data @click="window.dispatchEvent(new CustomEvent('open-modal', {detail: 'create-threshold'}))"
            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
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
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition hover:shadow-md">
                    <div class="flex items-stretch">
                        {{-- Left color bar --}}
                        <div class="w-[5px] flex-shrink-0" style="background-color: {{ $t->color }}"></div>

                        <div class="flex-1 px-5 py-4">
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0"
                                        style="background-color: {{ $t->color }}20; color: {{ $t->color }}">
                                        <i class="fa-solid fa-file-lines text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-bold text-gray-900" style="color: {{ $t->color }}">{{ $t->name }}</h3>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="thresholds[{{ $loop->index }}][is_active]" value="1"
                                                    {{ $t->is_active ? 'checked' : '' }}
                                                    class="sr-only peer">
                                                <div class="w-8 h-4 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all"
                                                    style="{{ $t->is_active ? 'background-color: ' . $t->color : '' }}"></div>
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-0.5">Min {{ $t->min_points }} poin
                                            @if($t->max_points) – Maks {{ $t->max_points }} poin @else + (tak terbatas) @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <form action="{{ route('settings.thresholds.destroy', $t->id) }}" method="POST" id="delete-threshold-{{ $t->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                            x-data
                                            x-on:click="if(await window.confirmSwal({title:'Hapus {{ $t->name }}?',text:'Yakin ingin menghapus ambang SP ini?',icon:'question',confirmText:'Ya, Hapus!',cancelText:'Batal'})) document.getElementById('delete-threshold-{{ $t->id }}').submit()"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition"
                                            title="Hapus">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Fields --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Nama</label>
                                    <input type="text" name="thresholds[{{ $loop->index }}][name]" value="{{ $t->name }}"
                                        class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Min Poin</label>
                                    <input type="number" name="thresholds[{{ $loop->index }}][min_points]" value="{{ $t->min_points }}" min="0"
                                        class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Maks Poin</label>
                                    <input type="number" name="thresholds[{{ $loop->index }}][max_points]" value="{{ $t->max_points }}" min="0" placeholder="Tak terbatas"
                                        class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Deskripsi</label>
                                    <input type="text" name="thresholds[{{ $loop->index }}][default_description]" value="{{ $t->default_description }}"
                                        class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="submit"
                class="w-full px-5 py-3 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                Simpan Semua Perubahan
            </button>
        </form>
    @else
        {{-- Empty state --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 py-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-chart-bar text-gray-300 text-2xl"></i>
            </div>
            <h4 class="text-sm font-semibold text-gray-500 mb-1">Belum Ada Ambang SP</h4>
            <p class="text-xs text-gray-400 mb-4">Tambahkan ambang Surat Peringatan untuk memantau level pelanggaran siswa</p>
            <button type="button" x-data @click="window.dispatchEvent(new CustomEvent('open-modal', {detail: 'create-threshold'}))"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-blue-600 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition shadow-sm">
                <i class="fa-solid fa-plus text-xs"></i>
                Tambah Ambang SP
            </button>
        </div>
    @endif

    {{-- Create Modal --}}
    <div x-data="{ open: false }"
        x-on:open-modal.window="if($event.detail === 'create-threshold') open = true"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg mx-4">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-plus text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Tambah Ambang SP</h3>
                            <p class="text-xs text-gray-400">Batas poin Surat Peringatan baru</p>
                        </div>
                    </div>
                    <button @click="open = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form action="{{ route('settings.thresholds.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Threshold <span class="text-red-500">*</span></label>
                            <input type="text" name="name" placeholder="SP 4, SP 5, dll" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Minimal Poin <span class="text-red-500">*</span></label>
                                <input type="number" name="min_points" min="0" value="200" required
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Maksimal Poin</label>
                                <input type="number" name="max_points" min="0" placeholder="Tak terbatas"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                            <input type="text" name="default_description" placeholder="Contoh: SP 4 — poin mencapai 200"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Warna</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="color" value="#8b5cf6"
                                    class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                                <span class="text-xs text-gray-400">Pilih warna identitas threshold</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                        <button type="button" @click="open = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm inline-flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="mt-6 bg-gradient-to-br from-blue-50 to-indigo-50/50 border border-blue-100 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-info text-blue-600 text-sm"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-blue-800 mb-1">Cara Kerja Ambang SP</h4>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li><i class="fa-solid fa-check mr-1.5 text-blue-500"></i>SP1 (≥50 poin) — peringatan pertama</li>
                    <li><i class="fa-solid fa-check mr-1.5 text-blue-500"></i>SP2 (≥100 poin) — peringatan kedua</li>
                    <li><i class="fa-solid fa-check mr-1.5 text-blue-500"></i>SP3 (≥150 poin) — peringatan ketiga</li>
                    <li><i class="fa-solid fa-star mr-1.5 text-amber-500"></i>Setiap poin dihitung dari akumulasi semua pelanggaran siswa</li>
                    <li><i class="fa-solid fa-star mr-1.5 text-amber-500"></i>Toggle aktif/nonaktif untuk mengontrol threshold yang digunakan</li>
                    <li><i class="fa-solid fa-star mr-1.5 text-amber-500"></i>Threshold baru otomatis aktif saat dibuat</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
