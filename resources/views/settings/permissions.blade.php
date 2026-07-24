@extends('layouts.app')

@section('title', 'Hak Akses Role')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Hak Akses Role</h1>
        <p class="text-sm text-gray-500 mt-1">Atur permission/fitur apa saja yang bisa diakses oleh setiap role pengguna.</p>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-sm p-4">
            <div class="flex items-center gap-3 text-sm font-medium text-emerald-700">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i class="fa-solid fa-check text-emerald-500 text-xs"></i>
                </div>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.permissions.update') }}">
        @csrf

        <div class="space-y-6">
            @foreach ($permissions as $group => $groupPermissions)
                @php
                    $groupLabels = [
                        'akses' => ['Akses Umum', 'fa-door-open', 'blue'],
                        'pelanggaran' => ['Pelanggaran', 'fa-triangle-exclamation', 'red'],
                        'data' => ['Data Master', 'fa-database', 'violet'],
                        'presensi' => ['Presensi', 'fa-clipboard-check', 'emerald'],
                        'master-data' => ['Master Data', 'fa-gear', 'slate'],
                        'administrasi' => ['Administrasi', 'fa-shield-halved', 'amber'],
                    ];
                    $gl = $groupLabels[$group] ?? [ucfirst($group), 'fa-circle', 'gray'];
                @endphp
                <div class="rounded-2xl bg-white border border-gray-200 shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md">
                    {{-- Group header --}}
                    <div class="px-5 py-3.5 bg-gradient-to-r from-{{ $gl[2] }}-50 to-white border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-{{ $gl[2] }}-500 to-{{ $gl[2] }}-600 flex items-center justify-center shadow-sm">
                                <i class="fa-solid {{ $gl[1] }} text-white text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-800">{{ $gl[0] }}</span>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-100">
                                    <th class="text-left px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider min-w-[200px]">Permission</th>
                                    @foreach ($roles as $role)
                                        @php
                                            $roleLabels = [
                                                'admin' => ['Admin', 'blue'],
                                                'bk' => ['BK', 'indigo'],
                                                'wali_kelas' => ['Wali Kelas', 'emerald'],
                                                'staff' => ['Staff', 'amber'],
                                                'other' => ['Guest', 'gray'],
                                            ];
                                            $rl = $roleLabels[$role] ?? [ucfirst($role), 'gray'];
                                        @endphp
                                        <th class="text-center px-3 py-3 min-w-[100px]">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-{{ $rl[1] }}-50 border border-{{ $rl[1] }}-200 shadow-sm">
                                                @if($role === 'admin')
                                                    <i class="fa-solid fa-crown text-[10px] text-{{ $rl[1] }}-500"></i>
                                                @else
                                                    <i class="fa-solid fa-user text-[10px] text-{{ $rl[1] }}-500"></i>
                                                @endif
                                                <span class="text-[11px] font-bold text-{{ $rl[1] }}-700 whitespace-nowrap">{{ $rl[0] }}</span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($groupPermissions as $perm)
                                    @php
                                        $allChecked = true;
                                        foreach ($roles as $role) {
                                            if ($role !== 'admin' && !in_array($perm->id, $rolePermissions[$role] ?? [])) {
                                                $allChecked = false;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <tr class="hover:bg-gradient-to-r hover:from-blue-50/30 hover:to-white transition-all duration-100 group">
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center group-hover:bg-blue-100 transition-all duration-200">
                                                    <i class="fa-solid fa-check text-xs text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-800">{{ $perm->label }}</div>
                                                    <div class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $perm->key }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        @foreach ($roles as $role)
                                            @php
                                                $checked = in_array($perm->id, $rolePermissions[$role] ?? []);
                                                $disabled = ($role === 'admin');
                                            @endphp
                                            <td class="text-center px-3 py-3.5">
                                                @if($disabled)
                                                    <div class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 border border-blue-200">
                                                        <i class="fa-solid fa-lock text-xs text-blue-400"></i>
                                                    </div>
                                                @else
                                                    <label class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg border-2 transition-all duration-150 cursor-pointer
                                                        {{ $checked ? 'bg-blue-500 border-blue-500 shadow-sm shadow-blue-200' : 'bg-white border-gray-200 hover:border-blue-300 hover:bg-blue-50' }}"
                                                        onclick="this.querySelector('input').click(); return false;">
                                                        <input type="checkbox"
                                                            name="permissions[{{ $role }}][]"
                                                            value="{{ $perm->id }}"
                                                            {{ $checked ? 'checked' : '' }}
                                                            onchange="this.parentElement.className = 'relative inline-flex items-center justify-center w-9 h-9 rounded-lg border-2 transition-all duration-150 cursor-pointer ' + (this.checked ? 'bg-blue-500 border-blue-500 shadow-sm shadow-blue-200' : 'bg-white border-gray-200 hover:border-blue-300 hover:bg-blue-50');"
                                                            class="sr-only">
                                                        @if($checked)
                                                            <i class="fa-solid fa-check text-white text-xs"></i>
                                                        @endif
                                                    </label>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bottom bar --}}
        <div class="mt-8 rounded-2xl bg-gradient-to-br from-white to-gray-50/50 border border-gray-200 shadow-sm p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-xs text-gray-400">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center">
                        <i class="fa-solid fa-crown text-xs text-blue-500"></i>
                    </div>
                    <span>Role <strong class="text-gray-600">Admin</strong> selalu memiliki semua hak akses <span class="text-gray-300">(tidak bisa diubah)</span>.</span>
                </div>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Simpan Hak Akses
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
