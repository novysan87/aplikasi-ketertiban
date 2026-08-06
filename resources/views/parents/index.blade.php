@extends('layouts.app')

@section('title', 'Data Wali Murid')

@section('content')
<div class="space-y-6">

    {{-- Header premium --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1e3a8a] via-[#1d4ed8] to-[#0ea5e9] shadow-xl shadow-blue-500/20">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>
        <div class="absolute -right-20 -top-24 w-80 h-80 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-24 w-72 h-72 rounded-full bg-sky-300/20 blur-3xl"></div>
        <div class="relative px-6 py-7 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="relative">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur border border-white/25 shadow-inner">
                        <i class="fa-solid fa-people-roof text-xl text-white"></i>
                    </div>
                    <span class="absolute -top-1.5 -right-1.5 w-3.5 h-3.5 rounded-full bg-emerald-400 border-2 border-blue-700"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-white tracking-tight">Data Wali Murid</h1>
                        <span class="px-2.5 py-1 rounded-full bg-white/15 backdrop-blur border border-white/20 text-[10px] font-bold text-white tracking-widest uppercase">SiMURID</span>
                    </div>
                    <p class="text-sm text-sky-100/90 mt-1">Akun orang tua/wali pengguna aplikasi — terpisah dari user internal sekolah</p>
                </div>
            </div>
            <a href="{{ route('parents.verification') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-blue-700 text-sm font-bold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <i class="fa-solid fa-user-check"></i> Verifikasi Wali
                @if($pending > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-amber-400 text-amber-900 text-[10px] font-extrabold">{{ $pending }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Kartu statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-blue-50"></div>
            <div class="flex items-center justify-between relative">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Akun</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-1.5">{{ $total }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Seluruh wali terdaftar</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-emerald-50"></div>
            <div class="flex items-center justify-between relative">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Aktif</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-1.5">{{ $totalAktif }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Terkait &amp; terverifikasi</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <i class="fa-solid fa-user-check text-lg"></i>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-amber-50"></div>
            <div class="flex items-center justify-between relative">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Menunggu</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-1.5">{{ $pending }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Perlu diverifikasi</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <i class="fa-solid fa-hourglass-half text-lg"></i>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-fuchsia-50"></div>
            <div class="flex items-center justify-between relative">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Baru 7 Hari</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-1.5">{{ $baruMingguIni }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Registrasi terakhir</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-fuchsia-500 to-pink-600 text-white flex items-center justify-center shadow-lg shadow-fuchsia-500/30">
                    <i class="fa-solid fa-rocket text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-xmark text-red-500"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Tabel wali --}}
    <div class="bg-white rounded-3xl shadow-lg shadow-slate-200/60 border border-slate-200/70 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-1.5 h-5 rounded-full bg-gradient-to-b from-blue-500 to-indigo-600"></span>
                    Daftar Akun Wali Murid
                </h2>
                <p class="text-xs text-slate-400 mt-1">Kelola akun &amp; reset password bila wali lupa</p>
            </div>
            <form method="GET" class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / username / HP..."
                    class="text-sm rounded-2xl border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pl-10 pr-4 py-2.5 w-full sm:w-72 bg-slate-50/50 focus:bg-white transition-all shadow-sm">
            </form>
        </div>

        @if($users->isEmpty())
        <div class="px-5 py-16 text-center">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center mb-4">
                <i class="fa-solid fa-people-roof text-3xl text-slate-300"></i>
            </div>
            <p class="font-semibold text-slate-500">Belum ada akun wali murid</p>
            <p class="text-sm text-slate-400 mt-1">Wali yang mendaftar lewat aplikasi akan tampil di sini.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-gradient-to-r from-slate-50 to-blue-50/40">
                    <tr class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Wali</th>
                        <th class="px-6 py-3.5">Kontak</th>
                        <th class="px-6 py-3.5">Putra/Putri Tertaut</th>
                        <th class="px-6 py-3.5">Perangkat</th>
                        <th class="px-6 py-3.5">Terdaftar</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($users as $user)
                    <tr class="group hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-extrabold text-sm shadow-md shadow-blue-500/25 group-hover:scale-105 transition-transform">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm text-gray-800 flex items-center gap-2">
                                        {{ $user->name }}
                                        @php $relations = collect($user->parentStudents)->pluck('relation')->filter(); @endphp
                                        @if($relations->contains('father'))
                                        <span class="px-1.5 py-0.5 rounded-md bg-sky-50 border border-sky-100 text-sky-600 text-[9px] font-bold">AYAH</span>
                                        @elseif($relations->contains('mother'))
                                        <span class="px-1.5 py-0.5 rounded-md bg-pink-50 border border-pink-100 text-pink-600 text-[9px] font-bold">IBU</span>
                                        @elseif($relations->contains('guardian'))
                                        <span class="px-1.5 py-0.5 rounded-md bg-violet-50 border border-violet-100 text-violet-600 text-[9px] font-bold">WALI</span>
                                        @endif
                                    </p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $user->username }}</p>
                                    @php
                                        $linkStatuses = $user->parentStudents->pluck('status');
                                        $verifStatus = $user->is_active
                                            ? ($linkStatuses->contains('active') ? 'active'
                                                : ($linkStatuses->contains('pending') ? 'pending'
                                                    : ($linkStatuses->contains('rejected') ? 'rejected' : 'none')))
                                            : 'nonaktif';
                                        $verifBadge = [
                                            'active' => ['Akun aktif', 'text-emerald-500', 'bg-emerald-500'],
                                            'pending' => ['Menunggu verifikasi', 'text-amber-500', 'bg-amber-500'],
                                            'rejected' => ['Tautan ditolak', 'text-red-500', 'bg-red-500'],
                                            'none' => ['Belum ada tautan', 'text-slate-400', 'bg-slate-400'],
                                            'nonaktif' => ['Akun nonaktif', 'text-red-400', 'bg-red-400'],
                                        ][$verifStatus];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 mt-0.5 text-[10px] {{ $verifBadge[0] ? $verifBadge[1] : '' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $verifBadge[2] }} {{ $verifStatus === 'pending' ? 'animate-pulse' : '' }}"></span>
                                        {{ $verifBadge[0] }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1.5 text-sm text-slate-600">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-phone text-[11px] text-slate-300"></i>
                                    {{ $user->phone ?? '-' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-xs {{ $user->email ? 'text-slate-500' : 'text-slate-300' }}">
                                    <i class="fa-regular fa-envelope text-[11px]"></i>
                                    {{ $user->email ?? 'tanpa email' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->parentStudents->isEmpty())
                            <span class="inline-flex items-center gap-1.5 text-xs text-slate-400">
                                <i class="fa-solid fa-link-slash"></i> Belum ada tautan
                            </span>
                            @else
                            <div class="flex flex-col gap-1.5">
                                @foreach($user->parentStudents as $link)
                                <span class="inline-flex items-center gap-2 text-xs">
                                    <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-sky-100 to-blue-100 text-blue-600 flex items-center justify-center">
                                        <i class="fa-solid fa-graduation-cap text-[9px]"></i>
                                    </span>
                                    <span class="font-medium text-slate-700">{{ $link->student?->full_name ?? 'Siswa' }}</span>
                                    @if($link->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                    </span>
                                    @elseif($link->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 border border-amber-100 text-amber-600 text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-50 border border-red-100 text-red-500 text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Ditolak
                                    </span>
                                    @endif
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->parentDevices->isEmpty())
                            <span class="text-xs text-slate-400">Belum ada perangkat</span>
                            @else
                            <div class="flex flex-col gap-1.5">
                                @foreach($user->parentDevices as $device)
                                <span class="inline-flex items-center gap-2 text-xs">
                                    @if($device->platform === 'android')
                                    <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-50 to-green-100 text-emerald-600 flex items-center justify-center">
                                        <i class="fa-solid fa-mobile-screen text-[10px]"></i>
                                    </span>
                                    @elseif($device->platform === 'ios')
                                    <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 text-slate-500 flex items-center justify-center">
                                        <i class="fa-brands fa-apple text-[10px]"></i>
                                    </span>
                                    @else
                                    <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-sky-50 to-blue-100 text-blue-500 flex items-center justify-center">
                                        <i class="fa-solid fa-globe text-[10px]"></i>
                                    </span>
                                    @endif
                                    <div>
                                        <p class="font-medium text-slate-600">{{ $device->device_name }}</p>
                                        <p class="text-[10px] text-slate-400">aktif {{ $device->last_active_at?->diffForHumans() }}</p>
                                    </div>
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                                <i class="fa-regular fa-calendar text-slate-300"></i>
                                {{ $user->created_at?->format('d M Y') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="forceLogoutWali({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-bold shadow-sm hover:shadow-md hover:-translate-y-0.5 hover:border-red-200 hover:text-red-600 transition-all"
                                    title="Logout akun di semua perangkat">
                                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                                </button>
                                <button onclick="resetWaliPassword({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-xs font-bold shadow-md shadow-amber-500/30 hover:shadow-lg hover:-translate-y-0.5 hover:brightness-105 transition-all"
                                    title="Set password baru">
                                    <i class="fa-solid fa-key"></i> Reset Password
                                </button>
                                <button onclick="deleteWali({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-xs font-bold shadow-md shadow-rose-500/30 hover:shadow-lg hover:-translate-y-0.5 hover:brightness-105 transition-all"
                                    title="Hapus akun wali">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function forceLogoutWali(userId, userName) {
    Swal.fire({
        title: 'Logout jarak jauh?',
        html: 'Semua perangkat akun <b>' + userName + '</b> akan di-logout paksa.<br>Wali harus login ulang dengan password.',
        icon: 'question',
        confirmButtonText: '<i class="fa-solid fa-right-from-bracket"></i> Ya, Logout',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        confirmButtonColor: '#6366f1',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ url('parents') }}/' + userId + '/force-logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            }).then(r => {
                if (r.redirected) window.location.href = r.url;
                else location.reload();
            });
        }
    });
}

function deleteWali(userId, userName) {
    Swal.fire({
        title: 'Hapus akun ' + userName + '?',
        html: 'Akun wali, <b>tautan putra/putri</b>, dan <b>data perangkat</b> akan dihapus permanen dari database.<br><br>Data siswa <b>tidak</b> ikut terhapus.',
        icon: 'warning',
        confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Ya, Hapus',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        confirmButtonColor: '#e11d48',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ url('parents') }}/' + userId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            }).then(r => {
                if (r.redirected) window.location.href = r.url;
                else location.reload();
            });
        }
    });
}

function resetWaliPassword(userId, userName) {
    Swal.fire({
        title: 'Reset Password — ' + userName,
        html: '<input type="password" id="new-password" class="swal2-input" placeholder="Password baru (min. 6 karakter)" minlength="6">' +
              '<input type="password" id="confirm-password" class="swal2-input" placeholder="Konfirmasi password">',
        confirmButtonText: '<i class="fa-solid fa-floppy-disk"></i> Simpan',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const pwd = document.getElementById('new-password').value;
            const confirm = document.getElementById('confirm-password').value;
            if (!pwd || pwd.length < 6) {
                Swal.showValidationMessage('Password minimal 6 karakter');
                return false;
            }
            if (pwd !== confirm) {
                Swal.showValidationMessage('Password tidak cocok');
                return false;
            }
            return pwd;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ url('users') }}/' + userId + '/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ new_password: result.value }),
            }).then(r => {
                if (r.redirected) window.location.href = r.url;
                else location.reload();
            });
        }
    });
}
</script>
@endpush
@endsection
