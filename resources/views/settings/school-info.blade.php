@extends('layouts.app')

@section('title', 'Informasi Sekolah')

@section('content')
@php
    $itemsData = $items->map(fn ($i) => [
        'id' => $i->id,
        'title' => $i->title,
        'category' => $i->category ?? 'umum',
        'event_date' => $i->event_date?->toDateString(),
        'is_today' => $i->event_date?->isToday(),
        'content' => $i->content ?? '',
        'is_published' => (bool) $i->is_published,
        'author' => $i->creator?->name ?? '-',
        'time' => $i->created_at->diffForHumans(),
    ])->values();

    $todayCount = $items->filter(fn ($i) => $i->event_date?->isToday())->count();
    $draftCount = $items->where('is_published', false)->count();

    $topCategoryRaw = $items->groupBy('category')->sortByDesc->count()->keys()->first();
    $topCategoryLabel = $topCategoryRaw
        ? ucfirst($topCategoryRaw)
        : '—';
@endphp

<div class="space-y-6" x-data="schoolInfoForm(@js($itemsData))">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-slate-500">
        <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition"><i class="fa-solid fa-house mr-1"></i>Dashboard</a>
        <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
        <span>Pengaturan</span>
        <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
        <span class="font-semibold text-slate-800">Informasi Sekolah</span>
    </nav>

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-600 to-slate-900 shadow-xl shadow-blue-900/10">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>
        <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -left-10 bottom-0 w-48 h-48 rounded-full bg-sky-400/20 blur-3xl"></div>
        <div class="relative px-6 py-7 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur border border-white/20 shadow-inner">
                    <i class="fa-solid fa-bullhorn text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">Informasi Sekolah</h1>
                    <p class="text-sm text-blue-100/80 mt-1">Pengumuman kegiatan sekolah yang tampil di aplikasi Wali Murid <span class="font-semibold text-white">(S1WON DIGI)</span></p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 backdrop-blur border border-white/20 text-white text-sm font-semibold">
                    <i class="fa-solid fa-newspaper text-blue-200"></i> {{ $items->count() }} informasi
                </span>
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-400/20 backdrop-blur border border-emerald-300/30 text-emerald-100 text-sm font-semibold">
                    <i class="fa-solid fa-calendar-day"></i> {{ $todayCount }} hari ini
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-center gap-2 animate-pulse">
        <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    {{-- KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $items->count() }}</div>
                <div class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">Total Informasi</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25 group-hover:scale-150 transition">
                <i class="fa-solid fa-newspaper text-white"></i>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div>
                <div class="text-2xl font-extrabold text-emerald-600">{{ $todayCount }}</div>
                <div class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">Terbit Hari Ini</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                <i class="fa-solid fa-calendar-day text-white"></i>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div>
                <div class="text-2xl font-extrabold text-amber-600">{{ $draftCount }}</div>
                <div class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">Draft</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/25">
                <i class="fa-solid fa-eye-slash text-white"></i>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div>
                <div class="text-2xl font-extrabold text-violet-600">{{ $topCategoryLabel }}</div>
                <div class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">Kategori Terbanyak</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-violet-500/25">
                <i class="fa-solid fa-layer-group text-white"></i>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="relative sm:col-span-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <input type="text" x-model="q" placeholder="Cari judul informasi..."
                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
            </div>
            <div class="relative">
                <i class="fa-solid fa-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <select x-model="cat" class="w-full pl-9 pr-8 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition appearance-none cursor-pointer">
                    <option value="">Semua Kategori</option>
                    <option value="umum">Umum</option>
                    <option value="akademik">Akademik</option>
                    <option value="kegiatan">Kegiatan</option>
                    <option value="uts">UTS</option>
                    <option value="uas">UAS</option>
                    <option value="lainnya">Lainnya</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
            </div>
            <div class="relative">
                <i class="fa-solid fa-filter absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <select x-model="status" class="w-full pl-9 pr-8 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition appearance-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="published">Terbit</option>
                    <option value="draft">Draft</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <p class="text-xs text-slate-400" x-cloak>
                Menampilkan <span class="font-semibold text-slate-600" x-text="filtered.length"></span> dari {{ $items->count() }} informasi
            </p>
            <button type="button" @click="q=''; cat=''; status=''" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline transition">
                <i class="fa-solid fa-rotate-left mr-1"></i>Reset filter
            </button>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- ===== Form ===== --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden lg:sticky lg:top-4">
                <div class="relative overflow-hidden px-5 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
                    <div class="absolute -right-4 -top-6 w-24 h-24 rounded-full bg-white/10 blur-xl"></div>
                    <h2 class="relative font-bold text-white flex items-center gap-2">
                        <i class="fa-solid" :class="id ? 'fa-pen-to-square' : 'fa-circle-plus'"></i>
                        <span x-text="id ? 'Edit Informasi' : 'Tambah Informasi'">Tambah Informasi</span>
                    </h2>
                    <button type="button" x-show="id" @click="reset()" x-cloak
                        class="relative text-xs font-medium text-blue-100 hover:text-white hover:underline transition">
                        <i class="fa-solid fa-xmark mr-0.5"></i>Batal
                    </button>
                </div>
                <form class="p-5 space-y-4" method="POST"
                      :action="id ? '{{ route('settings.school-info.update', 0) }}'.replace('/0', '/' + id) : '{{ route('settings.school-info.store') }}'">
                    @csrf
                    <input type="hidden" name="_method" :value="id ? 'PUT' : 'POST'">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Judul Informasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" x-model="title" required maxlength="200"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 transition"
                               placeholder="cth: Jadwal UTS Semester Ganjil 2026/2027">
                        <p class="text-[11px] text-slate-400 mt-1">Judul singkat yang tampil di beranda wali murid.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori</label>
                            <select name="category" x-model="category" class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 transition cursor-pointer">
                                <option value="umum">Umum</option>
                                <option value="akademik">Akademik</option>
                                <option value="kegiatan">Kegiatan</option>
                                <option value="uts">UTS</option>
                                <option value="uas">UAS</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Kegiatan</label>
                            <input type="date" name="event_date" x-model="event_date"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Isi Informasi</label>
                        <textarea name="content" x-model="content" rows="4"
                                  class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 transition resize-none"
                                  placeholder="Detail pengumuman..."></textarea>
                    </div>

                    <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 cursor-pointer hover:border-blue-200 hover:bg-blue-50/40 transition">
                        <span class="flex items-center gap-2.5 text-sm text-slate-700">
                            <i class="fa-solid fa-bullhorn text-blue-500"></i>
                            <span>
                                <span class="font-semibold">Publikasikan langsung</span>
                                <span class="block text-[11px] text-slate-400 font-normal">Wali murid langsung dapat notifikasi</span>
                            </span>
                        </span>
                        <input type="checkbox" name="is_published" x-model="is_published" value="1"
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 accent-blue-600">
                    </label>

                    <button class="w-full px-4 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-bold shadow-lg shadow-blue-500/25 transition hover:shadow-blue-600/30 active:scale-[0.99]">
                        <i class="fa-solid fa-paper-plane mr-1.5"></i>
                        <span x-text="id ? 'Simpan Perubahan' : 'Publikasikan'">Publikasikan</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- ===== Daftar ===== --}}
        <div class="lg:col-span-2 space-y-3">
            <template x-for="item in filtered" :key="item.id">
                <div class="group bg-white rounded-2xl shadow-sm border border-slate-200 p-5 hover:shadow-lg hover:shadow-blue-900/5 hover:border-blue-200 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        {{-- Ikon kategori --}}
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-lg transition-transform duration-200 group-hover:scale-105"
                             :class="catMeta[item.category]?.tile ?? catMeta.umum.tile">
                            <i class="fa-solid text-white text-lg" :class="catMeta[item.category]?.icon ?? catMeta.umum.icon"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold"
                                      :class="catMeta[item.category]?.badge ?? catMeta.umum.badge">
                                    <i class="fa-solid fa-tag"></i>
                                    <span x-text="item.category.charAt(0).toUpperCase() + item.category.slice(1)"></span>
                                </span>
                                <span x-show="item.event_date" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold">
                                    <i class="fa-regular fa-calendar"></i>
                                    <span x-text="item.event_date"></span>
                                </span>
                                <span x-show="item.is_today"
                                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold shadow-sm shadow-emerald-200">
                                    <i class="fa-solid fa-sparkles"></i> Hari ini
                                </span>
                                <span x-show="!item.is_published" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-[11px] font-semibold border border-amber-200">
                                    <i class="fa-regular fa-eye-slash"></i> Draft
                                </span>
                            </div>

                            <h3 class="font-bold text-gray-900 mt-2 group-hover:text-blue-700 transition-colors"
                                x-text="item.title"></h3>
                            <p x-show="item.content" class="text-sm text-slate-500 mt-1 line-clamp-2" x-text="item.content"></p>

                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-2 text-[11px] text-slate-400">
                                <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i class="fa-solid fa-user-tie text-[9px]"></i>
                                </span>
                                <span x-text="item.author"></span>
                                <span class="text-slate-300">•</span>
                                <span x-text="item.time"></span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5 shrink-0">
                            <button type="button" @click="edit(item)"
                                    class="w-9 h-9 rounded-xl border border-slate-200 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 active:scale-95"
                                    title="Edit">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            <form method="POST" :action="'{{ route('settings.school-info.destroy', 0) }}'.replace('/0', '/' + item.id)"
                                  onsubmit="return confirm('Hapus informasi ini?')">
                                @csrf @method('DELETE')
                                <button class="w-9 h-9 rounded-xl border border-slate-200 text-slate-400 transition hover:bg-red-50 hover:text-red-500 hover:border-red-200 active:scale-95" title="Hapus">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty state --}}
            <div x-show="filtered.length === 0" x-cloak
                 class="bg-white rounded-2xl shadow-sm border border-dashed border-slate-300 px-6 py-14 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-bullhorn text-2xl text-blue-400"></i>
                </div>
                <p class="text-gray-700 font-semibold" x-text="items.length === 0 ? 'Belum ada informasi' : 'Tidak ada hasil'"></p>
                <p class="text-sm text-gray-400 mt-1" x-text="items.length === 0 ? 'Tambahkan informasi pertama (UTS, Class Meet, kegiatan, dll) lewat form di samping.' : 'Coba ubah kata kunci atau filter.'"></p>
            </div>
        </div>
    </div>
