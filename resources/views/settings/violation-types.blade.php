@extends('layouts.app')

@section('title', 'Jenis Pelanggaran')

@section('content')
<div x-data="violationTypeManager()">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Jenis Pelanggaran</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar jenis pelanggaran beserta poin dan sanksi default</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('settings.import.template') }}"
                class="btn-outline">
                <i class="fa-solid fa-file-import text-xs"></i>
                <span class="hidden sm:inline">Download Template</span>
            </a>
            <a href="{{ route('settings.export.violation-types') }}"
                class="btn-outline">
                <i class="fa-solid fa-file-export text-xs"></i>
                <span class="hidden sm:inline">Export Excel</span>
            </a>
            <button onclick="document.getElementById('import-modal').style.display='flex'"
                class="btn-outline">
                <i class="fa-solid fa-download text-xs"></i>
                <span>Import Excel</span>
            </button>
            <button @click="openCreate()"
                class="btn-primary">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Jenis</span>
            </button>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form method="GET" class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari jenis pelanggaran..."
                        class="input pl-9">
                </div>
                <select name="category_id"
                    class="input">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="flex-1 btn-primary justify-center">
                        <i class="fa-solid fa-filter text-xs"></i>Filter
                    </button>
                    @if(request()->anyFilled(['search','category_id']))
                        <a href="{{ route('settings.violation-types') }}"
                            class="btn-outline">
                            <i class="fa-solid fa-xmark"></i>Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-slate-50/90">
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-[0.12em]">Kategori</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-[0.12em]">Nama Pelanggaran</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-slate-400 uppercase tracking-[0.12em]">Poin</th>
                        <th class="px-5 py-3.5 text-left hidden lg:table-cell text-xs font-bold text-slate-400 uppercase tracking-[0.12em]">Sanksi Default</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-slate-400 uppercase tracking-[0.12em]">Status</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-[0.12em]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($types as $type)
                        <tr class="hover:bg-slate-50/60 transition {{ !$type->is_active ? 'opacity-50' : '' }}">
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($type->category)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg"
                                        style="background-color: {{ $type->category->color }}14; color: {{ $type->category->color }}">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $type->category->color }}"></span>
                                        {{ $type->category->name }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-semibold text-slate-900 {{ !$type->is_active ? 'line-through text-slate-400' : '' }}">
                                    {{ $type->name }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-extrabold rounded-lg
                                    @if($type->points >= 50) bg-red-50 text-red-700
                                    @elseif($type->points >= 15) bg-yellow-50 text-yellow-700
                                    @else bg-green-50 text-green-700
                                    @endif">
                                    <i class="fa-solid fa-bolt text-[9px]"></i>
                                    +{{ $type->points }}
                                </span>
                            </td>
                            <td class="px-5 py-4 hidden lg:table-cell">
                                <span class="text-sm text-gray-500 {{ !$type->is_active ? 'line-through' : '' }}">
                                    {{ $type->default_sanction ?: '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($type->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-green-50 text-green-700 border border-green-200 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-500 border border-slate-200 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="openEdit({{ $type->id }}, {{ $type->category_id }}, '{{ addslashes($type->name) }}', {{ $type->points }}, '{{ addslashes($type->default_sanction ?? '') }}', {{ $type->is_active ? 'true' : 'false' }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-200 rounded-lg hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition">
                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                        <span class="hidden sm:inline">Edit</span>
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
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                                                <i class="fa-solid fa-pause text-[10px]"></i>
                                                <span class="hidden sm:inline">Nonaktifkan</span>
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
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                                                <i class="fa-solid fa-play text-[10px]"></i>
                                                <span class="hidden sm:inline">Aktifkan</span>
                                            </button>
                                        </form>
                                    @endif
                                    @if (!$type->is_system)
                                        <form action="{{ route('settings.violation-types.destroy', $type->id) }}" method="POST" class="inline"
                                            x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus jenis pelanggaran ini?'})) $el.submit()">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center w-8 h-8 text-red-500 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg" x-tooltip="'Jenis pelanggaran bawaan sistem — tidak bisa dihapus'">
                                            <i class="fa-solid fa-lock text-[10px]"></i>
                                            <span class="hidden sm:inline">Sistem</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 border border-blue-100 flex items-center justify-center mb-4 shadow-sm">
                                        <i class="fa-solid fa-list text-blue-300 text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-600 mb-1">Tidak ada jenis pelanggaran</p>
                                    <p class="text-xs text-slate-400">Coba ubah filter atau tambah jenis pelanggaran baru</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($types->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $types->appends(request()->query())->links() }}
            </div>
        @endif

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-400">
            <span>Menampilkan {{ $types->firstItem() ?? 0 }}–{{ $types->lastItem() ?? 0 }} dari {{ $types->total() }} jenis</span>
            <span class="font-medium">{{ $types->total() }} total</span>
        </div>
    </div>

    {{-- ===== CRUD MODAL ===== --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="modalOpen" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm"></div>

            <div x-show="modalOpen"
                class="relative inline-block align-bottom bg-white rounded-2xl shadow-2xl border border-slate-200 text-left overflow-hidden transform transition-all sm:align-middle sm:max-w-xl sm:w-full">
                {{-- Header --}}
                <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center shadow-sm backdrop-blur-sm">
                            <i x-show="!isEditing" class="fa-solid fa-plus text-white text-sm"></i>
                            <i x-show="isEditing" class="fa-solid fa-pen-to-square text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white" x-text="isEditing ? 'Edit Jenis Pelanggaran' : 'Tambah Jenis Pelanggaran'"></h3>
                            <p class="text-xs text-blue-100/80" x-text="isEditing ? 'Ubah detail jenis pelanggaran' : 'Buat jenis pelanggaran baru'"></p>
                        </div>
                    </div>
                    <button @click="modalOpen = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-100 hover:text-white hover:bg-white/10 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Form --}}
                <form :action="isEditing ? `/settings/violation-types/${editId}` : '{{ route('settings.violation-types.store') }}'"
                    method="POST" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Kategori --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                            <select x-model="formCategory" @change="updateCategoryColor(formCategory)" name="category_id" required
                                class="input">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Nama --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Nama Pelanggaran <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formName" name="name" required
                                class="input" placeholder="Contoh: Terlambat datang ke sekolah">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Poin --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Poin <span class="text-red-500">*</span></label>
                            <input type="number" x-model="formPoints" name="points" required min="0" max="500"
                                class="input">
                            <p class="text-[11px] text-slate-400 mt-1">Poin pelanggaran (1–500)</p>
                        </div>
                        {{-- Sanksi --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Sanksi Default</label>
                            <input type="text" x-model="formSanction" name="default_sanction"
                                class="input" placeholder="Contoh: Teguran lisan">
                            <p class="text-[11px] text-slate-400 mt-1">Sanksi yang muncul saat input pelanggaran</p>
                        </div>
                    </div>

                    {{-- Status toggle (edit only) --}}
                    <div x-show="isEditing" class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-800">Status Aktif</p>
                            <p class="text-xs text-slate-400 mt-0.5">Nonaktifkan untuk menyembunyikan dari daftar pemilihan</p>
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
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.12em] mb-3">Pratinjau</p>
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-slate-200">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: categoryColor }"></span>
                                <span class="text-sm font-semibold text-slate-900 truncate" x-text="formName || 'Nama Pelanggaran'"></span>
                            </div>
                            <span class="inline-flex items-center gap-1 text-sm font-extrabold flex-shrink-0 ml-3" :class="formPoints >= 50 ? 'text-red-600' : formPoints >= 15 ? 'text-yellow-600' : 'text-green-600'" x-text="'+' + (formPoints || 0)"></span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2" x-text="formSanction ? 'Sanksi: ' + formSanction : ''"></p>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="btn-outline">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid" :class="isEditing ? 'fa-floppy-disk' : 'fa-plus'"></i>
                            <span x-text="isEditing ? 'Simpan Perubahan' : 'Tambah Jenis'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== IMPORT MODAL ===== --}}
    <div id="import-modal" style="display: none;"
        class="fixed inset-0 z-50 flex-col items-center justify-center bg-gray-900/40 backdrop-blur-sm"
        tabindex="0">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg mx-4 overflow-hidden">
            <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center shadow-sm backdrop-blur-sm">
                        <i class="fa-solid fa-download text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Import Excel</h3>
                        <p class="text-xs text-blue-100/80">Upload file .xlsx jenis pelanggaran</p>
                    </div>
                </div>
                <button onclick="document.getElementById('import-modal').style.display='none'" class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('settings.import.violation-types') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf

                <div class="text-sm text-gray-600 mb-4 leading-relaxed">
                    <p class="mb-2">Upload file Excel untuk mengimpor jenis pelanggaran secara massal.</p>
                    <p class="text-xs text-gray-400">Format kolom: <strong>Kategori</strong> • <strong>Nama Pelanggaran</strong> • <strong>Poin</strong> • <strong>Sanksi Default</strong> • <strong>Deskripsi</strong></p>
                </div>

                <label class="flex flex-col items-center justify-center h-36 border-2 border-dashed border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 transition"
                    x-data="{ fileName: '' }"
                    @dragover.prevent="$el.classList.add('border-blue-400', 'bg-blue-50/50')"
                    @dragleave.prevent="$el.classList.remove('border-blue-400', 'bg-blue-50/50')"
                    @drop.prevent="$el.classList.remove('border-blue-400', 'bg-blue-50/50'); const f = $event.dataTransfer.files[0]; if(f) { document.getElementById('import-file-input').files = $event.dataTransfer.files; fileName = f.name; }">
                    <input id="import-file-input" type="file" name="file" accept=".xlsx,.xls" class="hidden"
                        @change="fileName = $event.target.files[0]?.name || ''">

                    <template x-if="!fileName">
                        <div class="text-center">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-300 mb-3"></i>
                            <p class="text-sm text-gray-500">Klik atau drag & drop file di sini</p>
                            <p class="text-xs text-gray-400 mt-1">.xlsx atau .xls, maks 2 MB</p>
                        </div>
                    </template>
                    <template x-if="fileName">
                        <div class="text-center">
                            <i class="fa-solid fa-circle-check text-3xl text-green-500 mb-3"></i>
                            <p class="text-sm font-medium text-gray-900" x-text="fileName"></p>
                            <p class="text-xs text-green-600 mt-1">Siap diupload</p>
                        </div>
                    </template>
                </label>

                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('settings.import.template') }}"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 transition">
                        <i class="fa-solid fa-file-import"></i>
                        Download template
                    </a>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="document.getElementById('import-modal').style.display='none'"
                            class="btn-outline">
                            Batal
                        </button>
                        <button type="submit"
                            class="btn-primary">
                            <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                            Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function violationTypeManager() {
    return {
        modalOpen: false,
        showImport: false,
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
