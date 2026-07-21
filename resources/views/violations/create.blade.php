@extends('layouts.app')

@section('title', 'Input Pelanggaran')

@section('content')
<div x-data="violationForm()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Input Pelanggaran</h1>
        <p class="text-sm text-gray-500">Catat pelanggaran siswa baru</p>
    </div>

    <form action="{{ route('violations.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200">
        @csrf

        <div class="p-6 space-y-6">
            {{-- Search Siswa --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Siswa <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" x-model="searchQuery" @input.debounce="searchStudents"
                        placeholder="Cari NISN atau Nama siswa..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    <input type="hidden" name="student_id" x-model="selectedStudentId">

                    <div x-show="results.length > 0 && !selectedStudentId"
                        class="absolute z-10 mt-1 w-full bg-white shadow-lg border border-gray-200 rounded-xl max-h-48 overflow-y-auto">
                        <template x-for="s in results" :key="s.id">
                            <div @click="selectStudent(s)"
                                class="px-4 py-2.5 hover:bg-red-50 cursor-pointer border-b border-gray-100 last:border-0 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900" x-text="s.full_name"></p>
                                    <p class="text-xs text-gray-500" x-text="s.nisn + ' - ' + (s.class_name || '')"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="selectedStudentId"
                        class="mt-2 flex items-center justify-between p-3 bg-red-50 border border-blue-200 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-blue-600" x-text="selectedStudentName.charAt(0)"></span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900" x-text="selectedStudentName"></p>
                                <p class="text-xs text-gray-500" x-text="selectedStudentInfo"></p>
                            </div>
                        </div>
                        <button type="button" @click="clearStudent()" class="text-red-400 hover:text-blue-600 transition p-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                @error('student_id') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
            </div>

            {{-- Jenis Pelanggaran + Poin & Sanksi in ONE scope --}}
            <div x-data="typeSearch({{ json_encode($typeGroups) }})">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Pelanggaran <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" x-model="q" @input="open=true" @focus="open=true"
                        @click.away="open=false" @keydown.escape="open=false"
                        placeholder="Ketik nama pelanggaran..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                        :class="selectedId ? 'border-green-300 bg-green-50' : ''">

                    <input type="hidden" name="violation_type_id" x-model="selectedId">

                    <div x-show="open && filtered.length > 0"
                        class="absolute z-20 mt-1 w-full bg-white shadow-lg border border-gray-200 rounded-xl max-h-72 overflow-y-auto">
                        <template x-for="(group, gi) in filtered" :key="gi">
                            <div>
                                <div class="sticky top-0 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider border-b flex items-center space-x-1.5 z-10"
                                    :style="'background:' + group.color + '15; color:' + group.color + '; border-color:' + group.color + '30'">
                                    <span class="w-2 h-2 rounded-full" :style="'background:' + group.color"></span>
                                    <span x-text="group.label"></span>
                                    <span class="ml-1 opacity-60" x-text="'(' + group.types.length + ')'"></span>
                                </div>
                                <template x-for="t in group.types" :key="t.id">
                                    <div @click="pick(t)" @keydown.enter="pick(t)" tabindex="0"
                                        class="px-4 py-2.5 cursor-pointer border-b border-gray-50 flex items-center justify-between transition"
                                        :class="selectedId == t.id ? 'bg-red-50 border-l-2 border-l-red-500' : 'hover:bg-gray-50'">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate" x-text="t.name"></p>
                                            <p class="text-xs text-gray-400 truncate" x-text="t.sanction || '—'"></p>
                                        </div>
                                        <span class="ml-3 text-sm font-bold flex-shrink-0 px-2 py-0.5 rounded"
                                            :class="t.points >= 50 ? 'bg-blue-100 text-red-700' : (t.points >= 15 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')"
                                            x-text="'+' + t.points"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div x-show="open && filtered.length === 0 && q.length > 0"
                        class="absolute z-20 mt-1 w-full bg-white shadow-lg border border-gray-200 rounded-xl p-5 text-center">
                        <p class="text-sm text-gray-500">Tidak ditemukan untuk "<span x-text="q" class="font-medium"></span>"</p>
                    </div>

                    <div x-show="selectedId && selectedName"
                        class="mt-2 flex items-center justify-between p-3 rounded-xl border"
                        :style="'border-color:' + selColor + '40; background:' + selColor + '08'">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-3 h-3 rounded-full" :style="'background:' + selColor"></span>
                            <div>
                                <p class="text-sm font-medium text-gray-900" x-text="selectedName"></p>
                                <p class="text-xs text-gray-500" x-text="selectedSanction ? 'Sanksi: ' + selectedSanction : ''"></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-xl font-bold" :class="selectedPoints >= 50 ? 'text-blue-600' : (selectedPoints >= 15 ? 'text-yellow-600' : 'text-green-600')" x-text="'+' + selectedPoints"></span>
                            <button @click="clear()" class="text-gray-300 hover:text-red-500 transition">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @error('violation_type_id') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror

                {{-- Poin & Sanksi -- same scope as typeSearch --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Poin</label>
                        <input type="number" name="points" x-model="selectedPoints" readonly
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl sm:text-sm text-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Sanksi</label>
                        <input type="text" name="sanction" x-model="selectedSanction"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                </div>
            </div>

            {{-- Tanggal & Waktu --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Pelanggaran <span class="text-red-500">*</span></label>
                    <input type="date" name="violation_date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Waktu</label>
                    <input type="time" name="violation_time" value="{{ date('H:i') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
            </div>

            {{-- Lokasi --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi</label>
                <input type="text" name="location" placeholder="Contoh: Depan kelas, Lapangan, dll"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
            </div>

            {{-- Deskripsi --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi / Catatan</label>
                <textarea name="description" rows="3" placeholder="Deskripsi pelanggaran..."
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition resize-none"></textarea>
            </div>

            {{-- Foto Bukti --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Foto <span class="text-gray-400 font-normal">(maks. 5 foto)</span></label>
                <input type="file" name="evidences[]" id="evidences" multiple accept="image/*" capture="environment" @change="handleFiles" class="hidden">

                <div class="grid grid-cols-3 md:grid-cols-5 gap-3 mb-3">
                    <template x-for="(file, index) in files" :key="index">
                        <div class="relative aspect-square rounded-xl overflow-hidden border border-gray-200 bg-gray-50 group">
                            <img :src="file.url" class="w-full h-full object-cover">
                            <button type="button" @click="removeFile(index)"
                                class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-blue-700 transition opacity-0 group-hover:opacity-100">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent text-white text-xs px-2 py-1.5 truncate" x-text="file.name"></div>
                        </div>
                    </template>
                    <template x-if="files.length < 5">
                        <div @click="document.getElementById('evidences').click()"
                            class="aspect-square rounded-xl border-2 border-dashed border-gray-300 hover:border-red-400 bg-gray-50 flex flex-col items-center justify-center cursor-pointer transition hover:bg-red-50/30">
                            <i class="fa-solid fa-camera text-gray-300 text-2xl mb-1"></i>
                            <span class="text-xs text-gray-400">Tambah Foto</span>
                        </div>
                    </template>
                </div>

                <div class="flex space-x-2">
                    <button type="button" @click="document.getElementById('evidences').click()" class="px-4 py-2 text-sm border border-gray-300 rounded-xl hover:bg-gray-50 transition flex items-center space-x-2">
                        <i class="fa-solid fa-image text-gray-500"></i><span>Pilih File</span>
                    </button>
                    <button type="button" @click="captureCamera" class="px-4 py-2 text-sm border border-gray-300 rounded-xl hover:bg-gray-50 transition flex items-center space-x-2">
                        <i class="fa-solid fa-camera text-gray-500"></i><span>Ambil Foto</span>
                    </button>
                </div>
                @error('evidences.*') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Submit --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl flex justify-end space-x-3">
            <a href="{{ route('violations.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm flex items-center space-x-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Pelanggaran</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function typeSearch(groups) {
    return {
        q: '', open: false,
        selectedId: '', selectedName: '',
        selectedPoints: 0, selectedSanction: '',
        selColor: '#6b7280',
        groups: groups,

        get filtered() {
            if (!this.q || this.q.length < 1) return this.groups;
            const q = this.q.toLowerCase();
            return this.groups.map(g => ({
                ...g,
                types: g.types.filter(t =>
                    t.name.toLowerCase().includes(q) ||
                    (t.sanction && t.sanction.toLowerCase().includes(q))
                )
            })).filter(g => g.types.length > 0);
        },

        pick(t) {
            this.selectedId = t.id;
            this.selectedName = t.name;
            this.selectedPoints = t.points;
            this.selectedSanction = t.sanction || '';
            this.selColor = this.groups.find(g => g.types.some(x => x.id === t.id))?.color || '#6b7280';
            this.q = t.name;
            this.open = false;
        },

        clear() {
            this.selectedId = ''; this.selectedName = '';
            this.selectedPoints = 0; this.selectedSanction = '';
            this.selColor = '#6b7280'; this.q = ''; this.open = false;
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
