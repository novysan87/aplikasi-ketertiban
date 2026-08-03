@extends('layouts.app')

@section('title', 'Kategori Pelanggaran')

@section('content')
<div x-data="categoryManager()" class="">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kategori Pelanggaran</h1>
            <p class="text-sm text-slate-500 mt-1">Kelompokkan jenis pelanggaran berdasarkan tingkat keparahan</p>
        </div>
        <button @click="openCreate()"
            class="btn-primary flex-shrink-0">
            <i class="fa-solid fa-plus text-xs"></i>
            Tambah Kategori
        </button>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @forelse($categories as $cat)
            @php
                $typeCount = $cat->violationTypes()->count();
            @endphp
            <div class="group bg-white rounded-2xl shadow-sm border border-slate-200/80 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 overflow-hidden @if(!$cat->is_active) opacity-70 @endif">
                {{-- Color strip gradient --}}
                <div class="h-1.5 w-full" style="background: linear-gradient(90deg, {{ $cat->color }}, {{ $cat->color }}77)"></div>

                <div class="p-5">
                    {{-- Header row --}}
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md flex-shrink-0"
                                style="background: linear-gradient(135deg, {{ $cat->color }}, {{ $cat->color }}bb); color: #fff">
                                <i class="fa-solid fa-tag text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[15px] font-extrabold text-slate-900 tracking-tight truncate">{{ $cat->name }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $cat->description ?: 'Tanpa deskripsi' }}</p>
                            </div>
                        </div>
                        @if(!$cat->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold bg-slate-100 text-slate-500 rounded-full flex-shrink-0">
                                <i class="fa-solid fa-pause text-[8px]"></i> Nonaktif
                            </span>
                        @endif
                    </div>

                    {{-- Stats row --}}
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold"
                            style="background-color: {{ $cat->color }}12; color: {{ $cat->color }}">
                            <i class="fa-solid fa-list text-[10px]"></i> {{ $typeCount }} jenis
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-500">
                            <i class="fa-solid fa-arrow-down-1-9 text-[10px]"></i> Urutan #{{ $cat->sort_order }}
                        </span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <button @click="openEdit({{ $cat->id }}, '{{ $cat->name }}', '{{ $cat->color }}', '{{ $cat->description }}', {{ $cat->sort_order }}, {{ json_encode(!$cat->is_active) }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-200 rounded-lg hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition">
                                <i class="fa-solid fa-pen text-[10px]"></i>
                                Edit
                            </button>
                            @if($cat->is_active)
                                <form action="{{ route('settings.categories.update', $cat->id) }}" method="POST" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $cat->name }}">
                                    <input type="hidden" name="color" value="{{ $cat->color }}">
                                    <input type="hidden" name="description" value="{{ $cat->description }}">
                                    <input type="hidden" name="sort_order" value="{{ $cat->sort_order }}">
                                    <input type="hidden" name="is_active" value="0">
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                                        <i class="fa-solid fa-pause text-[10px]"></i>
                                        Nonaktifkan
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('settings.categories.update', $cat->id) }}" method="POST" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $cat->name }}">
                                    <input type="hidden" name="color" value="{{ $cat->color }}">
                                    <input type="hidden" name="description" value="{{ $cat->description }}">
                                    <input type="hidden" name="sort_order" value="{{ $cat->sort_order }}">
                                    <input type="hidden" name="is_active" value="1">
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                                        <i class="fa-solid fa-play text-[10px]"></i>
                                        Aktifkan
                                    </button>
                                </form>
                            @endif
                        </div>
                        @if($typeCount === 0)
                            <form action="{{ route('settings.categories.destroy', $cat->id) }}" method="POST"
                                x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus kategori ini?'})) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition"
                                    title="Hapus">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-14 bg-white rounded-2xl border border-dashed border-slate-300">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 border border-blue-100 flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i class="fa-solid fa-tags text-blue-300 text-2xl"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-600">Belum ada kategori pelanggaran</p>
                    <button @click="openCreate()" class="mt-4 btn-primary">
                        <i class="fa-solid fa-plus text-xs"></i>
                        Tambah kategori pertama
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Modal Create/Edit --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            {{-- Overlay --}}
            <div x-show="modalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

            {{-- Panel --}}
            <div x-show="modalOpen" 
                class="relative inline-block align-bottom bg-white rounded-2xl shadow-2xl border border-slate-200 text-left overflow-hidden transform transition-all sm:align-middle sm:max-w-lg sm:w-full">
                {{-- Header --}}
                <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center shadow-sm backdrop-blur-sm">
                            <i class="fa-solid fa-tag text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white" x-text="isEditing ? 'Edit Kategori' : 'Tambah Kategori'"></h3>
                            <p class="text-xs text-blue-100/80" x-text="isEditing ? 'Ubah detail kategori pelanggaran' : 'Buat kategori pelanggaran baru'"></p>
                        </div>
                    </div>
                    <button @click="modalOpen = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-100 hover:text-white hover:bg-white/10 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Form --}}
                <form :action="isEditing ? `/settings/categories/${editId}` : '{{ route('settings.categories.store') }}'"
                    method="POST" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">

                    {{-- Nama --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Nama Kategori</label>
                        <input type="text" x-model="formName" name="name" required class="input"
                            placeholder="Contoh: Ringan, Sedang, Berat">
                    </div>

                    {{-- Color Picker --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Warna</label>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="formColor" name="color"
                                class="w-11 h-11 rounded-xl border border-slate-200 cursor-pointer p-1 bg-white shadow-sm">
                            <input type="text" x-model="formColor" name="color"
                                class="input flex-1 font-mono" placeholder="#22c55e">
                            <div class="flex-shrink-0 w-11 h-11 rounded-xl border border-slate-200 shadow-sm"
                                :style="{ backgroundColor: formColor }"></div>
                        </div>
                        <div class="flex items-center gap-1.5 mt-2.5 flex-wrap">
                            <template x-for="c in ['#22c55e','#3b82f6','#eab308','#f97316','#ef4444','#8b5cf6','#ec4899','#06b6d4']" :key="c">
                                <button type="button" @click="formColor = c"
                                    class="w-7 h-7 rounded-lg border border-slate-200 transition-transform hover:scale-110"
                                    :style="'background-color:' + c"
                                    :class="formColor === c ? 'ring-2 ring-offset-2 ring-blue-400 scale-110' : ''"
                                    :title="c"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Urutan Tampil</label>
                        <input type="number" x-model="formSort" name="sort_order" min="0" class="input" placeholder="0">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <textarea x-model="formDesc" name="description" rows="2" class="input resize-none"
                            placeholder="Penjelasan singkat tentang kategori ini"></textarea>
                    </div>

                    {{-- Status toggle for edit --}}
                    <div x-show="isEditing" class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-800">Status Aktif</p>
                            <p class="text-xs text-slate-400">Nonaktifkan untuk menyembunyikan kategori ini</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" x-model="formActive" name="is_active" value="1" class="sr-only peer">
                            <div class="w-11 h-6 rounded-full transition-all duration-300"
                                :style="formActive ? 'background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 0 14px 2px rgba(59,130,246,0.35);' : 'background-color: #e2e8f0; box-shadow: inset 0 1px 3px rgba(15,23,42,0.1);'">
                                <div class="absolute top-[2px] left-[2px] h-5 w-5 bg-white rounded-full shadow-md transition-all duration-300"
                                    :class="formActive ? 'translate-x-5' : ''"></div>
                            </div>
                        </label>
                    </div>

                    {{-- Preview --}}
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.12em] mb-2">Pratinjau</p>
                        <div class="flex items-center gap-2 p-2.5 bg-white rounded-lg border border-slate-200">
                            <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: formColor }"></span>
                            <span class="text-sm font-semibold text-slate-900" x-text="formName || 'Nama Kategori'"></span>
                            <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full font-medium"
                                :style="{ backgroundColor: formColor + '20', color: formColor }"
                                x-text="formDesc || 'Deskripsi'"></span>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="modalOpen = false" class="btn-outline">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary"
                            x-text="isEditing ? 'Simpan Perubahan' : 'Tambah Kategori'">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function categoryManager() {
    return {
        modalOpen: false,
        isEditing: false,
        editId: null,
        formName: '',
        formColor: '#22c55e',
        formDesc: '',
        formSort: 0,
        formActive: true,

        openCreate() {
            this.isEditing = false;
            this.editId = null;
            this.formName = '';
            this.formColor = '#22c55e';
            this.formDesc = '';
            this.formSort = 0;
            this.formActive = true;
            this.modalOpen = true;
        },

        openEdit(id, name, color, desc, sort, inactive) {
            this.isEditing = true;
            this.editId = id;
            this.formName = name;
            this.formColor = color;
            this.formDesc = desc || '';
            this.formSort = sort;
            this.formActive = !inactive;
            this.modalOpen = true;
        }
    };
}
</script>
@endpush
@endsection
