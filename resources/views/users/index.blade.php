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
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                                        @if($user->role === 'admin') bg-blue-100 text-blue-600
                                        @elseif($user->role === 'bk') bg-blue-100 text-blue-600
                                        @elseif($user->role === 'wali_kelas') bg-green-100 text-green-600
                                        @else bg-gray-100 text-gray-600 @endif">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 font-mono">{{ $user->username }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full
                                    @if($user->role === 'admin') bg-blue-100 text-red-700
                                    @elseif($user->role === 'bk') bg-blue-100 text-blue-700
                                    @elseif($user->role === 'wali_kelas') bg-green-100 text-green-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    @if($user->role === 'admin') <i class="fa-solid fa-shield-halved mr-1"></i>
                                    @elseif($user->role === 'bk') <i class="fa-solid fa-user-tie mr-1"></i>
                                    @elseif($user->role === 'wali_kelas') <i class="fa-solid fa-chalkboard-user mr-1"></i>
                                    @else <i class="fa-solid fa-user mr-1"></i> @endif
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
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
                                    <button @click="openEdit({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->username }}', '{{ $user->email }}', '{{ $user->role }}', {{ $user->is_active ? 'true' : 'false' }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline"
                                        x-data x-on:submit.prevent="if(await window.confirmSwal({text:'Hapus user ini?'})) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                    @endif
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
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>
            <div 
                class="relative inline-block align-bottom bg-white rounded-2xl shadow-xl border border-gray-200 text-left overflow-hidden transform transition-all sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="isEditing ? 'bg-blue-100' : 'bg-blue-100'">
                            <i class="fa-solid" :class="isEditing ? 'fa-pen-to-square text-blue-600' : 'fa-user-plus text-blue-600'"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="isEditing ? 'Edit User' : 'Tambah User'"></h3>
                            <p class="text-sm text-gray-500" x-text="isEditing ? 'Ubah data pengguna' : 'Buat akun pengguna baru'"></p>
                        </div>
                    </div>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                {{-- Form --}}
                <form :action="isEditing ? `/users/${editId}` : '{{ route('users.store') }}'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                            <input type="text" x-model="formName" name="name" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                placeholder="Nama lengkap">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                            <input type="text" x-model="formUsername" name="username" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                placeholder="username">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                            <input type="email" x-model="formEmail" name="email" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                placeholder="email@sekolah.sch.id">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Password <span x-text="isEditing ? '(kosongkan jika tidak diubah)' : ''" class="text-gray-400 font-normal"></span>
                            </label>
                            <input type="password" x-model="formPassword" name="password" :required="!isEditing" minlength="6"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                placeholder="Min. 6 karakter">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Role / Hak Akses</label>
                            <select x-model="formRole" name="role" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition bg-white">
                                <option value="bk">BK — Input & laporan</option>
                                <option value="admin">Admin — Full akses</option>
                                <option value="wali_kelas">Wali Kelas — Lihat kelas sendiri</option>
                                <option value="staff">Staff — Terbatas</option>
                            </select>
                        </div>
                    </div>

                    <div x-show="isEditing" class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Akun Aktif</p>
                            <p class="text-xs text-gray-500">Nonaktifkan untuk menonaktifkan akun</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="formActive" name="is_active" value="1" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="modalOpen = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">Batal</button>
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white rounded-xl transition shadow-sm"
                            :class="isEditing ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-600 hover:bg-blue-700'"
                            x-text="isEditing ? 'Simpan Perubahan' : 'Tambah User'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function userManager() {
    return {
        modalOpen: false, isEditing: false, editId: null,
        formName: '', formUsername: '', formEmail: '', formPassword: '', formRole: 'bk', formActive: true,
        openCreate() {
            this.isEditing = false; this.editId = null;
            this.formName = ''; this.formUsername = ''; this.formEmail = ''; this.formPassword = '';
            this.formRole = 'bk'; this.formActive = true; this.modalOpen = true;
        },
        openEdit(id, name, username, email, role, active) {
            this.isEditing = true; this.editId = id;
            this.formName = name; this.formUsername = username; this.formEmail = email;
            this.formPassword = ''; this.formRole = role; this.formActive = active; this.modalOpen = true;
        }
    };
}
</script>
@endpush
@endsection
