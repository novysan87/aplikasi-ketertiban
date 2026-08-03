@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div x-data="userManager()">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen User</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola pengguna yang berhak mengakses aplikasi</p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
            <i class="fa-solid fa-plus mr-2"></i>
            Tambah User
        </button>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <form method="GET" class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama, username, atau email..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>
                <select name="role"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition bg-white">
                    <option value="">Semua Role</option>
                    <option value="admin" @selected(request('role') == 'admin')>Admin</option>
                    <option value="bk" @selected(request('role') == 'bk')>BK</option>
                    <option value="wali_kelas" @selected(request('role') == 'wali_kelas')>Wali Kelas</option>
                    <option value="staff" @selected(request('role') == 'staff')>Staff</option>
                    <option value="kepala_sekolah" @selected(request('role') == 'kepala_sekolah')>Kepala Sekolah</option>
                    <option value="waka_kesiswaan" @selected(request('role') == 'waka_kesiswaan')>Waka Kesiswaan</option>
                    <option value="ketua_tim" @selected(request('role') == 'ketua_tim')>Ketua Tim</option>
                    <option value="other" @selected(request('role') == 'other')>Other</option>
                </select>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition">
                        <i class="fa-solid fa-filter mr-1.5"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search','role']))
                        <a href="{{ route('users.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition {{ !$user->is_active ? 'opacity-60' : '' }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 bg-blue-100 text-blue-600">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                        @if ($user->phone)
                                            <p class="text-xs text-gray-400 mt-0.5"><i class="fa-solid fa-phone text-[10px]"></i> {{ $user->phone }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 font-mono">{{ $user->username }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($user->roleList() as $r)
                                        @php
                                            $roleBadges = [
                                                'admin' => ['bg-blue-100 text-blue-700', 'fa-shield-halved'],
                                                'bk' => ['bg-cyan-100 text-cyan-700', 'fa-user-tie'],
                                                'wali_kelas' => ['bg-emerald-100 text-emerald-700', 'fa-chalkboard-user'],
                                                'staff' => ['bg-violet-100 text-violet-700', 'fa-user-gear'],
                                                'kepala_sekolah' => ['bg-amber-100 text-amber-700', 'fa-crown'],
                                                'waka_kesiswaan' => ['bg-rose-100 text-rose-700', 'fa-user-graduate'],
                                                'ketua_tim' => ['bg-teal-100 text-teal-700', 'fa-user-group'],
                                                'other' => ['bg-gray-100 text-gray-700', 'fa-user'],
                                            ];
                                            $rb = $roleBadges[$r] ?? ['bg-gray-100 text-gray-700', 'fa-user'];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full {{ $rb[0] }}">
                                            <i class="fa-solid {{ $rb[1] }} mr-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $r)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end space-x-1">
                                    <button @click="openEdit({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->username }}', '{{ $user->email }}', '{{ addslashes($user->phone ?? '') }}', '{{ implode(',', $user->roleList()) }}', {{ $user->is_active ? 'true' : 'false' }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="resetPassword({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Reset Password">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('users.force-logout', $user->id) }}" method="POST" class="inline"
                                          x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Akhiri sesi login ' + '{{ addslashes($user->name) }}' + '? User harus login ulang.'})) $el.submit()">
                                        @csrf
                                        <button type="submit"
                                            class="p-2 rounded-lg transition {{ $user->active_session_token ? 'text-slate-500 hover:bg-slate-100' : 'text-gray-300 cursor-not-allowed' }}"
                                            title="{{ $user->active_session_token ? 'Paksa logout (akhiri sesi aktif)' : 'Tidak ada sesi aktif' }}"
                                            @if(!$user->active_session_token) disabled @endif>
                                            <i class="fa-solid fa-right-from-bracket"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('users.send-wa', $user->id) }}" method="POST" class="inline"
                                          x-data="sendWaData({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->phone ? 'true' : 'false' }})"
                                          x-on:submit.prevent="confirmAndSubmit()">
                                        @csrf
                                        <button type="submit"
                                            class="p-2 rounded-lg transition {{ $user->phone ? 'text-emerald-600 hover:bg-emerald-50' : 'text-gray-300 cursor-not-allowed' }}"
                                            title="{{ $user->phone ? 'Kirim akun via WhatsApp' : 'Isi nomor HP dulu untuk kirim WhatsApp' }}"
                                            @if(!$user->phone) disabled @endif>
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline"
                                        x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus user ini?'})) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-users text-gray-300 text-4xl mb-3"></i>
                                <p class="text-sm text-gray-500">Tidak ada user ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-between text-xs text-gray-500">
            <span>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }}</span>
            <span>{{ $users->total() }} total</span>
        </div>
    </div>

