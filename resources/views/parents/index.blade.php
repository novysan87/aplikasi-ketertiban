@extends('layouts.app')

@section('title', 'Data Wali Murid')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-900 shadow-lg">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative px-6 py-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur border border-white/20">
                    <i class="fa-solid fa-people-roof text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Data Wali Murid</h1>
                    <p class="text-sm text-slate-300 mt-1">Akun orang tua/wali pengguna aplikasi SiMURID (terpisah dari user internal)</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 backdrop-blur border border-white/20 text-white text-sm font-semibold">
                    <i class="fa-solid fa-users"></i> {{ $total }} akun
                </span>
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 backdrop-blur border border-white/20 text-white text-sm font-semibold">
                    <i class="fa-solid fa-user-check"></i> {{ $totalAktif }} aktif
                </span>
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

    {{-- Daftar wali --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-gray-900">Daftar Akun Wali Murid</h2>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / username / HP..."
                    class="text-sm rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 px-4 py-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>

        @if($users->isEmpty())
        <div class="px-5 py-12 text-center text-slate-400">
            <i class="fa-solid fa-people-roof text-4xl mb-3"></i>
            <p class="text-sm">Belum ada akun wali murid terdaftar.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Wali</th>
                        <th class="px-5 py-3">Nomor HP</th>
                        <th class="px-5 py-3">Putra/Putri Tertaut</th>
                        <th class="px-5 py-3">Terdaftar</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-sm text-gray-800">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $user->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $user->phone ?? '-' }}</td>
                        <td class="px-5 py-3">
                            @if($user->parentStudents->isEmpty())
                            <span class="text-xs text-slate-400">Belum ada tautan</span>
                            @else
                            <div class="flex flex-col gap-1">
                                @foreach($user->parentStudents as $link)
                                <span class="inline-flex items-center gap-1.5 text-xs">
                                    <i class="fa-solid fa-graduation-cap text-slate-300"></i>
                                    {{ $link->student?->full_name ?? 'Siswa' }}
                                    @if($link->status === 'active')
                                    <span class="px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-semibold">Aktif</span>
                                    @elseif($link->status === 'pending')
                                    <span class="px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-600 text-[10px] font-semibold">Menunggu</span>
                                    @else
                                    <span class="px-1.5 py-0.5 rounded-md bg-red-50 text-red-500 text-[10px] font-semibold">Ditolak</span>
                                    @endif
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-400">{{ $user->created_at?->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <button onclick="resetWaliPassword({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 text-xs font-semibold transition">
                                <i class="fa-solid fa-key"></i> Reset Password
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
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
