@extends('layouts.app')

@section('title', 'Reset Aplikasi')

@push('styles')
<style>
    .reset-card {
        transition: all 0.2s ease;
    }
    .reset-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.06);
    }
    .reset-checkbox:checked {
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
    }
    .reset-item.selected {
        ring: 2px;
    }
</style>
@endpush

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reset Aplikasi</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kosongkan data aplikasi secara selektif</p>
        </div>
    </div>

    {{-- Warning Card --}}
    <div class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-6">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-red-800">⚠️ Peringatan</h3>
                <p class="text-xs text-red-600 mt-1">Data yang dipilih akan dihapus permanen dan tidak bisa dikembalikan. Pastikan Anda telah membackup data penting sebelum melanjutkan.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.reset.run') }}" id="reset-form">
        @csrf

        {{-- Selectable Data Cards --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Pilih Data yang Akan Dikosongkan</h3>
                        <p class="text-xs text-gray-400">Centang data yang ingin dihapus, lalu masukkan password admin</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="select-all" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">Pilih Semua</button>
                    <span class="text-gray-300">|</span>
                    <button type="button" id="deselect-all" class="text-xs text-gray-500 hover:text-gray-700 font-medium transition">Hapus Semua</button>
                </div>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    {{-- Presensi --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-orange-300 has-[:checked]:bg-orange-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="attendances" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Presensi</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['attendances'] }} data (dari sinkron E-Jurnal & manual)</p>
                        </div>
                    </label>

                    {{-- Pelanggaran & Evidences --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-red-300 has-[:checked]:bg-red-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="violations" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Pelanggaran</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['violations'] }} data</p>
                        </div>
                    </label>

                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-red-300 has-[:checked]:bg-red-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="evidences" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Foto Bukti</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['evidences'] }} file</p>
                        </div>
                    </label>

                    {{-- Surat Peringatan --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-red-300 has-[:checked]:bg-red-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="sp_letters" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Surat Peringatan</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['sp_letters'] }} surat</p>
                        </div>
                    </label>

                    {{-- Notifikasi --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-red-300 has-[:checked]:bg-red-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="notifications" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Notifikasi</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['notifications'] }} notifikasi</p>
                        </div>
                    </label>

                    {{-- Data Siswa --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-red-300 has-[:checked]:bg-red-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="students" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Siswa</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['students'] }} siswa</p>
                        </div>
                    </label>

                    {{-- Kelas --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-red-300 has-[:checked]:bg-red-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="classes" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Kelas</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['classes'] }} kelas</p>
                        </div>
                    </label>

                    {{-- Kategori Pelanggaran --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-orange-300 has-[:checked]:bg-orange-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="categories" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Kategori Pelanggaran</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['categories'] }} kategori</p>
                            <p class="text-xs text-green-600 font-medium mt-1">Akan dibuat ulang default</p>
                        </div>
                    </label>

                    {{-- Jenis Pelanggaran --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-orange-300 has-[:checked]:bg-orange-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="types" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Jenis Pelanggaran</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['types'] }} jenis</p>
                        </div>
                    </label>

                    {{-- Ambang SP --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-orange-300 has-[:checked]:bg-orange-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="thresholds" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Ambang SP</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['thresholds'] }} threshold</p>
                            <p class="text-xs text-green-600 font-medium mt-1">Akan dibuat ulang default</p>
                        </div>
                    </label>

                    {{-- Pengaturan --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-purple-300 has-[:checked]:bg-purple-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="settings" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-purple-500 focus:ring-purple-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Pengaturan</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['settings'] }} setting</p>
                            <p class="text-xs text-red-500 font-medium mt-1">Nama & logo sekolah ikut hilang</p>
                        </div>
                    </label>

                    {{-- Penanganan --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-amber-300 has-[:checked]:bg-amber-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="handlings" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-amber-500 focus:ring-amber-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">Riwayat Penanganan</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['handlings'] }} catatan penanganan</p>
                            <p class="text-xs text-gray-400">Status pelanggaran akan dikembalikan ke "Belum Ditangani"</p>
                        </div>
                    </label>

                    {{-- Backup Files --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-sky-300 has-[:checked]:bg-sky-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="backups" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-sky-500 focus:ring-sky-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">File Backup</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['backups'] }} file backup database</p>
                            <p class="text-xs text-red-500 font-medium mt-1">Tidak bisa dikembalikan!</p>
                        </div>
                    </label>

                    {{-- User Lain --}}
                    <label class="reset-card relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 has-[:checked]:border-red-300 has-[:checked]:bg-red-50/50 transition-all select-none">
                        <input type="checkbox" name="reset_items[]" value="users" class="reset-checkbox mt-0.5 w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400 focus:ring-offset-0 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">User Lain</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['users_other'] }} user (non-admin)</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Confirm Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Konfirmasi Password</h3>
                        <p class="text-xs text-gray-400">Masukkan password admin untuk menjalankan reset</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-xl">
                    <div class="flex-1">
                        <input type="password" name="confirm_password" required
                            placeholder="Password admin saat ini"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                    <button type="submit" id="reset-btn" disabled
                        class="px-6 py-2.5 text-sm font-semibold text-white bg-gray-300 rounded-xl cursor-not-allowed transition flex items-center justify-center gap-2 whitespace-nowrap">
                        <i class="fa-solid fa-trash-can"></i>
                        Jalankan Reset
                    </button>
                </div>
                <div id="reset-hint" class="mt-2 text-xs text-red-500 hidden">
                    Pilih minimal satu data yang akan direset.
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('input[name="reset_items[]"]');
        const resetBtn = document.getElementById('reset-btn');
        const resetHint = document.getElementById('reset-hint');
        const selectAll = document.getElementById('select-all');
        const deselectAll = document.getElementById('deselect-all');
        const form = document.getElementById('reset-form');

        function updateButton() {
            const checked = document.querySelectorAll('input[name="reset_items[]"]:checked');
            if (checked.length > 0) {
                resetBtn.disabled = false;
                resetBtn.className = 'px-6 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition flex items-center justify-center gap-2 whitespace-nowrap shadow-sm';
                resetHint.classList.add('hidden');
            } else {
                resetBtn.disabled = true;
                resetBtn.className = 'px-6 py-2.5 text-sm font-semibold text-white bg-gray-300 rounded-xl cursor-not-allowed transition flex items-center justify-center gap-2 whitespace-nowrap';
                resetHint.classList.remove('hidden');
            }
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateButton));

        selectAll.addEventListener('click', function (e) {
            e.preventDefault();
            checkboxes.forEach(cb => cb.checked = true);
            updateButton();
        });

        deselectAll.addEventListener('click', function (e) {
            e.preventDefault();
            checkboxes.forEach(cb => cb.checked = false);
            updateButton();
        });

        form.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('input[name="reset_items[]"]:checked');
            if (checked.length === 0) {
                e.preventDefault();
                resetHint.classList.remove('hidden');
                return;
            }
            if (!confirm('⚠️ YAKIN INGIN MELANJUTKAN?\n\nData yang dipilih akan dihapus permanen!\nTindakan ini tidak bisa dibatalkan.')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