{{-- Modal --}}
    <div x-show="modalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-xl mx-4 max-h-[92vh] overflow-y-auto">
                {{-- Header hero --}}
                <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-500 to-sky-400 px-7 py-6 sticky top-0 z-10">
                    <div class="pointer-events-none absolute -top-10 -right-10 w-44 h-44 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute -bottom-14 -left-8 w-40 h-40 rounded-full bg-sky-300/20 blur-3xl"></div>
                    <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
                        style="background-image: radial-gradient(circle at 25% 40%, #fff 1.5px, transparent 1.5px); background-size: 20px 20px;"></div>
                    <div class="relative z-10 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center ring-1 ring-white/25 backdrop-blur-sm">
                                <i class="fa-solid text-white text-xl" :class="isEditing ? 'fa-pen-to-square' : 'fa-user-plus'"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-white" x-text="isEditing ? 'Edit User' : 'Tambah User'"></h3>
                                <p class="text-xs text-white/75" x-text="isEditing ? 'Ubah data pengguna' : 'Buat akun pengguna baru'"></p>
                            </div>
                        </div>
                        <button @click="modalOpen = false"
                            class="w-9 h-9 rounded-xl bg-white/10 ring-1 ring-white/25 flex items-center justify-center text-white hover:bg-white/25 transition flex-shrink-0">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                {{-- Form --}}
                <form :action="isEditing ? `/users/${editId}` : '{{ route('users.store') }}'" method="POST" class="p-7">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">

                    <div class="space-y-5">
                        {{-- Nama --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                <input type="text" x-model="formName" name="name" required
                                    class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-2xl text-sm font-semibold bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition"
                                    placeholder="Nama lengkap">
                            </div>
                        </div>

                        {{-- Username + Email --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                    <input type="text" x-model="formUsername" name="username" required
                                        class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-2xl text-sm font-semibold bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition"
                                        placeholder="username">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                    <input type="email" x-model="formEmail" name="email" required
                                        class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-2xl text-sm font-semibold bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition"
                                        placeholder="email@sekolah.sch.id">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Nomor HP</label>
                                <div class="relative">
                                    <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                    <input type="tel" x-model="formPhone" name="phone"
                                        class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-2xl text-sm font-semibold bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition"
                                        placeholder="08xxxxxxxxxx">
                                </div>
                                <p class="text-xs text-gray-400 mt-1.5">Nomor untuk kontak &amp; notifikasi (opsional)</p>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Password <span class="text-red-500">*</span>
                                <span x-show="isEditing" class="text-xs font-normal text-gray-400">(kosongkan jika tidak diubah)</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                <input :type="showPassword ? 'text' : 'password'" x-model="formPassword" name="password" :required="!isEditing" minlength="6"
                                    class="w-full pl-11 pr-12 py-3 border-2 border-gray-200 rounded-2xl text-sm font-semibold bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition"
                                    placeholder="Min. 6 karakter">
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition">
                                    <i class="fa-solid text-sm" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Role (multi) --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Role / Hak Akses <span class="text-red-500">*</span> <span class="text-xs font-normal text-gray-400">(bisa pilih lebih dari satu)</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <template x-for="r in [
                                    { v: 'admin', label: 'Admin', desc: 'Full akses', icon: 'fa-user-shield', color: '#2563eb' },
                                    { v: 'bk', label: 'BK', desc: 'Input & laporan', icon: 'fa-user-tie', color: '#14b8a6' },
                                    { v: 'wali_kelas', label: 'Wali Kelas', desc: 'Lihat kelas sendiri', icon: 'fa-chalkboard-user', color: '#10b981' },
                                    { v: 'staff', label: 'Staff', desc: 'Terbatas', icon: 'fa-user-gear', color: '#8b5cf6' },
                                    { v: 'kepala_sekolah', label: 'Kepala Sekolah', desc: 'Pimpinan / persetujuan', icon: 'fa-crown', color: '#f59e0b' },
                                    { v: 'waka_kesiswaan', label: 'Waka Kesiswaan', desc: 'Pengelola kesiswaan', icon: 'fa-user-graduate', color: '#f43f5e' },
                                    { v: 'ketua_tim', label: 'Ketua Tim', desc: 'Koordinator tim', icon: 'fa-user-group', color: '#14b8a6' },
                                    { v: 'other', label: 'Other', desc: 'Guest / Terbatas', icon: 'fa-user', color: '#64748b' },
                                ]" :key="r.v">
                                    <label class="flex items-center gap-3 rounded-2xl border-2 p-3 cursor-pointer transition-all duration-150 active:scale-[0.98]"
                                        :class="formRoles.includes(r.v) ? 'border-transparent text-white shadow-md ring-2' : 'border-gray-200 bg-gray-50/50 hover:border-gray-300'"
                                        :style="formRoles.includes(r.v) ? 'background: linear-gradient(135deg, ' + r.color + ', ' + r.color + 'bb);' : ''">
                                        <input type="checkbox" :name="'roles[]'" :value="r.v" x-model="formRoles" class="sr-only">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                            :class="formRoles.includes(r.v) ? 'bg-white/20' : 'bg-white shadow-sm border border-gray-200'">
                                            <i :class="'fa-solid ' + r.icon" class="text-sm" :class="formRoles.includes(r.v) ? 'text-white' : 'text-gray-400'"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold" :class="formRoles.includes(r.v) ? 'text-white' : 'text-gray-800'" x-text="r.label"></p>
                                            <p class="text-[10px]" :class="formRoles.includes(r.v) ? 'text-white/70' : 'text-gray-400'" x-text="r.desc"></p>
                                        </div>
                                        <i class="fa-solid fa-circle-check ml-auto text-base"
                                            :class="formRoles.includes(r.v) ? 'text-white' : 'text-gray-200'"></i>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Aktif (edit) --}}
                        <div x-show="isEditing" x-transition class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-white shadow-sm border border-gray-200 flex items-center justify-center">
                                    <i class="fa-solid fa-power-off text-emerald-500 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Akun Aktif</p>
                                    <p class="text-xs text-gray-500">Nonaktifkan untuk menonaktifkan akun</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="formActive" name="is_active" value="1" class="sr-only">
                                <span class="relative h-6 w-11 rounded-full transition-colors duration-200" :class="formActive ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gray-300'">
                                    <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200" :class="formActive ? 'translate-x-5' : ''"></span>
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3 mt-7 pt-5 border-t border-gray-100">
                        <button type="button" @click="modalOpen = false"
                            class="px-6 py-3 text-sm font-bold text-gray-600 bg-white border-2 border-gray-200 rounded-2xl hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-7 py-3 text-sm font-black text-white bg-gradient-to-r from-blue-600 to-sky-500 rounded-2xl shadow-lg shadow-blue-200 hover:-translate-y-0.5 hover:brightness-105 transition-all inline-flex items-center gap-2 active:scale-95">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span x-text="isEditing ? 'Simpan Perubahan' : 'Tambah User'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function sendWaData(userId, userName, hasPhone) {
    return {
        confirmAndSubmit() {
            if (!hasPhone) return;
            const form = this.$el.closest('form');
            Swal.fire({
                title: 'Kirim Akun via WhatsApp',
                html: 'Kirim link aplikasi, username, dan <b>password baru</b> ke <b>' + userName + '</b>?' +
                      '<br><br><span style="font-size:12px;color:#6b7280">Password baru akan dibuat otomatis (password lama tidak bisa dikirim karena tersimpan ter-enkripsi).</span>',
                icon: 'question',
                confirmButtonText: '<i class="fa-brands fa-whatsapp"></i> Ya, Kirim',
                confirmButtonColor: '#25D366',
                showCancelButton: true,
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed || !form) return;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json().catch(() => ({ error: 'Respons tidak valid dari server' })))
                .then(data => {
                    if (data.error) {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.error });
                        return;
                    }
                    const msgLines = [
                        '<b>📲 Akun Aplikasi ' + (data.app_name || '') + '</b>',
                        'Yth. ' + (data.name || userName) + ',',
                        'Link: ' + (data.app_url || ''),
                        'Username: <b>' + (data.username || '') + '</b>',
                        'Password: <b>' + (data.password || '') + '</b>',
                        '<span style="color:#6b7280;font-size:12px">Mohon segera ganti password setelah login.</span>',
                    ].join('<br>');
                    Swal.fire({
                        icon: 'success',
                        title: 'Akun Siap Dikirim',
                        html: '<div style="text-align:left;font-size:13px;line-height:1.8">' + msgLines + '</div>' +
                              '<a href="' + data.url + '" target="_blank" rel="noopener" ' +
                              'style="display:inline-block;margin-top:16px;padding:11px 26px;background:#25D366;color:#fff;' +
                              'border-radius:12px;font-weight:700;text-decoration:none;font-size:14px">' +
                              '<i class="fa-brands fa-whatsapp"></i> Buka WhatsApp</a>' +
                              '<p style="font-size:11px;color:#9ca3af;margin-top:10px">WhatsApp dibuka di tab baru — aplikasi tetap terbuka.</p>',
                        showConfirmButton: false,
                        showCloseButton: true,
                    });
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menghubungi server.' });
                });
            });
        }
    };
}