</div>

<script>
function schoolInfoForm(items) {
    return {
        items: items ?? [],
        q: '',
        cat: '',
        status: '',
        id: null,
        title: '',
        category: 'umum',
        event_date: '',
        content: '',
        is_published: true,

        catMeta: {
            umum:      { icon: 'fa-bullhorn',     tile: 'bg-gradient-to-br from-slate-500 to-slate-700',       badge: 'bg-slate-100 text-slate-700' },
            akademik:  { icon: 'fa-graduation-cap', tile: 'bg-gradient-to-br from-blue-500 to-indigo-600',      badge: 'bg-blue-50 text-blue-700' },
            kegiatan:  { icon: 'fa-flag',         tile: 'bg-gradient-to-br from-emerald-500 to-teal-600',      badge: 'bg-emerald-50 text-emerald-700' },
            uts:       { icon: 'fa-file-pen',     tile: 'bg-gradient-to-br from-orange-400 to-amber-600',      badge: 'bg-orange-50 text-orange-700' },
            uas:       { icon: 'fa-file-lines',   tile: 'bg-gradient-to-br from-rose-500 to-red-600',          badge: 'bg-red-50 text-red-700' },
            lainnya:   { icon: 'fa-ellipsis',     tile: 'bg-gradient-to-br from-violet-500 to-fuchsia-600',    badge: 'bg-violet-50 text-violet-700' },
        },

        get filtered() {
            const q = this.q.trim().toLowerCase();
            return this.items.filter(item => {
                if (q && !item.title.toLowerCase().includes(q)) return false;
                if (this.cat && item.category !== this.cat) return false;
                if (this.status === 'published' && !item.is_published) return false;
                if (this.status === 'draft' && item.is_published) return false;
                return true;
            });
        },

        edit(item) {
            this.id = item.id;
            this.title = item.title;
            this.category = item.category;
            this.event_date = item.event_date ?? '';
            this.content = item.content ?? '';
            this.is_published = item.is_published;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        reset() {
            this.id = null;
            this.title = '';
            this.category = 'umum';
            this.event_date = '';
            this.content = '';
            this.is_published = true;
        },
    };
}
</script>
@endsection
