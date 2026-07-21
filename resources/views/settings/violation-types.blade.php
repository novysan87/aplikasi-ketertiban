@extends('layouts.app')

@section('title', 'Jenis Pelanggaran')

@section('content')
<div x-data="violationTypeManager()">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jenis Pelanggaran</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar jenis pelanggaran beserta poin dan sanksi default</p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Jenis
        </button>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <form method="GET" class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari jenis pelanggaran..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
                <select name="category_id"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <div class="flex space-x-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search','category_id']))
                        <a href="{{ route('settings.violation-types') }}"
                            class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Pelanggaran</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Poin</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sanksi Default</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($types as $type)
                        <tr class="hover:bg-gray-50 transition {{ !$type->is_active ? 'opacity-60' : '' }}">
                            {{-- Kategori --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2.5">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $type->category?->color ?? '#6b7280' }}"></span>
                                    <span class="text-sm font-medium" style="color: {{ $type->category?->color ?? '#6b7280' }}">
                                        {{ $type->category?->name ?? '-' }}
                                    </span>
                                </div>
                            </td>
                            {{-- Nama --}}
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-gray-900 {{ !$type->is_active ? 'line-through' : '' }}">
                                    {{ $type->name }}
                                </div>
                            </td>
                            {{-- Poin --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 text-sm font-bold rounded-lg 
                                    @if($type->points >= 50) bg-blue-100 text-red-700
                                    @elseif($type->points >= 15) bg-yellow-100 text-yellow-700
                                    @else bg-green-100 text-green-700
                                    @endif">
                                    +{{ $type->points }}
                                </span>
                            </td>
                            {{-- Sanksi --}}
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-600 {{ !$type->is_active ? 'line-through' : '' }}">
                                    {{ $type->default_sanction ?: '-' }}
                                </span>
                            </td>
                            {{-- Status --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($type->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            {{-- Aksi --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click="openEdit({{ $type->id }}, {{ $type->category_id }}, '{{ addslashes($type->name) }}', {{ $type->points }}, '{{ addslashes($type->default_sanction ?? '') }}', {{ $type->is_active ? 'true' : 'false' }})"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    @if($type->is_active)
                                        <form action="{{ route('settings.violation-types.update', $type->id) }}" method="POST" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="category_id" value="{{ $type->category_id }}">
                                            <input type="hidden" name="name" value="{{ $type->name }}">
                                            <input type="hidden" name="points" value="{{ $type->points }}">
                                            <input type="hidden" name="default_sanction" value="{{ $type->default_sanction }}">
                                            <input type="hidden" name="is_active" value="0">
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-yellow-600 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition">
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('settings.violation-types.update', $type->id) }}" method="POST" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="category_id" value="{{ $type->category_id }}">
                                            <input type="hidden" name="name" value="{{ $type->name }}">
                                            <input type="hidden" name="points" value="{{ $type->points }}">
                                            <input type="hidden" name="default_sanction" value="{{ $type->default_sanction }}">
                                            <input type="hidden" name="is_active" value="1">
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('settings.violation-types.destroy', $type->id) }}" method="POST" class="inline"
                                        x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus jenis pelanggaran ini?'})) $el.submit()"">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-red-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <p class="text-sm text-gray-500 mb-1">Tidak ada jenis pelanggaran ditemukan</p>
                                <p class="text-xs text-gray-400">Coba ubah filter atau tambah jenis pelanggaran baru</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($types->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                {{ $types->appends(request()->query())->links() }}
            </div>
        @endif

        {{-- Summary --}}
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-between text-xs text-gray-500">
            <span>Menampilkan {{ $types->firstItem() ?? 0 }}–{{ $types->lastItem() ?? 0 }} dari {{ $types->total() }} jenis</span>
            <span class="font-medium">{{ $types->total() }} total</span>
        </div>
    </div>

    {{-- Modal Tambah / Edit --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="modalOpen" @click="modalOpen = false" class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

            <div x-show="modalOpen" @click.away="modalOpen = false"
                class="relative inline-block align-bottom bg-white rounded-2xl shadow-xl border border-gray-200 text-left overflow-hidden transform transition-all sm:align-middle sm:max-w-xl sm:w-full">
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
                            <h3 class="text-lg font-semibold text-gray-900" x-text="isEditing ? 'Edit Jenis Pelanggaran' : 'Tambah Jenis Pelanggaran'"></h3>
                            <p class="text-sm text-gray-500" x-text="isEditing ? 'Ubah detail jenis pelanggaran' : 'Buat jenis pelanggaran baru'"></p>
                        </div>
                    </div>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form :action="isEditing ? `/settings/violation-types/${editId}` : '{{ route('settings.violation-types.store') }}'"
                    method="POST" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">

                    {{-- Kategori --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select x-model="formCategory" @change="updateCategoryColor(formCategory)" name="category_id" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nama --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Pelanggaran <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formName" name="name" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                            placeholder="Contoh: Terlambat datang ke sekolah">
                    </div>

                    {{-- Poin + Sanksi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Poin <span class="text-red-500">*</span></label>
                            <input type="number" x-model="formPoints" name="points" required min="0" max="500"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Sanksi Default</label>
                            <input type="text" x-model="formSanction" name="default_sanction"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                placeholder="Contoh: Teguran lisan">
                        </div>
                    </div>

                    {{-- Status toggle (edit only) --}}
                    <div x-show="isEditing" class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Status Aktif</p>
                            <p class="text-xs text-gray-500">Nonaktifkan untuk menyembunyikan jenis pelanggaran ini</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="formActive" name="is_active" value="1" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                    </div>

                    {{-- Preview --}}
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs font-medium text-gray-500 mb-2">Pratinjau</p>
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                            <div class="flex items-center space-x-2.5">
                                <span class="w-3 h-3 rounded-full flex-shrink-0"
                                    :style="{ backgroundColor: categoryColor }"></span>
                                <span class="text-sm font-medium text-gray-900" x-text="formName || 'Nama Pelanggaran'"></span>
                            </div>
                            <span class="text-sm font-bold text-blue-600" x-text="'+' + (formPoints || 0)"></span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5" x-text="formSanction ? 'Sanksi: ' + formSanction : ''"></p>
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
                            x-text="isEditing ? 'Simpan Perubahan' : 'Tambah Jenis'">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function violationTypeManager() {
    return {
        modalOpen: false,
        isEditing: false,
        editId: null,
        formCategory: '',
        formName: '',
        formPoints: 0,
        formSanction: '',
        formActive: true,
        categoryColor: '#6b7280',

        openCreate() {
            this.isEditing = false;
            this.editId = null;
            this.formCategory = '';
            this.formName = '';
            this.formPoints = 0;
            this.formSanction = '';
            this.formActive = true;
            this.categoryColor = '#6b7280';
            this.modalOpen = true;
        },

        openEdit(id, catId, name, points, sanction, active) {
            this.isEditing = true;
            this.editId = id;
            this.formCategory = catId;
            this.formName = name;
            this.formPoints = points;
            this.formSanction = sanction;
            this.formActive = active;
            this.updateCategoryColor(catId);
            this.modalOpen = true;
        },

        updateCategoryColor(catId) {
            const colors = @json($categories->pluck('color', 'id'));
            this.categoryColor = colors[catId] || '#6b7280';
        }
    };
}
</script>
@endpush
@endsection
