@extends('layouts.app')

@section('title', 'Scan Wajah')

@section('content')
<div class="max-w-3xl mx-auto pb-10">
    {{-- ===== HEADER PREMIUM ===== --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-500 to-cyan-400 shadow-xl shadow-blue-500/20 px-6 py-6 mb-6">
        {{-- Dekorasi --}}
        <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -bottom-20 left-1/3 w-48 h-48 rounded-full bg-cyan-300/20 blur-2xl"></div>
        <div class="absolute top-0 right-24 w-24 h-24 rounded-full border border-white/10"></div>

        <div class="relative flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center shadow-inner ring-1 ring-white/20">
                <i class="fa-solid fa-camera-retro text-white text-2xl"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-white tracking-tight">Scan Wajah Siswa</h2>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white/90 tracking-wider uppercase">Face ID</span>
                </div>
                <p class="text-sm text-white/75 mt-0.5">Hadapkan kamera ke wajah siswa — sistem mengenali identitasnya</p>
            </div>
        </div>
    </div>

    {{-- ===== LANGKAH PROGRES ===== --}}
    <div class="flex items-center justify-center gap-2 mb-6" x-data="{ step: 1 }" x-init="step = result ? 3 : (preview || loading ? 2 : 1)"
        x-effect="step = result ? 3 : (preview || loading ? 2 : 1)">
        <template x-for="(s, i) in ['Ambil Foto', 'Verifikasi', 'Catat Pelanggaran']" :key="i">
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300"
                        :class="step > i ? 'bg-blue-500 text-white shadow-md shadow-blue-500/30' : (step === i + 1 ? 'bg-blue-100 text-blue-600 ring-2 ring-blue-500/40' : 'bg-gray-100 text-gray-400')">
                        <i :class="step > i ? 'fa-solid fa-check' : ''" x-show="step > i"></i>
                        <span x-show="step <= i" x-text="i + 1"></span>
                    </div>
                    <span class="text-xs font-semibold hidden sm:block transition-colors" :class="step === i + 1 ? 'text-blue-600' : (step > i ? 'text-gray-500' : 'text-gray-300')" x-text="s"></span>
                </div>
                <i x-show="i < 2" class="fa-solid fa-chevron-right text-[10px] text-gray-300"></i>
            </div>
        </template>
    </div>

    <div class="space-y-6" x-data="faceScan()">
        {{-- ===== VIEWFINDER KAMERA ===== --}}
        <div class="relative">
            <div class="relative aspect-[4/3] rounded-3xl overflow-hidden bg-gradient-to-br from-gray-900 to-gray-950 shadow-2xl shadow-gray-900/30 ring-1 ring-gray-800/60"
                :class="autoScanning ? 'ring-2 ring-cyan-400/70 shadow-cyan-500/10' : ''">
                {{-- Grid rule-of-thirds --}}
                <div class="absolute inset-0 pointer-events-none opacity-20">
                    <div class="absolute left-1/3 top-0 bottom-0 w-px bg-white/30"></div>
                    <div class="absolute left-2/3 top-0 bottom-0 w-px bg-white/30"></div>
                    <div class="absolute top-1/3 left-0 right-0 h-px bg-white/30"></div>
                    <div class="absolute top-2/3 left-0 right-0 h-px bg-white/30"></div>
                </div>

                {{-- Belum buka kamera --}}
                <template x-if="!cameraOn && !preview && !loading">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                        <div class="absolute -top-10 -left-10 w-40 h-40 rounded-full bg-blue-500/10 blur-2xl"></div>
                        <div class="absolute -bottom-12 -right-8 w-44 h-44 rounded-full bg-cyan-400/10 blur-2xl"></div>
                        <div class="relative w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-500/20 to-cyan-400/10 ring-1 ring-white/15 backdrop-blur flex items-center justify-center mb-4 shadow-xl">
                            <i class="fa-solid fa-video text-white text-3xl"></i>
                        </div>
                        <p class="relative text-white font-bold text-lg">Buka Kamera</p>
                        <p class="relative text-gray-400 text-sm mt-1 max-w-xs">Izinkan akses kamera untuk deteksi wajah real-time</p>
                        <button type="button" @click="startCamera()"
                            class="relative mt-5 inline-flex items-center gap-2.5 px-7 py-3 text-sm font-bold text-white bg-gradient-to-r from-blue-500 to-cyan-400 rounded-2xl hover:from-blue-600 hover:to-cyan-500 active:scale-95 transition shadow-lg shadow-blue-500/30">
                            <i class="fa-solid fa-camera"></i> Buka Kamera
                        </button>
                    </div>
                </template>

                {{-- Video live --}}
                <video x-ref="video" x-show="cameraOn && !preview" autoplay playsinline muted
                    class="absolute inset-0 w-full h-full object-cover"></video>
                <canvas x-ref="overlay" x-show="cameraOn && !preview" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>

                {{-- Hasil tangkapan --}}
                <template x-if="preview">
                    <img :src="preview" class="absolute inset-0 w-full h-full object-cover">
                </template>

                {{-- Loading scan --}}
                <template x-if="loading">
                    <div class="absolute inset-0 bg-gray-950/70 backdrop-blur-sm flex flex-col items-center justify-center">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-full border-4 border-white/10 border-t-cyan-400 animate-spin"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <i class="fa-solid fa-camera-retro text-cyan-300 text-xl animate-pulse"></i>
                            </div>
                        </div>
                        <p class="text-white font-semibold mt-4 text-sm">Mengenali wajah...</p>
                        <p class="text-gray-400 text-xs mt-1">Membandingkan dengan database siswa</p>
                    </div>
                </template>

                {{-- Corner brackets viewfinder --}}
                <template x-if="cameraOn && !preview">
                    <div class="absolute inset-3 pointer-events-none">
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-[3px] border-l-[3px] border-white/70 rounded-tl-2xl"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-[3px] border-r-[3px] border-white/70 rounded-tr-2xl"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-[3px] border-l-[3px] border-white/70 rounded-bl-2xl"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-[3px] border-r-[3px] border-white/70 rounded-br-2xl"></div>
                    </div>
                </template>

                {{-- Status pill + kamera switch (overlay atas) --}}
                <template x-if="cameraOn && !preview">
                    <div class="absolute top-4 inset-x-4 flex items-center justify-between">
                        <span id="faceHint"
                            class="text-[11px] font-bold tracking-wider px-3.5 py-1.5 rounded-full backdrop-blur-md transition-all duration-300 bg-red-500/80 text-white shadow-lg shadow-red-500/30">CARI WAJAH…</span>
                        <div class="flex items-center gap-2">
                            <span x-show="autoScanOn"
                                class="flex items-center gap-1.5 text-[10px] font-bold tracking-widest px-2.5 py-1.5 rounded-full bg-cyan-500/90 text-white shadow-lg shadow-cyan-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> AUTO
                            </span>
                            <button type="button" @click="switchCamera()"
                                class="w-9 h-9 rounded-full bg-white/10 backdrop-blur ring-1 ring-white/20 text-white flex items-center justify-center hover:bg-white/20 active:scale-90 transition shadow-lg">
                                <i class="fa-solid fa-camera-rotate text-sm"></i>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Shutter + tutup kamera (overlay bawah) --}}
                <template x-if="cameraOn && !preview">
                    <div class="absolute bottom-4 inset-x-0 flex items-center justify-center">
                        <div class="flex items-center gap-6">
                            <button type="button" @click="stopCamera()"
                                class="w-11 h-11 rounded-full bg-white/10 backdrop-blur ring-1 ring-white/20 text-white/80 flex items-center justify-center hover:bg-white/20 active:scale-90 transition" title="Tutup kamera">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <button type="button" id="btnCapture" @click="capture()" disabled
                                class="w-[72px] h-[72px] rounded-full bg-white/15 backdrop-blur ring-4 ring-white/60 flex items-center justify-center hover:ring-white/80 active:scale-95 transition disabled:opacity-50 disabled:ring-white/30">
                                <span class="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-lg shadow-emerald-500/40 ring-2 ring-white/50"></span>
                            </button>
                            <label class="w-11 h-11 rounded-full bg-white/10 backdrop-blur ring-1 ring-white/20 text-white/80 flex items-center justify-center hover:bg-white/20 active:scale-90 transition cursor-pointer" title="Pilih dari galeri">
                                <i class="fa-solid fa-images"></i>
                                <input type="file" class="hidden" accept="image/*" @change="onFile">
                            </label>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Petunjuk kecil --}}
            <div class="flex items-center justify-center gap-2 mt-3 text-[11px] text-gray-400 min-h-[18px]">
                <i class="fa-solid fa-lightbulb text-amber-400"></i>
                <span x-show="cameraOn && !preview && !engineOk" class="text-red-500 font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Mesin deteksi tidak termuat — muat ulang halaman</span>
                <span x-show="!preview && !autoScanning && !autoNoMatch && !needBlink && !nudge && engineOk">Scan otomatis aktif — arahkan kamera ke wajah, hasil muncul sendiri</span>
                <span x-show="autoScanning" class="text-cyan-500 font-semibold"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i>Mencocokkan dengan database…</span>
                <span x-show="autoNoMatch && !autoScanning && !preview && !result" class="text-amber-500 font-semibold"><i class="fa-solid fa-eye-slash mr-1"></i>Belum dikenali — posisikan wajah lebih jelas</span>
                <span x-show="preview && !result">Foto diambil — sentuh <b class="text-gray-500">Scan Lagi</b> untuk mengambil ulang</span>
            </div>
        </div>

        {{-- Tombol aksi saat preview (setelah capture / dari galeri) --}}
        <template x-if="preview && !loading">
            <div class="flex items-center justify-center gap-3">
                <button type="button" @click="captureAgain()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-slate-200 rounded-2xl hover:bg-gray-50 active:scale-95 transition shadow-sm">
                    <i class="fa-solid fa-rotate text-gray-400"></i> Ambil Ulang
                </button>
                <button type="button" @click="scan()"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl hover:from-emerald-600 hover:to-teal-600 active:scale-95 transition shadow-lg shadow-emerald-500/25">
                    <i class="fa-solid fa-camera-retro"></i> Scan Sekarang
                </button>
            </div>
        </template>

        {{-- Error kamera --}}
        <div x-show="cameraError" class="p-4 rounded-2xl bg-red-50 border border-red-200 text-sm text-red-600 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i> <span x-text="cameraError"></span>
        </div>
        <div x-show="error && !cameraError" class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-sm text-amber-700 flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i> <span x-text="error"></span>
        </div>
        <div x-show="livenessOff" class="p-4 rounded-2xl bg-orange-50 border border-orange-200 text-sm font-semibold text-orange-600 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i> Mesin liveness tidak aktif di perangkat ini — hasil scan memerlukan konfirmasi manual staff.
        </div>

        {{-- ===== HASIL: SISWA DIKENALI ===== --}}
        <template x-if="result && result.matched && result.candidates.length">
            <div class="overflow-hidden rounded-3xl bg-white shadow-xl shadow-emerald-500/5 ring-1 ring-emerald-100">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-500 to-teal-400 px-6 py-4 flex items-center gap-3">
                    <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="absolute bottom-0 right-16 w-20 h-20 rounded-full border border-white/15 pointer-events-none"></div>
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center shadow-inner ring-1 ring-white/30">
                        <i class="fa-solid fa-circle-check text-white text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-white font-bold text-base">Siswa Dikenali!</p>
                        <p class="text-emerald-50/90 text-xs">Identitas cocok dengan database siswa</p>
                    </div>
                    <div class="flex flex-col items-end gap-1.5">
                        <span x-show="!livenessWarn" class="flex items-center gap-1 text-[10px] font-bold tracking-wider px-2.5 py-1 rounded-full bg-emerald-700/40 text-white">
                            <i class="fa-solid fa-eye"></i> LIVENESS OK · <span x-text="(signalCount || 1) + ' sinyal'"></span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-bold tracking-wider px-3 py-1.5 rounded-full bg-white/15 text-white ring-1 ring-white/30 backdrop-blur">
                            <i class="fa-solid fa-user-check"></i> TERVERIFIKASI
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <p x-show="result.ambiguous" class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-700 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Hasil ragu (kemungkinan kembar) — pilih siswa yang benar di bawah
                    </p>
                    <p x-show="livenessWarn" class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-700 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Liveness tidak terverifikasi — pastikan siswa asli berada di depan kamera (bukan foto).
                    </p>
                    <label x-show="livenessWarn" class="flex items-center gap-2.5 p-3.5 rounded-2xl bg-amber-50/60 border border-amber-200 text-xs font-semibold text-amber-800 cursor-pointer">
                        <input type="checkbox" x-model="confirmLiveness" class="w-4 h-4 rounded accent-amber-600">
                        Saya konfirmasi siswa asli berada di depan kamera saat scan ini
                    </label>

                    {{-- Kandidat --}}
                    <div class="space-y-3">
                        <template x-for="c in result.candidates" :key="c.id">
                            <div class="rounded-2xl p-4 transition-all duration-200 cursor-pointer border-2"
                                :class="c.id === chosenId ? 'border-emerald-400 bg-emerald-50/50 shadow-lg shadow-emerald-500/10' : 'border-gray-100 bg-white hover:border-slate-200'"
                                @click="result.candidates.length > 1 && (chosenId = c.id)">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-500 text-white flex items-center justify-center font-extrabold text-lg shadow-md shrink-0"
                                        x-text="c.full_name.charAt(0).toUpperCase()"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-800 truncate" x-text="c.full_name"></p>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="c.nisn + ' · ' + c.class_name"></p>
                                    </div>
                                    <div class="relative w-14 h-14 shrink-0">
                                        <svg class="w-14 h-14 -rotate-90" viewBox="0 0 56 56">
                                            <circle cx="28" cy="28" r="24" fill="none" stroke="#e2e8f0" stroke-width="5"></circle>
                                            <circle cx="28" cy="28" r="24" fill="none" stroke="#10b981" stroke-width="5" stroke-linecap="round"
                                                :stroke-dasharray="150.8" :stroke-dashoffset="150.8 * (1 - c.score)"></circle>
                                        </svg>
                                        <div class="absolute inset-0 flex items-center justify-center text-[11px] font-black" :class="(c.score*100) >= 95 ? 'text-emerald-600' : 'text-amber-600'" x-text="Math.round(c.score*100) + '%'"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-50" x-show="result.candidates.length > 1">
                                    <span class="text-xs text-gray-400"><i class="fa-solid fa-hand-pointer mr-1"></i>Pilih kandidat yang benar:</span>
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg"
                                        :class="c.id === chosenId ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500'"
                                        x-text="c.id === chosenId ? '✓ Dipilih' : 'Ketuk untuk pilih'"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Profil + poin --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 p-3.5 text-center shadow-md shadow-indigo-500/20">
                            <div class="absolute right-0 top-0 w-10 h-10 opacity-15"><i class="fa-solid fa-chart-simple text-3xl"></i></div>
                            <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-wider">Total Poin</p>
                            <p class="text-xl font-extrabold text-white mt-1" x-text="selectedCandidate ? selectedCandidate.total_points : 0"></p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3.5 text-center">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kelas</p>
                            <p class="text-sm font-bold text-gray-700 mt-1 truncate px-1" x-text="selectedCandidate ? (selectedCandidate.class_name || '—') : '—'"></p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3.5 text-center">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">NISN</p>
                            <p class="text-sm font-bold text-gray-700 mt-1 truncate px-1" x-text="selectedCandidate ? (selectedCandidate.nisn || '—') : '—'"></p>
                        </div>
                    </div>

                    {{-- Pelanggaran terakhir --}}
                    <div x-show="selectedCandidate && selectedCandidate.recent_violations.length" class="rounded-2xl border border-gray-100 p-4">
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left"></i> Pelanggaran Terakhir
                        </p>
                        <div class="space-y-2.5">
                            <template x-for="(v, i) in selectedCandidate.recent_violations" :key="i">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-7 h-7 rounded-lg bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-gavel text-[10px]"></i>
                                        </div>
                                        <span class="text-sm text-gray-600 truncate" x-text="v.date + ' — ' + v.type"></span>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-600 shrink-0" x-text="'+' + v.points + ' poin'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Aksi --}}
                    <div class="flex flex-col sm:flex-row items-stretch gap-3 pt-1">
                        <a :href="(livenessWarn && !confirmLiveness) ? null : '{{ route('violations.create') }}?student_id=' + chosenId + '&name=' + encodeURIComponent(chosenName) + '&info=' + encodeURIComponent(chosenInfo)"
                            :class="(livenessWarn && !confirmLiveness) ? 'opacity-40 pointer-events-none' : ''"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl hover:from-blue-600 hover:to-indigo-600 active:scale-[0.98] transition shadow-lg shadow-blue-500/25">
                            <i class="fa-solid fa-plus"></i> Tambah Pelanggaran
                        </a>
                        <button type="button" @click="reset()"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-gray-600 bg-white border border-slate-200 rounded-2xl hover:bg-gray-50 active:scale-[0.98] transition">
                            <i class="fa-solid fa-rotate text-gray-400"></i> Scan Lagi
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- ===== HASIL: TIDAK DIKENALI ===== --}}
        <template x-if="result && !result.matched">
            <div class="overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-amber-100">
                <div class="bg-gradient-to-r from-amber-500 to-orange-400 px-6 py-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center shadow-inner">
                        <i class="fa-solid fa-user-slash text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-white font-bold text-base">Siswa Belum Terdaftar</p>
                        <p class="text-amber-50/90 text-xs">Wajah tidak dikenali di database siswa</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="rounded-2xl bg-amber-50/60 border border-amber-100 p-4 text-sm text-amber-700 mb-5 flex items-start gap-3">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <span>Wajah tidak dikenali — simpan foto ini atas nama siswa agar bisa dikenali di kemudian hari.</span>
                    </div>

                    {{-- Sukses daftar --}}
                    <template x-if="regDone">
                        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 mb-4">
                            <p class="text-sm font-bold text-emerald-700 flex items-center gap-2"><i class="fa-solid fa-circle-check"></i> Wajah <span x-text="regName"></span> berhasil terdaftar (1 foto)</p>
                            <p class="text-xs text-emerald-600 mt-1">Scan ulang sekarang untuk verifikasi. Tambahkan foto lain di halaman Registrasi Wajah untuk akurasi lebih baik.</p>
                        </div>
                    </template>

                    {{-- Form simpan cepat --}}
                    <template x-if="!regDone">
                        <div class="rounded-2xl border border-gray-100 p-4 mb-4">
                            <div class="flex items-center gap-3 mb-3">
                                <img :src="lastScanPreview" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shadow-sm">
                                <div>
                                    <p class="text-sm font-bold text-gray-700">Simpan foto ini atas nama siswa:</p>
                                    <p class="text-xs text-gray-400">Foto hasil scan akan dipakai sebagai referensi wajah</p>
                                </div>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                <input type="text" x-model="regQuery" @input.debounce="searchStudent" placeholder="Cari NISN / nama siswa..."
                                    class="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-400 transition">
                            </div>
                            <div x-show="regResults.length > 0" class="mt-1.5 max-h-44 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg">
                                <template x-for="s in regResults" :key="s.id">
                                    <button type="button" @click="selectRegStudent(s)"
                                        class="w-full text-left px-3.5 py-2.5 hover:bg-amber-50 transition flex items-center gap-2.5 border-b border-gray-50 last:border-0">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-xs shrink-0" x-text="s.full_name.charAt(0)"></div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-700 truncate" x-text="s.full_name"></p>
                                            <p class="text-[11px] text-gray-400" x-text="s.nisn + ' · ' + (s.class_name || '')"></p>
                                        </div>
                                    </button>
                                </template>
                            </div>
                            <div x-show="regStudentId && !regResults.length" class="mt-3 flex items-center justify-between gap-3 p-3 rounded-xl bg-amber-50 border border-amber-200">
                                <p class="text-sm font-semibold text-amber-800"><i class="fa-solid fa-user-check mr-1.5"></i><span x-text="regName"></span></p>
                                <button type="button" @click="regStudentId = null; regQuery = ''" class="text-xs text-gray-400 hover:text-gray-600">ubah</button>
                            </div>
                            <p x-show="regError" class="mt-2 text-xs font-semibold text-red-500" x-text="regError"></p>
                            <button type="button" @click="saveReg()" :disabled="!regStudentId || regSaving"
                                class="mt-3 w-full inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl hover:from-amber-600 hover:to-orange-600 active:scale-[0.98] transition shadow-lg shadow-amber-500/25 disabled:opacity-40 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-floppy-disk" x-show="!regSaving"></i>
                                <i class="fa-solid fa-spinner fa-spin" x-show="regSaving"></i>
                                <span x-text="regSaving ? 'Menyimpan...' : 'Simpan Wajah untuk Siswa Ini'"></span>
                            </button>
                            <a :href="'{{ route('face.register') }}?q=' + encodeURIComponent(queryHint)"
                                class="mt-2.5 block text-center text-xs font-semibold text-gray-400 hover:text-amber-600 transition">
                                Atau buka halaman Registrasi Wajah (ambil 3 foto) →
                            </a>
                        </div>
                    </template>

                    <div class="flex flex-col sm:flex-row items-stretch gap-3">
                        <button type="button" @click="reset()"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl hover:from-blue-600 hover:to-indigo-600 active:scale-[0.98] transition shadow-lg shadow-blue-500/25">
                            <i class="fa-solid fa-rotate"></i> Scan Siswa Lain
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @if (!$faceidConfigured)
        <div class="mt-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-sm text-red-600 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i> Microservice FaceID belum dikonfigurasi (FACEID_BASE_URL / FACEID_API_KEY di .env). Fitur scan nonaktif sementara — gunakan input manual.
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script type="module" src="/vendor/mediapipe/facefinder.js"></script>
<script>
function faceScan() {
    return {
        preview: null, file: null, loading: false, error: null, cameraError: null,
        result: null, chosenId: null, cameraOn: false, detectorError: false, facing: 'environment',
        autoScanOn: false, autoTimer: null, autoBusy: false, autoScanning: false, autoNoMatch: false, autoAttempts: 0,
        needBlink: false, livenessWarn: false, noBlinkTicks: 0, nudge: false, confirmLiveness: false, signalCount: 0,
        livenessOff: false, engineOk: true, signalTimer: null, signalsLive: { blink: false, smile: false, depth: false, motion: false },
        lastScanFile: null, lastScanPreview: null, regQuery: '', regResults: [], regStudentId: null, regName: '', regSaving: false, regDone: false, regError: null,

        get chosenName() { return this._chosen()?.full_name || ''; },
        get chosenInfo() {
            const c = this._chosen();
            return c ? (c.nisn + ' - ' + (c.class_name || '')) : '';
        },
        get selectedCandidate() { return this._chosen() || null; },
        get queryHint() { return this.selectedCandidate ? this.selectedCandidate.full_name : ''; },
        _chosen() {
            return (this.result?.candidates || []).find(x => x.id === this.chosenId) || this.result?.candidates?.[0] || null;
        },

        init() {
            window.addEventListener('beforeunload', () => this.stopCamera());
            window.addEventListener('face-detector-error', () => {
                this.detectorError = true;
                const btn = document.getElementById('btnCapture');
                if (btn) { btn.disabled = false; btn.classList.remove('opacity-50', 'disabled:ring-white/30'); }
                const hint = document.getElementById('faceHint');
                if (hint) {
                    hint.textContent = 'DETEKSI OFF — FOTO TETAP BISA';
                    hint.className = 'text-[11px] font-bold tracking-wider px-3.5 py-1.5 rounded-full backdrop-blur-md transition-all duration-300 bg-amber-500/80 text-white shadow-lg shadow-amber-500/30';
                }
            });
            window.addEventListener('face-liveness-off', () => {
                this.livenessOff = true;
            });
        },

        async startCamera() {
            this.cameraError = null;
            if (!navigator.mediaDevices?.getUserMedia) {
                this.cameraError = 'Perangkat ini tidak mendukung kamera langsung — gunakan ikon galeri di viewfinder.';
                return;
            }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: this.facing, width: { ideal: 960 } },
                    audio: false,
                });
                this.$refs.video.srcObject = stream;
                this.cameraOn = true;
                this.engineOk = !!window.__faceLoop;
                await this.$refs.video.play().catch(() => {});
                if (window.__faceLoop) window.__faceLoop.start(this.$refs.video, this.$refs.overlay);
                this.needBlink = false; this.livenessWarn = false; this.noBlinkTicks = 0; this.nudge = false; this.confirmLiveness = false;
                this.startSignalPoll();
                this.startAutoScan();
            } catch (e) {
                this.cameraError = 'Kamera tidak bisa diakses — izinkan akses kamera di browser, atau gunakan ikon galeri di viewfinder.';
            }
        },

        stopCamera() {
            this.stopAutoScan();
            this.stopSignalPoll();
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

        // ===== AUTO-SCAN: cocokkan frame live dengan database tanpa tombol =====
        startSignalPoll() {
            if (this.signalTimer) return;
            this.signalTimer = setInterval(() => {
                const s = window.__faceLoop?.signals;
                if (s) this.signalsLive = { ...s };
            }, 500);
        },

        stopSignalPoll() {
            if (this.signalTimer) { clearInterval(this.signalTimer); this.signalTimer = null; }
        },

        startAutoScan() {
            if (this.autoTimer) return;
            this.autoScanOn = true;
            this.autoAttempts = 0;
            this.autoNoMatch = false;
            // Tick pertama segera (setelah kamera stabil), lalu tiap 1.2 detik
            setTimeout(() => this.autoTick(), 400);
            this.autoTimer = setInterval(() => this.autoTick(), 1200);
        },

        stopAutoScan() {
            if (this.autoTimer) { clearInterval(this.autoTimer); this.autoTimer = null; }
            this.autoScanOn = false;
            this.autoBusy = false;
            this.autoScanning = false;
        },

        grabFrame() {
            return new Promise((resolve) => {
                const v = this.$refs.video;
                if (!v || !v.videoWidth) { resolve(null); return; }
                // Downscale ke maks 960px + kompresi 0.82 → upload kecil, matching tetap akurat
                const maxDim = 960;
                const scale = Math.min(1, maxDim / Math.max(v.videoWidth, v.videoHeight));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(v.videoWidth * scale);
                canvas.height = Math.round(v.videoHeight * scale);
                canvas.getContext('2d').drawImage(v, 0, 0, canvas.width, canvas.height);
                canvas.toBlob((blob) => {
                    if (!blob) { resolve(null); return; }
                    resolve(new File([blob], 'auto-' + Date.now() + '.jpg', { type: 'image/jpeg' }));
                }, 'image/jpeg', 0.82);
            });
        },

        async autoTick() {
            if (this.autoBusy || this.loading || this.result || this.preview || !this.cameraOn) return;
            // Tanpa wajah di frame → tunggu (kecuali deteksi otomatis sedang error)
            if (!this.detectorError && window.__faceLoop && !window.__faceLoop.detected) {
                this.autoNoMatch = true;
                return;
            }
            this.autoBusy = true;
            this.autoScanning = true;
            const file = await this.grabFrame();
            if (!file) { this.autoBusy = false; this.autoScanning = false; return; }
            this.lastScanFile = file;
            if (this.lastScanPreview) URL.revokeObjectURL(this.lastScanPreview);
            this.lastScanPreview = URL.createObjectURL(file);
            const fd = new FormData();
            fd.append('photo', file);
            fetch('{{ route('face.scan.verify') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: fd,
            })
            .then(r => r.json())
            .then(d => {
                if (!d.ok) { this.autoNoMatch = true; return; }
                this.autoAttempts++;
                const top = d.candidates?.[0];
                // Ragu (kemungkinan kembar) → tampilkan kandidat untuk dipilih
                if (d.matched && d.ambiguous) {
                    this._maybeFinishAuto(d);
                    return;
                }
                // Cocok kuat → langsung tampilkan hasil
                if (d.matched && top && top.score >= 0.85) {
                    this._maybeFinishAuto(d);
                    return;
                }
                this.autoNoMatch = this.autoAttempts >= 3;
                if (this.autoAttempts >= 3) {
                    // Belum dikenal → tampilkan kartu simpan-cepat (registrasi atas nama siswa)
                    this.result = { matched: false, candidates: [] };
                }
            })
            .catch(() => {})
            .finally(() => {
                this.autoBusy = false;
                setTimeout(() => { this.autoScanning = false; }, 500);
            });
        },

        _maybeFinishAuto(d) {
            const loop = window.__faceLoop;
            // MODE TANPA LIVENESS (sesuai permintaan): wajah cocok → langsung selesai.
            // Anti-foto tetap ada di lapisan lain (deteksi wajah hidup).
            this._finishAuto(d);
        },

        _finishAuto(d) {
            this.result = d;
            this.signalCount = window.__faceLoop ? window.__faceLoop.signalCount : 0;
            if (d.candidates.length) this.chosenId = d.candidates[0].id;
            this.stopAutoScan();
            this.stopCamera();
        },

        capture() {
            this.stopAutoScan();
            if (!this.detectorError && window.__faceLoop && !window.__faceLoop.detected) {
                this.error = 'Tidak ada wajah terdeteksi — hadapkan kamera ke wajah siswa.';
                return;
            }
            const v = this.$refs.video;
            if (!v || !v.srcObject || v.videoWidth === 0) return;
            const maxDim = 960;
            const scale = Math.min(1, maxDim / Math.max(v.videoWidth, v.videoHeight));
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(v.videoWidth * scale);
            canvas.height = Math.round(v.videoHeight * scale);
            canvas.getContext('2d').drawImage(v, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((blob) => {
                if (!blob) return;
                this.file = new File([blob], 'scan-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                this.preview = URL.createObjectURL(this.file);
                this.result = null; this.chosenId = null; this.error = null;
                this.stopCamera();
                this.scan();
            }, 'image/jpeg', 0.85);
        },

        captureAgain() {
            this.preview = null; this.file = null; this.result = null;
            this.startCamera();
        },

        onFile(e) {
            const f = e.target.files[0];
            if (!f) return;
            this.file = f;
            this.preview = URL.createObjectURL(f);
            this.result = null; this.chosenId = null; this.error = null;
        },

        scan() {
            if (!this.file) { this.error = 'Ambil foto wajah dulu.'; return; }
            this.lastScanFile = this.file;
            this.lastScanPreview = this.preview;
            this.loading = true; this.error = null; this.result = null;
            const fd = new FormData();
            fd.append('photo', this.file);
            fetch('{{ route('face.scan.verify') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: fd,
            })
            .then(r => r.json())
            .then(d => {
                if (!d.ok) {
                    this.error = d.error === 'faceid_down'
                        ? 'Layanan FaceID sedang mati — gunakan input manual.'
                        : 'Verifikasi gagal: ' + (d.error || 'unknown');
                    return;
                }
                this.result = d;
                if (d.candidates.length) this.chosenId = d.candidates[0].id;
            })
            .catch(() => { this.error = 'Terjadi kesalahan jaringan.'; })
            .finally(() => { this.loading = false; });
        },

        searchStudent() {
            if (this.regQuery.length < 2) { this.regResults = []; return; }
            fetch('{{ route('api.students.search') }}?q=' + encodeURIComponent(this.regQuery))
                .then(r => r.json())
                .then(data => { this.regResults = data; });
        },

        selectRegStudent(s) {
            this.regStudentId = s.id;
            this.regName = s.full_name;
            this.regQuery = s.full_name;
            this.regResults = [];
        },

        saveReg() {
            if (!this.regStudentId || !this.lastScanFile) return;
            this.regSaving = true; this.regError = null;
            const fd = new FormData();
            fd.append('student_id', this.regStudentId);
            fd.append('photo', this.lastScanFile);
            fetch('{{ route('face.register.quick') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: fd,
            })
            .then(r => r.json())
            .then(d => {
                if (!d.ok) {
                    this.regError = d.detail && d.detail.length
                        ? 'Foto ditolak quality gate: ' + d.detail.map(x => x.reason).join(', ')
                        : 'Registrasi gagal: ' + (d.error || 'unknown');
                    return;
                }
                this.regDone = true;
            })
            .catch(() => { this.regError = 'Terjadi kesalahan jaringan.'; })
            .finally(() => { this.regSaving = false; });
        },

        reset() {
            this.preview = null; this.file = null; this.result = null;
            this.chosenId = null; this.error = null; this.cameraError = null;
            this.needBlink = false; this.livenessWarn = false; this.noBlinkTicks = 0; this.nudge = false; this.confirmLiveness = false;
            this.regQuery = ''; this.regResults = []; this.regStudentId = null; this.regName = ''; this.regSaving = false; this.regDone = false; this.regError = null;
            if (window.__faceLoop) window.__faceLoop.resetLiveness();
            this.stopCamera();
        },
    };
}
</script>
@endpush
