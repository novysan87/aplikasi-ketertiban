@extends('layouts.app')

@section('title', 'Backup Database')

@section('content')
<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-400 mb-1">
                <a href="{{ route('settings.index') }}" class="hover:text-gray-600 transition">Pengaturan</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700 font-medium">Backup Database</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Backup Database</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola backup dan restore database aplikasi</p>
        </div>
        <form action="{{ route('settings.backup.create') }}" method="POST">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl hover:from-blue-600 hover:to-blue-700 transition shadow-sm">
                <i class="fa-solid fa-database text-xs"></i>
                Backup Sekarang
            </button>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10">
                <i class="fa-solid fa-layer-group text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Total Backup</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_backups'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">file tersimpan</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10">
                <i class="fa-solid fa-clock-rotate-left text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Backup Terakhir</p>
                <p class="text-base font-bold text-white mt-1">{{ $stats['latest_backup'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">terbaru</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-700 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10">
                <i class="fa-solid fa-hard-drive text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Total Ukuran</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_size'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">semua backup</p>
            </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 shadow-sm">
            <div class="absolute right-0 top-0 w-20 h-20 opacity-10">
                <i class="fa-solid fa-server text-white text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Ukuran Database</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $stats['db_size'] }}</p>
                <p class="text-[10px] text-white/50 mt-0.5">saat ini</p>
            </div>
        </div>
    </div>

    {{-- Daftar Backup --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Riwayat Backup</h3>
                    <p class="text-xs text-gray-400">Daftar file backup database</p>
                </div>
            </div>
        </div>

        @if($backups->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama File</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Tanggal</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Ukuran</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($backups as $backup)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                            <i class="fa-solid fa-file-zipper text-blue-500 text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate max-w-[240px] sm:max-w-[300px]">{{ $backup->filename }}</p>
                                            <p class="text-xs text-gray-400 sm:hidden">{{ $backup->date }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap hidden sm:table-cell">
                                    <span class="text-sm text-gray-600">{{ $backup->date }}</span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-lg">
                                        <i class="fa-solid fa-hard-drive mr-1 text-gray-400"></i>
                                        {{ $backup->size }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('settings.backup.download', $backup->filename) }}"
                                            class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition"
                                            title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>

                                        @if($loop->first)
                                            <form action="{{ route('settings.backup.restore') }}" method="POST" id="restore-form-{{ $loop->index }}">
                                                @csrf
                                                <input type="hidden" name="filename" value="{{ $backup->filename }}">
                                                <button type="button"
                                                    x-data
                                                    x-on:click="if(await window.confirmSwal({title:'Restore Database?',text:'Semua data saat ini akan diganti dengan data backup {{ $backup->filename }}. Backup otomatis akan dibuat sebelum restore.',icon:'warning',confirmText:'Ya, Restore!',cancelText:'Batal'})) document.getElementById('restore-form-{{ $loop->index }}').submit()"
                                                    class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Restore">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('settings.backup.destroy', $backup->filename) }}" method="POST" id="delete-form-{{ $loop->index }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                x-data
                                                x-on:click="if(await window.confirmSwal({title:'Hapus Backup?',text:'Yakin ingin menghapus {{ $backup->filename }}?',icon:'question',confirmText:'Ya, Hapus!',cancelText:'Batal'})) document.getElementById('delete-form-{{ $loop->index }}').submit()"
                                                class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-400">
                <span>{{ $backups->count() }} file backup</span>
                <span>Total {{ $stats['total_size'] }}</span>
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-database text-gray-300 text-2xl"></i>
                </div>
                <h4 class="text-sm font-semibold text-gray-500 mb-1">Belum Ada Backup</h4>
                <p class="text-xs text-gray-400 mb-4">Backup database pertama Anda untuk mengamankan data</p>
                <form action="{{ route('settings.backup.create') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-blue-600 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition shadow-sm">
                        <i class="fa-solid fa-database text-xs"></i>
                        Buat Backup Sekarang
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Info Card --}}
    <div class="mt-6 bg-gradient-to-br from-blue-50 to-indigo-50/50 border border-blue-100 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-info text-blue-600 text-sm"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-blue-800 mb-1">Informasi Backup</h4>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li><i class="fa-solid fa-check mr-1.5 text-blue-500"></i>Backup disimpan di <code class="bg-blue-100 px-1 rounded">storage/app/backups/</code></li>
                    <li><i class="fa-solid fa-check mr-1.5 text-blue-500"></i>Format file: <code class="bg-blue-100 px-1 rounded">.sql.gz</code> (SQL + kompresi Gzip)</li>
                    <li><i class="fa-solid fa-check mr-1.5 text-blue-500"></i>Restore akan membuat backup otomatis sebelum menjalankan restore</li>
                    <li><i class="fa-solid fa-check mr-1.5 text-blue-500"></i>Hanya admin yang dapat mengakses fitur ini</li>
                    <li><i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-500"></i>Pastikan ada cukup ruang penyimpanan di server</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