function userManager() {
    return {
        modalOpen: false, isEditing: false, editId: null,
        formName: '', formUsername: '', formEmail: '', formPhone: '', formPassword: '', formRoles: ['bk'], formActive: true,
        showPassword: false,
        get roleInfo() {
            const map = {
                admin: { icon: 'fa-user-shield', color: '#2563eb' },
                bk: { icon: 'fa-user-tie', color: '#14b8a6' },
                wali_kelas: { icon: 'fa-chalkboard-user', color: '#10b981' },
                staff: { icon: 'fa-user-gear', color: '#8b5cf6' },
                kepala_sekolah: { icon: 'fa-crown', color: '#f59e0b' },
                waka_kesiswaan: { icon: 'fa-user-graduate', color: '#f43f5e' },
                ketua_tim: { icon: 'fa-user-group', color: '#14b8a6' },
                other: { icon: 'fa-user', color: '#64748b' },
            };
            const primary = this.formRoles[0] || 'bk';
            return map[primary] || { icon: 'fa-user', color: '#64748b' };
        },
        openCreate() {
            this.isEditing = false; this.editId = null;
            this.formName = ''; this.formUsername = ''; this.formEmail = ''; this.formPhone = ''; this.formPassword = '';
            this.formRoles = ['bk']; this.formActive = true; this.showPassword = false; this.modalOpen = true;
        },
        openEdit(id, name, username, email, phone, rolesCsv, active) {
            this.isEditing = true; this.editId = id;
            this.formName = name; this.formUsername = username; this.formEmail = email; this.formPhone = phone;
            this.formPassword = '';
            this.formRoles = rolesCsv ? rolesCsv.split(',') : ['bk'];
            this.formActive = active; this.showPassword = false; this.modalOpen = true;
        }
    };
}
    function resetPassword(userId, userName) {
        Swal.fire({
            title: 'Reset Password untuk ' + userName,
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
