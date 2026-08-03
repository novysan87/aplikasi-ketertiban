@extends('layouts.app')

@section('title', 'Registrasi Wajah')

@section('content')
<div class="pb-10">
    {{-- ===== HEADER PREMIUM ===== --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 via-violet-500 to-fuchsia-400 shadow-xl shadow-indigo-500/20 px-6 py-6 mb-6">
        <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -bottom-20 left-1/3 w-48 h-48 rounded-full bg-fuchsia-300/20 blur-2xl"></div>
        <div class="absolute top-0 right-24 w-24 h-24 rounded-full border border-white/10"></div>

        <div class="relative flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center shadow-inner ring-1 ring-white/20">
                <i class="fa-solid fa-user-plus text-white text-2xl"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-white tracking-tight">Registrasi Wajah Siswa</h2>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white/90 tracking-wider uppercase">Face ID</span>
                </div>
                <p class="text-sm text-white/75 mt-0.5">Daftarkan foto wajah ke siswa yang sudah ada di database — Master Data</p>
            </div>
        </div>
    </div>

    {{-- ===== LANGKAH ===== --}}
    <div class="flex items-center justify-center gap-2 mb-6">
        @foreach (['Cari Siswa', 'Ambil Foto', 'Simpan'] as $i => $step)
            @php($active = ($i === 0) || ($selected && $i === 1))
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 {{ $active ? 'bg-indigo-500 text-white shadow-md shadow-indigo-500/30' : 'bg-gray-100 text-gray-400' }}">
                        {{ $i + 1 }}
                    </div>
                    <span class="text-xs font-semibold {{ $active ? 'text-indigo-600' : 'text-gray-300' }}">{{ $step }}</span>
                </div>
                @if ($i < 2)
                    <i class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
                @endif
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-5 gap-6 items-start">
        {{-- ===== KIRI: CARI + DAFTAR SISWA ===== --}}
        <div class="lg:col-span-3 space-y-5">
            {{-- Cari --}}
            <form method="GET" action="{{ route('face.register') }}" class="flex items-center gap-3">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari NISN / nomor / nama siswa..."
                        class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-2xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition shadow-sm">
                </div>
                <button class="px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-indigo-500 to-violet-500 rounded-2xl hover:from-indigo-600 hover:to-violet-600 active:scale-95 transition shadow-lg shadow-indigo-500/25 inline-flex items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
            </form>

            {{-- Daftar siswa --}}
            <div class="bg-white rounded-3xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <p class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <i class="fa-solid fa-users text-indigo-500"></i> Daftar Siswa
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600">{{ $students->total() }} siswa</span>
                    </p>
                    <span class="text-[11px] text-gray-400 font-medium">Status wajah: <span class="text-emerald-600 font-bold">Terdaftar</span> / <span class="text-gray-500 font-bold">Belum</span></span>
                </div>

                @if ($students->count())
                    {{-- Desktop: tabel --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50/70 text-left text-[11px] text-gray-400 uppercase tracking-wider">
                                    <th class="px-6 py-3 font-bold">Nama</th>
                                    <th class="px-4 py-3 font-bold">NISN</th>
                                    <th class="px-4 py-3 font-bold">Kelas</th>
                                    <th class="px-4 py-3 font-bold">Status Wajah</th>
                                    <th class="px-6 py-3 font-bold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($students as $student)
                                    @php($registered = ($enrolledMap[(string) $student->id] ?? 0) > 0)
                                    <tr class="hover:bg-indigo-50/30 transition {{ $selected && $selected->id === $student->id ? 'bg-indigo-50/50' : '' }}">
                                        <td class="px-6 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white flex items-center justify-center font-bold text-xs shadow-sm shrink-0">
                                                    {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                                </div>
                                                <span class="font-semibold text-gray-800">{{ $student->full_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-gray-500 font-medium">{{ $student->nisn }}</td>
                                        <td class="px-4 py-3.5">
                                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">{{ $student->class_name }}</span>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @if ($registered)
                                                <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600">
                                                    <i class="fa-solid fa-check"></i> Terdaftar
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                                    <i class="fa-solid fa-circle-minus"></i> Belum
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-right">
                                            <a href="{{ route('face.register', ['student_id' => $student->id, 'q' => $q]) }}"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-xl border transition {{ $registered ? 'text-indigo-600 border-indigo-200 bg-white hover:bg-indigo-50' : 'text-white bg-gradient-to-r from-indigo-500 to-violet-500 border-transparent hover:from-indigo-600 hover:to-violet-600 shadow-md shadow-indigo-500/20' }}">
                                                <i class="fa-solid fa-camera"></i> {{ $registered ? 'Perbarui' : 'Registrasi' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile: kartu (tanpa scroll samping) --}}
                    <div class="md:hidden divide-y divide-gray-50">
                        @foreach ($students as $student)
                            @php($registered = ($enrolledMap[(string) $student->id] ?? 0) > 0)
                            <div class="p-4 flex items-center gap-3 {{ $selected && $selected->id === $student->id ? 'bg-indigo-50/50' : '' }}">
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                    {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $student->full_name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $student->nisn }} · {{ $student->class_name }}</p>
                                    <div class="mt-1.5">
                                        @if ($registered)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600">
                                                <i class="fa-solid fa-check"></i> Wajah Terdaftar
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                                                <i class="fa-solid fa-circle-minus"></i> Belum Terdaftar
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('face.register', ['student_id' => $student->id, 'q' => $q]) }}"
                                    class="shrink-0 inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2.5 rounded-xl transition {{ $registered ? 'text-indigo-600 bg-indigo-50 hover:bg-indigo-100' : 'text-white bg-gradient-to-r from-indigo-500 to-violet-500 hover:from-indigo-600 hover:to-violet-600 shadow-md shadow-indigo-500/20' }}">
                                    <i class="fa-solid fa-camera"></i> {{ $registered ? 'Perbarui' : 'Registrasi' }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center">
                        <i class="fa-solid fa-user-slash text-3xl text-gray-200 mb-3 block"></i>
                        <p class="text-gray-400 text-sm font-medium">Siswa tidak ditemukan</p>
                        <p class="text-xs text-gray-300 mt-1">Coba kata kunci lain</p>
                    </div>
                @endif

                <div class="px-6 py-4 border-t border-gray-50">{{ $students->links() }}</div>
            </div>
        </div>

        {{-- ===== KANAN: KAMERA + FORM ===== --}}
        <div class="lg:col-span-2 space-y-5 lg:sticky lg:top-6">
            @if ($selected)
                <div class="bg-white rounded-3xl shadow-lg shadow-indigo-500/5 ring-1 ring-gray-100 overflow-hidden" x-data="faceRegister()">
                    {{-- Siswa terpilih --}}
                    <div class="bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-400 px-6 py-4 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur text-white flex items-center justify-center font-extrabold text-lg shadow-inner ring-1 ring-white/20">
                            {{ strtoupper(substr($selected->full_name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-bold truncate">{{ $selected->full_name }}</p>
                            <p class="text-indigo-50/90 text-xs">{{ $selected->nisn }} · {{ $selected->class_name }}</p>
                        </div>
                        <a href="{{ route('face.register', ['q' => $q]) }}" class="w-9 h-9 rounded-full bg-white/15 backdrop-blur ring-1 ring-white/25 text-white flex items-center justify-center hover:bg-white/25 active:scale-90 transition" title="Ganti siswa">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    </div>

                    <form action="{{ route('face.register.store') }}" method="POST" enctype="multipart/form-data"
                        class="p-6 space-y-5" @submit.prevent="if (shots.length) $el.submit()">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $selected->id }}">
                        <input type="hidden" name="q" value="{{ $q }}">
                        <input type="file" id="photos" name="photos[]" multiple accept="image/*" class="hidden" @change="onGallery">

                        {{-- Viewfinder kamera --}}
                        <div class="relative aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-gray-900 to-gray-950 ring-1 ring-gray-800/60 shadow-2xl shadow-gray-900/20">
                            {{-- Grid --}}
                            <div class="absolute inset-0 pointer-events-none opacity-20">
                                <div class="absolute left-1/3 top-0 bottom-0 w-px bg-white/30"></div>
                                <div class="absolute left-2/3 top-0 bottom-0 w-px bg-white/30"></div>
                                <div class="absolute top-1/3 left-0 right-0 h-px bg-white/30"></div>
                                <div class="absolute top-2/3 left-0 right-0 h-px bg-white/30"></div>
                            </div>

                            {{-- Belum buka kamera --}}
                            <template x-if="!cameraOn && !shots.length">
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-6">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-fuchsia-400/10 ring-1 ring-white/15 backdrop-blur flex items-center justify-center mb-3 shadow-xl">
                                        <i class="fa-solid fa-video text-white text-2xl"></i>
                                    </div>
                                    <p class="text-white font-bold">Buka Kamera</p>
                                    <p class="text-gray-400 text-xs mt-1">Pastikan wajah siswa terlihat jelas</p>
                                    <button type="button" @click="startCamera()"
                                        class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-500 to-fuchsia-500 rounded-xl hover:from-indigo-600 hover:to-fuchsia-600 active:scale-95 transition shadow-lg shadow-indigo-500/30">
                                        <i class="fa-solid fa-camera"></i> Buka Kamera
                                    </button>
                                </div>
                            </template>

                            <video x-ref="video" x-show="cameraOn" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover"></video>
                            <canvas x-ref="overlay" x-show="cameraOn" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>

                            {{-- Sudah ada foto & kamera mati --}}
                            <template x-if="shots.length && !cameraOn">
                                <div class="absolute inset-0 bg-gray-950/80 backdrop-blur-sm flex flex-col items-center justify-center text-center">
                                    <i class="fa-solid fa-circle-check text-emerald-400 text-4xl mb-2"></i>
                                    <p class="text-white font-bold text-sm" x-text="shots.length + ' dari 3 foto diambil'"></p>
                                    <button type="button" @click="startCamera()" class="mt-3 text-xs font-bold text-indigo-300 hover:text-white transition">Tambah foto lagi</button>
                                </div>
                            </template>

                            {{-- Corner brackets --}}
                            <template x-if="cameraOn">
                                <div class="absolute inset-3 pointer-events-none">
                                    <div class="absolute top-0 left-0 w-7 h-7 border-t-[3px] border-l-[3px] border-white/70 rounded-tl-2xl"></div>
                                    <div class="absolute top-0 right-0 w-7 h-7 border-t-[3px] border-r-[3px] border-white/70 rounded-tr-2xl"></div>
                                    <div class="absolute bottom-0 left-0 w-7 h-7 border-b-[3px] border-l-[3px] border-white/70 rounded-bl-2xl"></div>
                                    <div class="absolute bottom-0 right-0 w-7 h-7 border-b-[3px] border-r-[3px] border-white/70 rounded-br-2xl"></div>
                                </div>
                            </template>

                            {{-- Status pill + ganti kamera --}}
                            <template x-if="cameraOn">
                                <div class="absolute top-3 inset-x-3 flex items-center justify-between">
                                    <span id="faceHint"
                                        class="text-[10px] font-bold tracking-wider px-3 py-1.5 rounded-full backdrop-blur-md transition-all duration-300 bg-red-500/80 text-white shadow-lg shadow-red-500/30">CARI WAJAH…</span>
                                    <button type="button" @click="switchCamera()"
                                        class="w-8 h-8 rounded-full bg-white/10 backdrop-blur ring-1 ring-white/20 text-white flex items-center justify-center hover:bg-white/20 active:scale-90 transition shadow-lg">
                                        <i class="fa-solid fa-camera-rotate text-xs"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Tombol kamera --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <template x-if="cameraOn && shots.length < 3">
                                <button type="button" id="btnCapture" @click="capture()" disabled
                                    class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl hover:from-emerald-600 hover:to-teal-600 active:scale-95 transition shadow-md shadow-emerald-500/25 disabled:opacity-40 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-camera"></i> <span x-text="'Ambil Foto ' + (shots.length + 1)"></span>
                                </button>
                            </template>
                            <button type="button" x-show="cameraOn" @click="stopCamera()"
                                class="inline-flex items-center gap-2 px-3 py-2.5 text-xs font-medium text-gray-500 bg-white border border-slate-200 rounded-xl hover:bg-gray-50 transition">
                                <i class="fa-solid fa-xmark"></i> Tutup
                            </button>
                            <label class="inline-flex items-center gap-2 px-3 py-2.5 text-xs font-medium text-gray-600 bg-white border border-slate-200 rounded-xl hover:bg-gray-50 transition cursor-pointer">
                                <i class="fa-solid fa-folder-open"></i> Galeri
                                <input type="file" class="hidden" accept="image/*" @change="onGalleryAlt">
                            </label>
                        </div>
                        <p x-show="cameraError" class="text-xs font-semibold text-red-500" x-text="cameraError"></p>

                        {{-- Hasil tangkapan --}}
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Foto Diambil (1–3)</p>
                            <div class="grid grid-cols-3 gap-2.5">
                                <template x-for="(shot, i) in shots" :key="i">
                                    <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 group shadow-sm">
                                        <img :src="shot.url" class="w-full h-full object-cover">
                                        <button type="button" @click="removeShot(i)" title="Hapus foto ini"
                                            class="absolute top-1.5 right-1.5 w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600 active:scale-90 transition shadow-md md:opacity-0 md:group-hover:opacity-100">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        <span class="absolute bottom-1 left-1 text-[9px] font-bold px-1.5 py-0.5 rounded bg-black/60 text-white" x-text="'Foto ' + (i + 1)"></span>
                                    </div>
                                </template>
                                <template x-for="n in (3 - shots.length)" :key="'ph' + n">
                                    <div class="aspect-square rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center text-gray-300 bg-gray-50/50">
                                        <i class="fa-solid fa-image text-xl"></i>
                                    </div>
                                </template>
                                <template x-if="!shots.length">
                                    <div class="col-span-3 py-6 text-center text-xs text-gray-400 rounded-xl border border-dashed border-slate-200">
                                        <i class="fa-solid fa-camera-retro text-2xl mb-2 block text-gray-300"></i>
                                        Belum ada foto — ambil dari kamera atau galeri
                                    </div>
                                </template>
                            </div>
                            <p class="mt-2 text-[11px] text-gray-400">Disarankan 3: frontal netral, sedikit miring, kondisi berbeda. Foto buram/gelap/wajah kecil otomatis ditolak sistem.</p>
                            <p x-show="formError" class="mt-2 text-xs font-semibold text-red-500" x-text="formError"></p>
                            @error('photos.*') <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p> @enderror
                        </div>

                        {{-- Submit --}}
                        <button type="submit" :disabled="!shots.length"
                            class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-500 to-violet-500 rounded-2xl hover:from-indigo-600 hover:to-violet-600 active:scale-[0.98] transition shadow-lg shadow-indigo-500/25 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-user-plus"></i> Simpan Registrasi Wajah
                        </button>
                    </form>
                </div>
            @else
                {{-- Placeholder: belum pilih siswa --}}
                <div class="bg-white rounded-3xl shadow-sm ring-1 ring-gray-100 p-10 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-indigo-100 to-violet-100 flex items-center justify-center mb-4">
                        <i class="fa-solid fa-hand-pointer text-indigo-400 text-2xl"></i>
                    </div>
                    <p class="font-bold text-gray-700">Pilih Siswa Terlebih Dahulu</p>
                    <p class="text-xs text-gray-400 mt-1.5 max-w-xs mx-auto">Cari siswa di kolom pencarian, lalu klik tombol <b>Registrasi</b> untuk mulai mengambil foto wajah</p>
                </div>
            @endif

            @if (!$faceidConfigured)
                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-sm text-red-600 flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i> Microservice FaceID belum dikonfigurasi (FACEID_BASE_URL / FACEID_API_KEY di .env).
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module" src="/vendor/mediapipe/facefinder.js"></script>
<script>
function faceRegister() {
    return {
        cameraOn: false, cameraError: null, formError: null, detectorError: false, facing: 'environment',
        shots: [],

        init() {
            window.addEventListener('beforeunload', () => this.stopCamera());
            window.addEventListener('face-detector-error', () => {
                this.detectorError = true;
                const btn = document.getElementById('btnCapture');
                if (btn) { btn.disabled = false; btn.classList.remove('opacity-40', 'cursor-not-allowed'); }
                const hint = document.getElementById('faceHint');
                if (hint) {
                    hint.textContent = 'DETEKSI OFF — FOTO TETAP BISA';
                    hint.className = 'text-[10px] font-bold tracking-wider px-3 py-1.5 rounded-full backdrop-blur-md transition-all duration-300 bg-amber-500/80 text-white shadow-lg shadow-amber-500/30';
                }
            });
        },

        async startCamera() {
            this.cameraError = null;
            if (!navigator.mediaDevices?.getUserMedia) {
                this.cameraError = 'Perangkat ini tidak mendukung kamera langsung — gunakan tombol Galeri.';
                return;
            }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: this.facing, width: { ideal: 1280 } },
                    audio: false,
                });
                this.$refs.video.srcObject = stream;
                this.cameraOn = true;
                await this.$refs.video.play().catch(() => {});
                if (window.__faceLoop) window.__faceLoop.start(this.$refs.video, this.$refs.overlay);
            } catch (e) {
                this.cameraError = 'Kamera tidak bisa diakses — izinkan akses kamera di browser, atau gunakan tombol Galeri.';
            }
        },

        stopCamera() {
            if (window.__faceLoop) window.__faceLoop.stop();
            const v = this.$refs.video;
            if (v && v.srcObject) {
                v.srcObject.getTracks().forEach(t => t.stop());
                v.srcObject = null;
            }
            this.cameraOn = false;
        },

        async switchCamera() {
            const prev = this.facing;
            this.facing = this.facing === 'user' ? 'environment' : 'user';
            this.stopCamera();
            this.cameraOn = false;
            await this.startCamera();
            if (this.cameraError) {
                this.cameraError = null;
                this.facing = prev;
                this.stopCamera();
                this.cameraOn = false;
                await this.startCamera();
            }
        },

        capture() {
            if (!this.detectorError && window.__faceLoop && !window.__faceLoop.detected) {
                this.cameraError = 'Tidak ada wajah terdeteksi — arahkan kamera ke wajah siswa.';
                return;
            }
            const v = this.$refs.video;
            if (!v || !v.srcObject || v.videoWidth === 0) return;
            if (this.shots.length >= 3) return;
            const canvas = document.createElement('canvas');
            canvas.width = v.videoWidth;
            canvas.height = v.videoHeight;
            canvas.getContext('2d').drawImage(v, 0, 0);
            canvas.toBlob((blob) => {
                if (!blob) return;
                this.addShot(new File([blob], 'foto-' + Date.now() + '.jpg', { type: 'image/jpeg' }));
            }, 'image/jpeg', 0.92);
        },

        addShot(file) {
            if (this.shots.length >= 3) return;
            this.shots.push({ url: URL.createObjectURL(file), file });
            this.formError = null;
            this.syncInput();
            if (this.shots.length >= 3) this.stopCamera();
        },

        removeShot(i) {
            URL.revokeObjectURL(this.shots[i].url);
            this.shots.splice(i, 1);
            this.syncInput();
        },

        syncInput() {
            const input = document.getElementById('photos');
            if (!input || !window.DataTransfer) return;
            const dt = new DataTransfer();
            this.shots.forEach(s => dt.items.add(s.file));
            input.files = dt.files;
        },

        onGallery(e) {
            Array.from(e.target.files).slice(0, 3 - this.shots.length).forEach(f => this.addShot(f));
            e.target.value = '';
        },

        onGalleryAlt(e) {
            this.onGallery(e);
        },
    };
}
</script>
@endpush
