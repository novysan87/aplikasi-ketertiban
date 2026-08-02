@extends('layouts.app')

@section('title', 'Jenis Penanganan')

@section('content')
<div x-data="{
    showModal: false,
    isEditing: false,
    editId: null,
    formName: '',
    formIcon: 'fa-clipboard-list',
    formColor: '#64748b',
    formSort: 0,
    openCreate() {
        this.isEditing = false; this.editId = null;
        this.formName = ''; this.formIcon = 'fa-clipboard-list'; this.formColor = '#64748b'; this.formSort = 0;
        this.showModal = true;
    },
    openEdit(t) {
        this.isEditing = true; this.editId = t.id;
        this.formName = t.name; this.formIcon = t.icon; this.formColor = t.color; this.formSort = t.sort_order;
        this.showModal = true;
    },
    colors: ['#3b82f6','#4f46e5','#14b8a6','#10b981','#f59e0b','#f97316','#ef4444','#8b5cf6','#64748b','#0ea5e9'],
    icons: ['fa-comment','fa-file-pen','fa-hand-holding-heart','fa-phone-volume','fa-house-chimney','fa-ban','fa-handshake-angle','fa-file-signature','fa-file-circle-exclamation','fa-clipboard-list','fa-gavel','fa-user-check','fa-chalkboard-user','fa-bullhorn']
}">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-400 mb-1">
                <a href="{{ route('settings.index') }}" class="hover:text-gray-600 transition">Pengaturan</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-medium">Jenis Penanganan</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Jenis Penanganan</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola jenis tindak lanjut yang tersedia di detail pelanggaran</p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl shadow-md shadow-orange-200 hover:-translate-y-0.5 transition-all active:scale-95 self-start sm:self-auto">
            <i class="fa-solid fa-plus"></i> Tambah Jenis
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-semibold text-emerald-700">
            <i class="fa-solid fa-circle-check mr-1.5"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-sm font-semibold text-red-700">
            <i class="fa-solid fa-circle-exclamation mr-1.5"></i>{{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
            @foreach($errors->all() as $e)
                <p><i class="fa-solid fa-circle-exclamation mr-1.5"></i>{{ $e }}</p>
            @endforeach
        </div>
    @endif

    @php
        $typeReorderData = $types->map(fn($t) => [
            'id' => $t->id, 'name' => $t->name, 'icon' => $t->icon, 'color' => $t->color,
            'sort_order' => $t->sort_order, 'is_active' => $t->is_active, 'is_system' => $t->is_system,
        ])->values();
    @endphp
    <script>
        window.typeReorderData = @json($typeReorderData);
        function typeReorder() {
            return {
                types: window.typeReorderData || [],
                dragIndex: null,
                dropTarget: null,
                saving: false,
                saved: false,
                dragStart(i) { this.dragIndex = i; },
                dragOver(i) { this.dropTarget = i; },
                dragEnd() { this.dragIndex = null; this.dropTarget = null; },
                drop(i) {
                    if (this.dragIndex === null || this.dragIndex === i) { this.dragEnd(); return; }
                    const [moved] = this.types.splice(this.dragIndex, 1);
                    this.types.splice(i, 0, moved);
                    this.dragEnd();
                    this.saveOrder();
                },
                saveOrder() {
                    this.saving = true;
                    this.saved = false;
                    fetch('{{ route('settings.handling-types.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                        },
                        body: JSON.stringify({ order: this.types.map(t => t.id) })
                    })
                    .then(r => r.json())
                    .then(() => { this.saving = false; this.saved = true; setTimeout(() => this.saved = false, 2000); })
                    .catch(() => { this.saving = false; });
                }
            };
        }
    </script>

    <div x-data="typeReorder()" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <i class="fa-solid fa-grip-vertical text-gray-300"></i>
                <span class="font-semibold">Seret baris untuk mengubah urutan</span>
            </div>
            <div class="flex items-center gap-2">
                <span x-show="saving" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600">
                    <i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...
                </span>
                <span x-show="saved" x-transition class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600">
                    <i class="fa-solid fa-circle-check"></i> Urutan tersimpan
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Urutan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Jenis Penanganan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Warna</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Aktif</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="(type, i) in types" :key="type.id">
                        <tr draggable="true"
                            @dragstart="dragStart(i)"
                            @dragover.prevent="dragOver(i)"
                            @dragenter.prevent
                            @drop.prevent="drop(i)"
                            @dragend="dragEnd()"
                            class="hover:bg-gray-50/50 transition cursor-grab active:cursor-grabbing"
                            :class="dragIndex === i ? 'opacity-40 bg-amber-50/40' : (dropTarget === i && dragIndex !== null ? 'ring-2 ring-inset ring-amber-300 bg-amber-50/30' : '')">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-grip-vertical text-gray-300"></i>
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-xs font-bold text-gray-500" x-text="i + 1"></span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm"
                                        :style="'background: linear-gradient(135deg, ' + type.color + ', ' + type.color + 'cc);'">
                                        <i :class="'fa-solid ' + type.icon" class="text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900" x-text="type.name"></p>
                                        <p class="text-[11px] text-gray-400">
                                            <span x-show="type.is_system" class="inline-flex items-center gap-1 text-amber-600 font-semibold">
                                                <i class="fa-solid fa-lock text-[9px]"></i> Sistem
                                            </span>
                                            <span x-show="!type.is_system" class="text-gray-400">Kustom</span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 text-xs font-mono text-gray-500">
                                    <span class="w-5 h-5 rounded-full border border-gray-200 shadow-sm" :style="'background-color: ' + type.color"></span>
                                    <span x-text="type.color"></span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <form :action="'/settings/handling-types/' + type.id" method="POST">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" :value="type.name">
                                    <input type="hidden" name="icon" :value="type.icon">
                                    <input type="hidden" name="color" :value="type.color">
                                    <input type="hidden" name="sort_order" :value="type.sort_order">
                                    <input type="hidden" name="is_active" :value="type.is_active ? 0 : 1">
                                    <button type="submit"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                                        :class="type.is_active ? 'bg-emerald-500' : 'bg-gray-300'">
                                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"
                                            :class="type.is_active ? 'translate-x-5' : 'translate-x-0.5'"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="openEdit(type)"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <template x-if="!type.is_system">
                                        <form :action="'/settings/handling-types/' + type.id" method="POST" class="inline"
                                            x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus jenis penanganan ini?'})) $el.submit()">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Hapus">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </template>
                                    <template x-if="type.is_system">
                                        <span class="w-8 h-8 inline-flex items-center justify-center text-gray-200" title="Terkunci sistem">
                                            <i class="fa-solid fa-lock text-xs"></i>
                                        </span>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="types.length === 0">
                        <td colspan="5" class="px-5 py-12 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-hand-holding-heart text-gray-300 text-xl"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-500">Belum ada jenis penanganan</p>
                            <p class="text-xs text-gray-400 mt-1">Tambahkan jenis penanganan pertama</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Peran Penanganan --}}
    <div class="mt-6 bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-user-tag text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-gray-900">Peran Penanganan</h3>
                    <p class="text-xs text-gray-400">Pilihan peran di form tambah penanganan</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <form action="{{ route('settings.handling-roles.store') }}" method="POST" class="flex flex-col sm:flex-row gap-3 mb-5">
                @csrf
                <input type="text" name="role" required maxlength="100" placeholder="Tambah peran baru..."
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl shadow-md shadow-orange-200 hover:brightness-105 transition active:scale-95">
                    <i class="fa-solid fa-plus"></i> Tambah Peran
                </button>
            </form>

            @php
                $roleReorderData = $roles;
            @endphp
            <script>
                window.roleReorderData = @json($roleReorderData);
                function roleReorder() {
                    return {
                        roles: window.roleReorderData || [],
                        dragIndex: null,
                        dropTarget: null,
                        saving: false,
                        saved: false,
                        dragStart(i) { this.dragIndex = i; },
                        dragOver(i) { this.dropTarget = i; },
                        dragEnd() { this.dragIndex = null; this.dropTarget = null; },
                        drop(i) {
                            if (this.dragIndex === null || this.dragIndex === i) { this.dragEnd(); return; }
                            const [moved] = this.roles.splice(this.dragIndex, 1);
                            this.roles.splice(i, 0, moved);
                            this.dragEnd();
                            this.saveOrder();
                        },
                        saveOrder() {
                            this.saving = true;
                            this.saved = false;
                            fetch('{{ route('settings.handling-roles.reorder') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                                },
                                body: JSON.stringify({ order: this.roles })
                            })
                            .then(r => r.json())
                            .then(() => { this.saving = false; this.saved = true; setTimeout(() => this.saved = false, 2000); })
                            .catch(() => { this.saving = false; });
                        }
                    };
                }
            </script>

            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-grip-vertical text-gray-300"></i> Seret untuk mengubah urutan
                </p>
                <div class="flex items-center gap-2">
                    <span x-show="saving" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600">
                        <i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...
                    </span>
                    <span x-show="saved" x-transition class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600">
                        <i class="fa-solid fa-circle-check"></i> Tersimpan
                    </span>
                </div>
            </div>

            <div x-data="roleReorder()">
                <div class="space-y-2">
                    <template x-for="(role, i) in roles" :key="role">
                        <div draggable="true"
                            @dragstart="dragStart(i)"
                            @dragover.prevent="dragOver(i)"
                            @dragenter.prevent
                            @drop.prevent="drop(i)"
                            @dragend="dragEnd()"
                            class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50/60 px-4 py-3 cursor-grab active:cursor-grabbing transition"
                            :class="dragIndex === i ? 'opacity-40 bg-amber-50/40' : (dropTarget === i && dragIndex !== null ? 'ring-2 ring-inset ring-amber-300 bg-amber-50/30' : 'hover:bg-gray-50')">
                            <i class="fa-solid fa-grip-vertical text-gray-300"></i>
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center text-white shadow-sm shrink-0">
                                <i class="fa-solid fa-user-tag text-xs"></i>
                            </div>
                            <span class="flex-1 text-sm font-bold text-gray-700" x-text="role"></span>
                            <span class="text-[10px] font-bold text-gray-300" x-text="i + 1"></span>
                            <form :action="'/settings/handling-roles/' + encodeURIComponent(role)" method="POST" class="inline"
                                x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus peran ' + role + '?'})) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition" title="Hapus">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
                <p x-show="roles.length === 0" class="text-sm text-gray-400">Belum ada peran. Tambahkan peran pertama di atas.</p>
            </div>

    <div x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl border border-gray-200 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-hand-holding-heart text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900" x-text="isEditing ? 'Edit Jenis Penanganan' : 'Tambah Jenis Penanganan'"></h3>
                            <p class="text-xs text-gray-400">Tampil di dropdown penanganan pelanggaran</p>
                        </div>
                    </div>
                    <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form :action="isEditing ? `/settings/handling-types/${editId}` : '{{ route('settings.handling-types.store') }}'"
                    method="POST" class="p-6">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="space-y-4">
                        {{-- Nama --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Jenis <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formName" name="name" required maxlength="255"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                                placeholder="contoh: Surat Panggilan">
                        </div>

                        {{-- Ikon --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ikon <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm"
                                    :style="'background: linear-gradient(135deg, ' + formColor + ', ' + formColor + 'cc);'">
                                    <i :class="'fa-solid ' + formIcon" class="text-white text-sm"></i>
                                </div>
                                <input type="text" x-model="formIcon" name="icon" required
                                    class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-mono bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                                    placeholder="fa-clipboard-list">
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="ic in icons" :key="ic">
                                    <button type="button" @click="formIcon = ic"
                                        class="w-9 h-9 rounded-lg flex items-center justify-center border transition"
                                        :class="formIcon === ic ? 'border-amber-400 bg-amber-50 text-amber-600 shadow-sm' : 'border-gray-200 text-gray-400 hover:border-gray-300 hover:text-gray-600'">
                                        <i :class="'fa-solid ' + ic" class="text-sm"></i>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Warna --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Warna <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-3">
                                <input type="color" x-model="formColor" name="color" required
                                    class="w-12 h-10 rounded-xl border border-gray-200 cursor-pointer bg-white p-1">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="c in colors" :key="c">
                                        <button type="button" @click="formColor = c"
                                            class="w-8 h-8 rounded-lg border-2 transition"
                                            :class="formColor === c ? 'border-gray-800 scale-110' : 'border-transparent hover:scale-110'"
                                            :style="'background-color: ' + c"></button>
                                    </template>
                                </div>
                                <span class="text-xs font-mono text-gray-400" x-text="formColor"></span>
                            </div>
                        </div>

                        {{-- Urutan --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Urutan</label>
                            <input type="number" x-model="formSort" name="sort_order" min="0" max="999"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                            <p class="mt-1 text-[11px] text-gray-400">Angka kecil tampil lebih dulu di dropdown</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                        <button type="button" @click="showModal = false"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-black text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl hover:brightness-105 transition shadow-md shadow-orange-200 inline-flex items-center gap-2 active:scale-95">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
