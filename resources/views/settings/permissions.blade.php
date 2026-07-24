@extends('layouts.app')

@section('title', 'Hak Akses Role')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Hak Akses Role</h1>
        <p class="text-sm text-gray-500 mt-1">Atur permission/fitur apa saja yang bisa diakses oleh setiap role pengguna.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.permissions.update') }}">
        @csrf

        <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-200">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Permission</th>
                        @foreach ($roles as $role)
                            @php
                                $label = [
                                    'admin' => 'Admin',
                                    'bk' => 'BK',
                                    'wali_kelas' => 'Wali Kelas',
                                    'staff' => 'Staff',
                                    'other' => 'Other (Guest)',
                                ][$role] ?? ucfirst($role);
                            @endphp
                            <th class="text-center px-3 py-3 font-semibold text-gray-700">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $group => $groupPermissions)
                        <tr class="bg-gray-100 border-b border-gray-200">
                            <td colspan="{{ count($roles) + 1 }}" class="px-4 py-2 font-bold text-gray-700 uppercase text-xs tracking-wider">
                                {{ $group === 'master-data' ? 'Master Data' : ($group === 'administrasi' ? 'Administrasi' : $group) }}
                            </td>
                        </tr>
                        @foreach ($groupPermissions as $perm)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $perm->label }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $perm->key }}</div>
                                </td>
                                @foreach ($roles as $role)
                                    @php
                                        $checked = in_array($perm->id, $rolePermissions[$role] ?? []);
                                        $disabled = ($role === 'admin');
                                    @endphp
                                    <td class="text-center px-3 py-3">
                                        <input type="checkbox"
                                            name="permissions[{{ $role }}][]"
                                            value="{{ $perm->id }}"
                                            {{ $checked ? 'checked' : '' }}
                                            {{ $disabled ? 'disabled' : '' }}
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <p class="text-xs text-gray-400">Role <strong>Admin</strong> selalu memiliki semua hak akses (tidak bisa diubah).</p>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                Simpan Hak Akses
            </button>
        </div>
    </form>
</div>
@endsection
