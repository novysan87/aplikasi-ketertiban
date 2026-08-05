@extends('layouts.app')

@section('title', 'Informasi Sekolah')

@section('content')
<div class="space-y-6" x-data="schoolInfoForm()">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-blue-700 to-slate-900 shadow-lg">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative px-6 py-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur border border-white/20">
                    <i class="fa-solid fa-bullhorn text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Informasi Sekolah</h1>
                    <p class="text-sm text-slate-300 mt-1">Pengumuman kegiatan sekolah yang tampil di aplikasi Wali Murid (S1WON DIGI)</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 backdrop-blur border border-white/20 text-white text-sm font-semibold">
                <i class="fa-solid fa-newspaper"></i> {{ $items->count() }} informasi
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- ===== Form ===== --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden lg:sticky lg:top-4">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900" x-text="id ? 'Edit Informasi' : 'Tambah Informasi'">Tambah Informasi</h2>
                    <button type="button" x-show="id" @click="reset()" class="text-xs font-medium text-blue-600 hover:underline">Batal edit</button>
                </div>
                <form class="p-5 space-y-4" method="POST"
                      :action="id ? '{{ route('settings.school-info.update', 0) }}'.replace('/0', '/' + id) : '{{ route('settings.school-info.store') }}'">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" x-show="id">

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Judul Informasi *</label>
                        <input type="text" name="title" x-model="title" required maxlength="200"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="cth: Jadwal UTS Semester Ganjil 2026/2027">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kategori</label>
                            <select name="category" x-model="category" class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="umum">Umum</option>
                                <option value="akademik">Akademik</option>
                                <option value="kegiatan">Kegiatan</option>
                                <option value="uts">UTS</option>
                                <option value="uas">UAS</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Kegiatan</label>
                            <input type="date" name="event_date" x-model="event_date"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Isi Informasi</label>
                        <textarea name="content" x-model="content" rows="4"
                                  class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Detail pengumuman..."></textarea>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_published" x-model="is_published" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Publikasikan langsung
                    </label>

                    <button class="w-full px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                        <i class="fa-solid fa-paper-plane mr-1"></i> <span x-text="id ? 'Simpan Perubahan' : 'Publikasikan'">Publikasikan</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- ===== Daftar ===== --}}
        <div class="lg:col-span-2 space-y-3">
            @forelse($items as $info)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold
                            @switch($info->category)
                                @case('uts') bg-orange-50 text-orange-700 @break
                                @case('uas') bg-red-50 text-red-700 @break
                                @case('akademik') bg-blue-50 text-blue-700 @break
                                @case('kegiatan') bg-emerald-50 text-emerald-700 @break
                                @default bg-slate-100 text-slate-600 @endswitch">
                            <i class="fa-solid fa-tag"></i> {{ ucfirst($info->category) }}
                        </span>
                        @if($info->event_date)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold">
                            <i class="fa-regular fa-calendar"></i> {{ $info->event_date->format('d M Y') }}
                        </span>
                        @endif
                        @if(!$info->is_published)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-[11px] font-semibold">
                            <i class="fa-regular fa-eye-slash"></i> Draft
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" @click="edit({{ json_encode([
                            'id' => $info->id,
                            'title' => $info->title,
                            'category' => $info->category,
                            'event_date' => $info->event_date?->toDateString() ?? '',
                            'content' => $info->content ?? '',
                            'is_published' => $info->is_published,
                        ]) }})" class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-500 transition" title="Edit">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <form method="POST" action="{{ route('settings.school-info.destroy', $info->id) }}" onsubmit="return confirm('Hapus informasi ini?')">
                            @csrf @method('DELETE')
                            <button class="w-8 h-8 rounded-lg hover:bg-red-50 text-red-400 transition" title="Hapus">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <h3 class="font-semibold text-gray-900 mt-3">{{ $info->title }}</h3>
                @if($info->content)
                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $info->content }}</p>
                @endif
                <p class="text-[11px] text-slate-400 mt-2">
                    Diposting oleh {{ $info->creator?->name ?? '-' }} · {{ $info->created_at->diffForHumans() }}
                </p>
            </div>
            @empty
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-14 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-bullhorn text-2xl text-blue-400"></i>
                </div>
                <p class="text-gray-700 font-medium">Belum ada informasi</p>
                <p class="text-sm text-gray-400 mt-1">Tambahkan informasi pertama (UTS, Class Meet, kegiatan, dll) lewat form di samping.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function schoolInfoForm() {
    return {
        id: null,
        title: '',
        category: 'umum',
        event_date: '',
        content: '',
        is_published: true,
        edit(item) {
            this.id = item.id;
            this.title = item.title;
            this.category = item.category;
            this.event_date = item.event_date ?? '';
            this.content = item.content ?? '';
            this.is_published = item.is_published;
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
