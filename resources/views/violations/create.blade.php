@extends('layouts.app')

@section('title', 'Input Pelanggaran')

@section('content')
<div x-data="violationForm()">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-400 mb-1">
                <a href="{{ route('violations.index') }}" class="hover:text-gray-600 transition">Data Pelanggaran</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-medium">Input Baru</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Input Pelanggaran</h1>
            <p class="text-sm text-gray-500 mt-1">Catat pelanggaran siswa baru</p>
        </div>
    </div>

    <form action="{{ route('violations.store') }}" method="POST" enctype="multipart/form-data"
        class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        @csrf

        {{-- Gradient header --}}
        <div class="bg-gradient-to-r from-blue-500 via-blue-400 to-sky-300 px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                    <i class="fa-solid fa-gavel text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Form Pelanggaran Baru</h2>
                    <p class="text-xs text-white/70">Isi data pelanggaran dengan lengkap</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-8">
            {{-- ===== BAGIAN 1: DATA SISWA ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i class="fa-solid fa-user-graduate text-blue-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Data Siswa</span>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Cari Siswa <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input type="text" x-model="searchQuery" @input.debounce="searchStudents"
                                placeholder="Cari NISN atau Nama siswa..."
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        </div>
                        <input type="hidden" name="student_id" x-model="selectedStudentId">

                        {{-- Search results dropdown --}}
                        <div x-show="results.length > 0 && !selectedStudentId"
                            class="absolute z-10 mt-1 w-full bg-white shadow-lg border border-gray-200 rounded-xl max-h-48 overflow-y-auto">
                            <template x-for="s in results" :key="s.id">
                                <div @click="selectStudent(s)"
                                    class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="s.full_name"></p>
                                        <p class="text-xs text-gray-500" x-text="s.nisn + ' - ' + (s.class_name || '')"></p>
                                    </div>
                                    <span class="text-xs text-gray-400" x-text="s.class_level"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Selected student card --}}
                        <div x-show="selectedStudentId"
                            class="mt-2.5 flex items-center justify-between p-3 bg-gradient-to-br from-blue-50 to-indigo-50/50 border border-blue-100 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <span class="text-sm font-bold text-white" x-text="selectedStudentName.charAt(0)"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900" x-text="selectedStudentName"></p>
                                    <p class="text-xs text-gray-500" x-text="selectedStudentInfo"></p>
                                </div>
                            </div>
                            <button type="button" @click="clearStudent()"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    @error('student_id') <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ===== BAGIAN 2: PELANGGARAN ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Detail Pelanggaran</span>
                </div>

                <div x-data="typeMultiSearch({{ json_encode($typeGroups) }})" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jenis Pelanggaran <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-400 mb-2">Cari dan pilih satu atau lebih pelanggaran yang dilakukan</p>

                        {{-- Search with dropdown --}}
                        <div class="relative">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                <input type="text" x-model="q" @input="open=true" @focus="open=true"
                                    @click.away="open=false" @keydown.escape="open=false"
                                    placeholder="Ketik nama pelanggaran untuk menambah..."
                                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            </div>

                            {{-- Search results --}}
                            <div x-show="open && filtered.length > 0"
                                class="absolute z-20 mt-1 w-full bg-white shadow-lg border border-gray-200 rounded-xl max-h-72 overflow-y-auto">
                                <template x-for="(group, gi) in filtered" :key="gi">
                                    <div>
                                        <div class="sticky top-0 px-4 py-1.5 text-xs font-bold uppercase tracking-wider border-b flex items-center gap-1.5 z-10"
                                            :style="'background:' + group.color + '12; color:' + group.color + '; border-color:' + group.color + '30'">
                                            <span class="w-2 h-2 rounded-full" :style="'background:' + group.color"></span>
                                            <span x-text="group.label"></span>
                                            <span class="ml-auto text-[10px] opacity-60" x-text="'(' + group.types.length + ')'"></span>
                                        </div>
                                        <template x-for="t in group.types" :key="t.id">
                                            <div @click="addType(t)" @keydown.enter="addType(t)" tabindex="0"
                                                class="px-4 py-2.5 cursor-pointer border-b border-gray-50 flex items-center justify-between transition hover:bg-blue-50"
                                                :class="selectedIds.includes(t.id) ? 'bg-blue-50/50 opacity-50' : ''">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900 truncate" x-text="t.name"></p>
                                                    <p class="text-xs text-gray-400 truncate" x-text="t.sanction || '—'"></p>
                                                </div>
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg"
                                                        :class="t.points >= 50 ? 'bg-red-50 text-red-700' : (t.points >= 15 ? 'bg-yellow-50 text-yellow-700' : 'bg-blue-50 text-blue-700')"
                                                        x-text="'+' + t.points"></span>
                                                    <template x-if="selectedIds.includes(t.id)">
                                                        <i class="fa-solid fa-check text-green-500 text-sm"></i>
                                                    </template>
                                                    <template x-if="!selectedIds.includes(t.id)">
                                                        <i class="fa-solid fa-plus text-gray-300 text-sm"></i>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            {{-- No results --}}
                            <div x-show="open && filtered.length === 0 && q.length > 0"
                                class="absolute z-20 mt-1 w-full bg-white shadow-lg border border-gray-200 rounded-xl p-5 text-center">
                                <p class="text-sm text-gray-500">Tidak ditemukan untuk "<span x-text="q" class="font-medium"></span>"</p>
                            </div>
                        </div>
                        @error('violation_type_ids') <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                        @error('violation_type_ids.*') <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                    </div>

                    {{-- Selected types list --}}
                    <template x-if="selected.length > 0">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider" x-text="'Dipilih (' + selected.length + ')'"></span>
                                <button type="button" @click="clearAll()" class="text-xs font-medium text-red-500 hover:text-red-700 transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-xmark"></i> Hapus Semua
                                </button>
                            </div>
                            <div class="space-y-2">
                                <template x-for="(item, i) in selected" :key="item.id">
                                    <div class="flex items-center justify-between p-3 rounded-xl border shadow-sm"
                                        :style="'border-color:' + item.color + '30; background:' + item.color + '06'">
                                        <input type="hidden" :name="'violation_type_ids[' + i + ']'" :value="item.id">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-3 h-3 rounded-full flex-shrink-0" :style="'background:' + item.color"></span>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-gray-900" x-text="item.name"></p>
                                                <p class="text-xs text-gray-500" x-text="item.sanction || 'Tanpa sanksi'"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 flex-shrink-0">
                                            <span class="text-base font-bold"
                                                :class="item.points >= 50 ? 'text-red-600' : (item.points >= 15 ? 'text-yellow-600' : 'text-blue-600')"
                                                x-text="'+' + item.points"></span>
                                            <button type="button" @click="removeType(i)"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Summary bar --}}
                            <div class="mt-3 flex items-center justify-between p-3 bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-calculator text-gray-400 text-xs"></i>
                                    <span class="text-xs text-gray-600 font-medium">Total</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-gray-500" x-text="selected.length + ' pelanggaran'"></span>
                                    <span class="text-lg font-bold text-gray-800" x-text="'+' + totalPoints"></span>
                                </div>
                            </div>

                            {{-- Severity info --}}
                            <div class="mt-2 flex items-center gap-2 p-3 rounded-xl text-xs font-medium"
                                :class="totalPoints >= 100 ? 'bg-red-50 text-red-700 border border-red-200' : (totalPoints >= 50 ? 'bg-orange-50 text-orange-700 border border-orange-200' : (totalPoints >= 15 ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' : 'bg-blue-50 text-blue-700 border border-blue-200'))">
                                <i class="fa-solid fa-circle-info"></i>
                                <span x-text="totalPoints >= 100 ? 'Perhatian! Total poin mencapai level SP-2/3 — segera ditindaklanjuti' : (totalPoints >= 50 ? 'Total poin mencapai level SP-1 — perlu penanganan' : (totalPoints >= 15 ? 'Akumulasi poin sedang — perlu dipantau' : 'Total poin masih dalam batas wajar'))"></span>
                            </div>
                        </div>
                    </template>

                    {{-- Empty state --}}
                    <template x-if="selected.length === 0">
                        <div class="flex items-center gap-2 p-3 rounded-xl border border-dashed border-gray-200 bg-gray-50/50">
                            <i class="fa-solid fa-circle-plus text-gray-300"></i>
                            <span class="text-xs text-gray-400">Gunakan pencarian di atas untuk memilih pelanggaran</span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ===== BAGIAN 3: WAKTU & LOKASI ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-teal-50 flex items-center justify-center">
                        <i class="fa-solid fa-clock text-teal-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Waktu & Lokasi</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="violation_date" value="{{ date('Y-m-d') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Waktu</label>
                        <input type="time" name="violation_time" value="{{ date('H:i') }}"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Lokasi</label>
                        <input type="text" name="location" placeholder="Depan kelas, Lapangan, dll"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                </div>
            </div>

            {{-- ===== BAGIAN 4: CATATAN ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-violet-50 flex items-center justify-center">
                        <i class="fa-solid fa-note-sticky text-violet-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Catatan</span>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi / Catatan Pelanggaran</label>
                    <textarea name="description" rows="3" placeholder="Deskripsi lengkap pelanggaran yang terjadi..."
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition resize-none"></textarea>
                </div>
            </div>

            {{-- ===== BAGIAN 5: BUKTI FOTO ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-sky-50 flex items-center justify-center">
                        <i class="fa-solid fa-camera text-sky-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Bukti Foto</span>
                    <span class="text-[10px] text-gray-400 font-medium">(maks. 5 foto)</span>
                </div>

                <input type="file" name="evidences[]" id="evidences" multiple accept="image/*" capture="environment" @change="handleFiles" class="hidden">

                <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-3">
                    <template x-for="(file, index) in files" :key="index">
                        <div class="relative aspect-square rounded-xl overflow-hidden border border-gray-200 bg-gray-50 group shadow-sm">
                            <img :src="file.url" class="w-full h-full object-cover">
                            <button type="button" @click="removeFile(index)"
                                class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600 transition opacity-0 group-hover:opacity-100 shadow-lg">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent text-white text-[10px] px-2 py-1.5 truncate" x-text="file.name"></div>
                        </div>
                    </template>

                    {{-- Upload placeholder --}}
                    <template x-if="files.length < 5">
                        <div @click="document.getElementById('evidences').click()"
                            class="aspect-square rounded-xl border-2 border-dashed border-gray-200 hover:border-blue-300 bg-gray-50/50 flex flex-col items-center justify-center cursor-pointer transition hover:bg-blue-50/30 group">
                            <i class="fa-solid fa-camera text-gray-300 text-2xl mb-1 group-hover:text-blue-400 transition"></i>
                            <span class="text-xs text-gray-400 group-hover:text-blue-500 transition font-medium">Tambah Foto</span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="document.getElementById('evidences').click()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition shadow-sm">
                        <i class="fa-solid fa-image text-gray-400"></i>
                        Pilih File
                    </button>
                    <button type="button" @click="captureCamera"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition shadow-sm">
                        <i class="fa-solid fa-camera text-gray-400"></i>
                        Ambil Foto
                    </button>
                </div>
                @error('evidences.*') <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('violations.index') }}"
                class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition inline-flex items-center gap-2">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                Simpan Pelanggaran
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function typeMultiSearch(groups) {
    return {
        q: '', open: false,
        selected: [],
        groups: groups,

        get filtered() {
            if (!this.q || this.q.length < 1) return this.groups;
            const query = this.q.toLowerCase();
            return this.groups.map(g => ({
                ...g,
                types: g.types.filter(t =>
                    t.name.toLowerCase().includes(query) ||
                    (t.sanction && t.sanction.toLowerCase().includes(query))
                )
            })).filter(g => g.types.length > 0);
        },

        get selectedIds() {
            return this.selected.map(s => s.id);
        },

        get totalPoints() {
            return this.selected.reduce((sum, s) => sum + s.points, 0);
        },

        addType(t) {
            if (this.selectedIds.includes(t.id)) return;
            const color = this.groups.find(g => g.types.some(x => x.id === t.id))?.color || '#6b7280';
            this.selected.push({ ...t, color });
            this.q = '';
            this.open = false;
        },

        removeType(i) {
            this.selected.splice(i, 1);
        },

        clearAll() {
            this.selected = [];
            this.q = '';
        }
    };
}

function violationForm() {
    return {
        searchQuery: '', results: [],
        selectedStudentId: null, selectedStudentName: '', selectedStudentInfo: '',
        files: [],

        searchStudents() {
            if (this.searchQuery.length < 2) { this.results = []; return; }
            if (this.selectedStudentId) return;
            fetch('{{ route("api.students.search") }}?q=' + encodeURIComponent(this.searchQuery))
                .then(r => r.json()).then(data => { this.results = data; });
        },

        selectStudent(s) {
            this.selectedStudentId = s.id; this.selectedStudentName = s.full_name;
            this.selectedStudentInfo = s.nisn + ' - ' + (s.class_name || '');
            this.searchQuery = s.full_name; this.results = [];
        },

        clearStudent() {
            this.selectedStudentId = null; this.selectedStudentName = '';
            this.selectedStudentInfo = ''; this.searchQuery = '';
        },

        handleFiles(e) {
            const newFiles = Array.from(e.target.files);
            const toAdd = newFiles.slice(0, 5 - this.files.length);
            toAdd.forEach(file => {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.files.push({ file, url: ev.target.result, name: file.name });
                };
                reader.readAsDataURL(file);
            });
        },

        removeFile(index) { this.files.splice(index, 1); },

        captureCamera() {
            const input = document.getElementById('evidences');
            input.setAttribute('capture', 'environment'); input.click();
            setTimeout(() => input.removeAttribute('capture'), 100);
        }
    };
}
</script>
@endpush
@endsection
