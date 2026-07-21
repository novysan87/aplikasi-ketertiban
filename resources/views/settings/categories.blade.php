@extends('layouts.app')

@section('title', 'Kategori Pelanggaran')

@section('content')
<div x-data="categoryManager()" class="">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kategori Pelanggaran</h1>
            <p class="text-sm text-gray-500 mt-1">Kelompokkan jenis pelanggaran berdasarkan tingkat keparahan</p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
        </button>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @forelse($categories as $cat)
            @php
                $typeCount = $cat->violationTypes()->count();
            @endphp
            <div class="group bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 @if(!$cat->is_active) opacity-60 @endif">
                {{-- Color bar --}}
                <div class="h-2 rounded-t-xl" style="background-color: {{ $cat->color }}"></div>

                <div class="p-5">
                    {{-- Header row --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <span class="w-4 h-4 rounded-full flex-shrink-0 ring-2 ring-offset-1" style="background-color: {{ $cat->color }}; --tw-ring-color: {{ $cat->color }}40"></span>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ $cat->name }}</h3>
                                @if($cat->description)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $cat->description }}</p>
                                @endif
                            </div>
                        </div>
                        @if(!$cat->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">Nonaktif</span>
                        @endif
                    </div>

                    {{-- Stats row --}}
                    <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">
                        <div class="flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span>{{ $typeCount }} jenis</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span>Urutan #{{ $cat->sort_order }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="flex space-x-2">
                            <button @click="openEdit({{ $cat->id }}, '{{ $cat->name }}', '{{ $cat->color }}', '{{ $cat->description }}', {{ $cat->sort_order }}, {{ json_encode(!$cat->is_active) }})"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
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
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-yellow-600 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
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
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Aktifkan
                                    </button>
                                </form>
                            @endif
                        </div>
                        @if($typeCount === 0)
                            <form action="{{ route('settings.categories.destroy', $cat->id) }}" method="POST"
                                x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus kategori ini?'})) $el.submit()"">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-red-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12 bg-white rounded-xl border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Belum ada kategori pelanggaran.</p>
                    <button @click="openCreate()" class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                        + Tambah kategori pertama
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Modal Create/Edit --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            {{-- Overlay --}}
            <div x-show="modalOpen" @click="modalOpen = false" class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

            {{-- Panel --}}
            <div x-show="modalOpen" @click.away="modalOpen = false"
                class="relative inline-block align-bottom bg-white rounded-2xl shadow-xl border border-gray-200 text-left overflow-hidden transform transition-all sm:align-middle sm:max-w-lg sm:w-full">
                {{-- Header --}}
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="isEditing ? 'bg-blue-100' : 'bg-blue-100'">
                            <svg x-show="!isEditing" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <svg x-show="isEditing" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="isEditing ? 'Edit Kategori' : 'Tambah Kategori'"></h3>
                            <p class="text-sm text-gray-500" x-text="isEditing ? 'Ubah detail kategori pelanggaran' : 'Buat kategori pelanggaran baru'"></p>
                        </div>
                    </div>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form :action="isEditing ? `/settings/categories/${editId}` : '{{ route('settings.categories.store') }}'"
                    method="POST" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">

                    {{-- Nama --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Kategori</label>
                        <input type="text" x-model="formName" name="name" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                            placeholder="Contoh: Ringan, Sedang, Berat">
                    </div>

                    {{-- Color Picker --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Warna</label>
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <input type="color" x-model="formColor" name="color"
                                    class="w-12 h-12 rounded-xl border border-gray-300 cursor-pointer p-0.5">
                            </div>
                            <input type="text" x-model="formColor" name="color"
                                class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                placeholder="#22c55e">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl border border-gray-200"
                                :style="{ backgroundColor: formColor }"></div>
                        </div>
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Urutan Tampil</label>
                        <input type="number" x-model="formSort" name="sort_order" min="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                            placeholder="0">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <textarea x-model="formDesc" name="description" rows="2"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition resize-none"
                            placeholder="Penjelasan singkat tentang kategori ini"></textarea>
                    </div>

                    {{-- Status toggle for edit --}}
                    <div x-show="isEditing" class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Status Aktif</p>
                            <p class="text-xs text-gray-500">Nonaktifkan untuk menyembunyikan kategori ini</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="formActive" name="is_active" value="1" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                    </div>

                    {{-- Preview --}}
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs font-medium text-gray-500 mb-2">Pratinjau</p>
                        <div class="flex items-center space-x-2 p-2.5 bg-white rounded-lg border border-gray-200">
                            <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: formColor }"></span>
                            <span class="text-sm font-medium text-gray-900" x-text="formName || 'Nama Kategori'"></span>
                            <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full font-medium"
                                :style="{
                                    backgroundColor: formColor + '20',
                                    color: formColor
                                }"
                                x-text="formDesc || 'Deskripsi'"></span>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="modalOpen = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white rounded-xl transition shadow-sm"
                            :class="isEditing ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-600 hover:bg-blue-700'"
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
