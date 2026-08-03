@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Sekolah</h1>
        <p class="text-sm text-slate-500 mt-1">Informasi sekolah untuk kop surat</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        {{-- Header card --}}
        <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-sky-400 px-6 py-5 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-32 h-32 opacity-10">
                <i class="fa-solid fa-building-columns text-white text-8xl"></i>
            </div>
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 border border-white/25 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <i class="fa-solid fa-building-columns text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Informasi Sekolah</h2>
                    <p class="text-xs text-white/70">Data dasar yang dipakai di seluruh aplikasi</p>
                </div>
            </div>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            {{-- ===== Seksi: Identitas Aplikasi & Sekolah ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i class="fa-solid fa-school text-blue-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Identitas</span>
                </div>
                <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Nama Aplikasi</label>
                <input type="text" name="app_name" value="{{ old('app_name', $settings->get('app_name')?->value ?? 'Aplikasi Ketertiban') }}" required
                    class="input">
                <p class="text-xs text-gray-400 mt-1">Nama yang tampil di sidebar, login page, dan tab browser.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Nama Sekolah</label>
                <input type="text" name="school_name" value="{{ old('school_name', $settings->get('school_name')?->value ?? '') }}" required
                    class="input">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Alamat Sekolah</label>
                <textarea name="school_address" rows="2" class="input">{{ old('school_address', $settings->get('school_address')?->value ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">No. Telepon</label>
                <input type="text" name="school_phone" value="{{ old('school_phone', $settings->get('school_phone')?->value ?? '') }}"
                    class="input">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Logo Sekolah</label>
                @if($settings->get('school_logo')?->value)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $settings->get('school_logo')->value) }}" class="h-16 object-contain">
                    </div>
                @endif
                <input type="file" name="school_logo" accept="image/*"
                    class="input file:mr-3 file:py-1.5 file:px-3 file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            </div>
            </div>

            {{-- ===== Seksi: Kop Surat ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center">
                        <i class="fa-solid fa-file-lines text-amber-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kop Surat (format resmi)</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Baris kop yang tampil di dokumen laporan — mengikuti format kop resmi sekolah.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Baris 1 (pemerintah)</label>
                        <input type="text" name="school_government" value="{{ old('school_government', $settings->get('school_government')?->value ?? 'PEMERINTAH PROVINSI JAWA TIMUR') }}"
                            class="input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Baris 2 (dinas)</label>
                        <input type="text" name="school_agency" value="{{ old('school_agency', $settings->get('school_agency')?->value ?? 'DINAS PENDIDIKAN') }}"
                            class="input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Baris 3 (nama resmi sekolah)</label>
                        <input type="text" name="school_full_name" value="{{ old('school_full_name', $settings->get('school_full_name')?->value ?? '') }}"
                            class="input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Baris 4 (alamat &amp; telp)</label>
                        <input type="text" name="school_address_detail" value="{{ old('school_address_detail', $settings->get('school_address_detail')?->value ?? '') }}"
                            class="input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Baris 5 (website &amp; email)</label>
                        <input type="text" name="school_website_email" value="{{ old('school_website_email', $settings->get('school_website_email')?->value ?? '') }}"
                            class="input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Baris 6 (kode pos)</label>
                        <input type="text" name="school_postal" value="{{ old('school_postal', $settings->get('school_postal')?->value ?? '') }}"
                            class="input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Logo Kop Surat</label>
                        @if($settings->get('kop_logo')?->value)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings->get('kop_logo')->value) }}" class="h-16 object-contain">
                            </div>
                        @endif
                        <input type="file" name="kop_logo" accept="image/*"
                            class="input file:mr-3 file:py-1.5 file:px-3 file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>
            </div>

            {{-- ===== Seksi: Background Login ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-violet-50 flex items-center justify-center">
                        <i class="fa-solid fa-image text-violet-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Background Halaman Login</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Background Halaman Login</label>
                @if($settings->get('login_background')?->value)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $settings->get('login_background')->value) }}" class="h-32 w-full object-cover rounded-lg border border-slate-200">
                    </div>
                @endif
                <input type="file" name="login_background" accept="image/*"
                    class="input file:mr-3 file:py-1.5 file:px-3 file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-400 mt-1.5">Dimensi ideal: <strong class="text-gray-500">1200 × 800 px</strong> atau <strong class="text-gray-500">3:2</strong> (landscape). Maksimal <strong class="text-gray-500">2 MB</strong>. Format JPG/PNG/WebP.</p>
            </div>
            </div>

            {{-- ===== Seksi: Kepala Sekolah ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <i class="fa-solid fa-user-tie text-emerald-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kepala Sekolah</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Nama Kepala Sekolah</label>
                        <input type="text" name="kepala_sekolah_name" value="{{ old('kepala_sekolah_name', $settings->get('kepala_sekolah_name')?->value ?? '') }}"
                            class="input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">NIP Kepala Sekolah</label>
                        <input type="text" name="kepala_sekolah_nip" value="{{ old('kepala_sekolah_nip', $settings->get('kepala_sekolah_nip')?->value ?? '') }}"
                            class="input">
                    </div>
                </div>
            </div>

            {{-- ===== Seksi: Template WhatsApp ===== --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <i class="fa-brands fa-whatsapp text-emerald-500 text-xs"></i>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Notifikasi WhatsApp Orang Tua</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Template Notifikasi WhatsApp Orang Tua</label>
                <textarea name="wa_notification_template" rows="8"
                    class="input font-mono text-xs">{{ old('wa_notification_template', $settings->get('wa_notification_template')?->value ?? '') }}</textarea>
                <p class="text-xs text-gray-400 mt-1.5">Placeholder: <code class="text-blue-600">{nama_siswa} {nisn} {kelas} {jenis} {tanggal} {waktu} {poin} {total_poin} {lokasi} {deskripsi} {sekolah}</code>.<br>
                Akhiri pesan dengan <strong class="text-gray-500">Tim Ketertiban</strong> sebagai penanda pengirim. Kosongkan untuk memakai template bawaan.</p>
            </div>
            </div>

            <button type="submit" class="btn-primary w-full justify-center py-3.5">
                <i class="fa-solid fa-floppy-disk text-sm"></i>
                Simpan Pengaturan
            </button>
        </form>
    </div>

    {{-- Backup Database Card --}}
    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-database text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Backup Database</h3>
                    <p class="text-xs text-gray-400">Kelola backup dan restore database</p>
                </div>
            </div>
            <a href="{{ route('settings.backup') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition flex-shrink-0">
                Kelola Backup
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
        <div class="p-5 text-xs text-gray-500 leading-relaxed">
            <p>Fitur backup database memungkinkan Anda untuk:</p>
            <ul class="mt-2 space-y-1.5">
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-emerald-500 mt-0.5"></i>
                    Membuat backup database kapan saja
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-emerald-500 mt-0.5"></i>
                    Mendownload file backup untuk penyimpanan eksternal
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-emerald-500 mt-0.5"></i>
                    Merestore database dari backup dengan safety backup otomatis
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
