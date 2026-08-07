@extends('layouts.app')

@section('title', 'Verifikasi Wali Murid')

@section('content')
<div class="space-y-6" x-data="rejectModal()">

    {{-- Header premium --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#065f46] via-[#0d9488] to-[#134e4a] shadow-xl shadow-emerald-500/20">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1.5px, transparent 1.5px); background-size: 22px 22px;"></div>
        <div class="absolute -right-20 -top-24 w-80 h-80 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-24 w-72 h-72 rounded-full bg-teal-300/20 blur-3xl"></div>
        <div class="relative px-6 py-7 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="relative">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur border border-white/25 shadow-inner">
                        <i class="fa-solid fa-user-check text-xl text-white"></i>
                    </div>
                    <span class="absolute -top-1.5 -right-1.5 w-3.5 h-3.5 rounded-full bg-amber-400 border-2 border-emerald-800"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-white tracking-tight">Verifikasi Wali Murid</h1>
                        <span class="px-2.5 py-1 rounded-full bg-white/15 backdrop-blur border border-white/20 text-[10px] font-bold text-white tracking-widest uppercase">SiMURID</span>
                    </div>
                    <p class="text-sm text-emerald-50/90 mt-1">Akun wali yang mendaftar lewat aplikasi &amp; belum terverifikasi otomatis</p>
                </div>
            </div>
            <a href="{{ route('parents.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-700 text-sm font-bold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <i class="fa-solid fa-people-roof"></i> Data Wali Murid
            </a>
        </div>
    </div>

    {{-- Kartu statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-amber-50"></div>
            <div class="flex items-center justify-between relative">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Antrean</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-1.5">{{ $pending->count() }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Menunggu keputusan</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <i class="fa-solid fa-hourglass-half text-lg"></i>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-emerald-50"></div>
            <div class="flex items-center justify-between relative">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Disetujui Hari Ini</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-1.5">{{ $approvedToday }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Akun langsung aktif</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <i class="fa-solid fa-check-double text-lg"></i>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-red-50"></div>
            <div class="flex items-center justify-between relative">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Ditolak</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-1.5">{{ $rejectedTotal }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Total penolakan</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 text-white flex items-center justify-center shadow-lg shadow-rose-500/30">
                    <i class="fa-solid fa-user-slash text-lg"></i>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-blue-50"></div>
            <div class="flex items-center justify-between relative">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Wali</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-1.5">{{ $totalWali }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Akun terdaftar</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-users text-lg"></i>
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

    {{-- Antrean pending --}}
    <div class="bg-white rounded-3xl shadow-lg shadow-slate-200/60 border border-slate-200/70 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-1.5 h-5 rounded-full bg-gradient-to-b from-amber-400 to-orange-500"></span>
                    Antrean Verifikasi
                </h2>
                <p class="text-xs text-slate-400 mt-1">Wali daftar via app → cocokkan data → setujui di sini</p>
            </div>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-100 text-amber-600 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                {{ $pending->count() }} menunggu
            </span>
        </div>

        @if($pending->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-br from-emerald-50 to-teal-100 flex items-center justify-center mb-4">
                <i class="fa-solid fa-check-double text-3xl text-emerald-500"></i>
            </div>
            <p class="font-semibold text-slate-500">Tidak ada antrean 🎉</p>
            <p class="text-sm text-slate-400 mt-1">Semua permintaan verifikasi sudah diproses.</p>
        </div>
        @else
        <div class="divide-y divide-slate-50">
            @foreach($pending as $link)
            <div class="px-6 py-4 flex flex-col lg:flex-row lg:items-center gap-4 hover:bg-emerald-50/30 transition-colors group">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-extrabold text-base shadow-md shadow-emerald-500/25 group-hover:scale-105 transition-transform">
                            {{ strtoupper(substr($link->user->name, 0, 1)) }}
                        </div>
                        @if($link->relation === 'father')
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-sky-100 border-2 border-white flex items-center justify-center">
                            <i class="fa-solid fa-person text-[7px] text-sky-500"></i>
                        </span>
                        @elseif($link->relation === 'mother')
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-pink-100 border-2 border-white flex items-center justify-center">
                            <i class="fa-solid fa-person-dress text-[7px] text-pink-500"></i>
                        </span>
                        @elseif($link->relation === 'guardian')
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-violet-100 border-2 border-white flex items-center justify-center">
                            <i class="fa-solid fa-shield-halved text-[7px] text-violet-500"></i>
                        </span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate flex items-center gap-2">
                            {{ $link->user->name }}
                            @if($link->relation === 'father')
                            <span class="px-1.5 py-0.5 rounded-md bg-sky-50 border border-sky-100 text-sky-600 text-[9px] font-bold">AYAH</span>
                            @elseif($link->relation === 'mother')
                            <span class="px-1.5 py-0.5 rounded-md bg-pink-50 border border-pink-100 text-pink-600 text-[9px] font-bold">IBU</span>
                            @elseif($link->relation === 'guardian')
                            <span class="px-1.5 py-0.5 rounded-md bg-violet-50 border border-violet-100 text-violet-600 text-[9px] font-bold">WALI</span>
                            @endif
                        </p>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-solid fa-graduation-cap text-slate-300"></i>
                                <span class="font-medium text-slate-700">{{ $link->student->full_name }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1 font-mono text-[11px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded-md">
                                NISN {{ $link->student->nisn }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-regular fa-building text-slate-300"></i>
                                {{ $link->student->class_name }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            <i class="fa-regular fa-clock mr-1"></i>Daftar {{ $link->created_at?->diffForHumans() }}
                            @if($link->user->phone)
                            · <i class="fa-solid fa-phone mr-0.5"></i>{{ $link->user->phone }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 lg:pl-4">
                    <button onclick="approveWali({{ $link->id }}, '{{ addslashes($link->user->name) }}', '{{ addslashes($link->student->full_name) }}')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold shadow-md shadow-emerald-500/30 hover:shadow-lg hover:-translate-y-0.5 hover:brightness-105 transition-all">
                        <i class="fa-solid fa-check"></i> Setujui
                    </button>
                    <button @click="openReject({{ $link->id }}, '{{ addslashes($link->user->name) }}')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-rose-200 text-rose-600 text-sm font-bold shadow-sm hover:shadow-md hover:-translate-y-0.5 hover:bg-rose-50 transition-all">
                        <i class="fa-solid fa-xmark"></i> Tolak
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Riwayat terakhir --}}
    @if($recent->isNotEmpty())
    <div class="bg-white rounded-3xl shadow-lg shadow-slate-200/60 border border-slate-200/70 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="font-bold text-gray-900 flex items-center gap-2">
                <span class="w-1.5 h-5 rounded-full bg-gradient-to-b from-slate-400 to-slate-600"></span>
                Terakhir Diproses
            </h2>
            <p class="text-xs text-slate-400 mt-1">20 verifikasi terakhir</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-slate-50 to-emerald-50/40">
                    <tr class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-3.5">Wali</th>
                        <th class="px-6 py-3.5">Siswa</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Diproses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($recent as $link)
                    <tr class="hover:bg-emerald-50/20 transition-colors">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($link->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $link->user->name }}</p>
                                    @if($link->user->phone)
                                    <p class="text-[11px] text-slate-400">{{ $link->user->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-slate-600">
                            {{ $link->student->full_name }}
                            <span class="text-slate-400">({{ $link->student->class_name }})</span>
                        </td>
                        <td class="px-6 py-3.5">
                            @if($link->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-[11px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 border border-red-100 text-red-500 text-[11px] font-bold" title="{{ $link->rejection_reason }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Ditolak
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-slate-500 text-xs">{{ $link->verified_at?->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Modal Tolak (premium) --}}
    <div x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[70] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md mx-4">
                {{-- Header --}}
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow-lg shadow-rose-500/25">
                            <i class="fa-solid fa-user-slash text-white text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900">Tolak Akun Wali</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Wali tidak akan bisa memantau putra/putri</p>
                        </div>
                    </div>
                    <button @click="show = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6">
                    <div class="rounded-2xl bg-rose-50/70 border border-rose-100 px-4 py-3.5 mb-4 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-white border border-rose-100 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-xs"></i>
                        </div>
                        <div class="text-xs text-rose-700 leading-relaxed">
                            Anda akan menolak akun <span class="font-bold" x-text="name"></span>.
                            Wali tetap bisa login, namun tautan putra/putri ditolak dan ia dapat mengajukan ulang.
                        </div>
                    </div>

                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alasan Penolakan <span class="font-normal text-slate-400">(opsional, tampil ke wali)</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-comment-dots absolute left-3.5 top-3.5 text-slate-300 text-sm pointer-events-none"></i>
                        <textarea x-model="reason" rows="4" maxlength="255"
                                  class="w-full pl-9 pr-12 rounded-xl border-slate-200 text-sm bg-gray-50/50 focus:bg-white focus:border-rose-400 focus:ring-rose-500/20 transition resize-none"
                                  placeholder="cth: No. HP tidak cocok dengan data sekolah"></textarea>
                        <span class="absolute right-3 bottom-3 text-[10px] font-semibold text-slate-300" x-text="reason.length + '/255'">0/255</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5"><i class="fa-solid fa-circle-info mr-1"></i>Alasan ini langsung tampil di banner aplikasi wali.</p>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-slate-50/60 border-t border-gray-100 rounded-b-3xl flex items-center justify-end gap-3">
                    <button type="button" @click="show = false"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="button" @click="confirmReject()" :disabled="loading"
                            class="px-5 py-2.5 text-sm font-black text-white bg-gradient-to-r from-rose-500 to-red-600 rounded-xl hover:brightness-105 transition shadow-md shadow-rose-200 inline-flex items-center gap-2 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed">
                        <i class="fa-solid" :class="loading ? 'fa-circle-notch fa-spin' : 'fa-ban'"></i>
                        <span x-text="loading ? 'Menolak...' : 'Ya, Tolak'">Ya, Tolak</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function approveWali(linkId, userName, studentName) {
    Swal.fire({
        title: 'Setujui akun wali?',
        html: '<b>' + userName + '</b> akan diverifikasi sebagai wali dari<br><b>' + studentName + '</b>. Akun langsung aktif.',
        icon: 'question',
        confirmButtonText: '<i class="fa-solid fa-check"></i> Ya, Setujui',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        confirmButtonColor: '#10b981',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ url('parents/verification') }}/' + linkId + '/approve', {
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

function rejectModal() {
    return {
        show: false,
        loading: false,
        id: null,
        name: '',
        reason: '',
        openReject(linkId, userName) {
            this.id = linkId;
            this.name = userName;
            this.reason = '';
            this.show = true;
        },
        confirmReject() {
            if (this.loading) return;
            this.loading = true;
            fetch('{{ url('parents/verification') }}/' + this.id + '/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ reason: this.reason }),
            })
            .then(r => {
                if (r.redirected) window.location.href = r.url;
                else location.reload();
            })
            .catch(() => {
                this.loading = false;
                this.show = false;
            });
        },
    };
}
</script>
@endpush
@endsection
