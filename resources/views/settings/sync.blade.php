@extends('layouts.app')

@section('title', 'Sinkronisasi Data Siswa')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Sinkronisasi Data Siswa</h1>
        <p class="text-sm text-gray-500">Sinkronkan data siswa dari Database Kesiswaan</p>
    </div>

    {{-- Sync Database Kesiswaan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form action="{{ route('settings.sync.run') }}" method="POST" class="p-6 space-y-5">
            @csrf

            {{-- URL Kesiswaan --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">URL Database Kesiswaan</label>
                <input type="url" name="base_url" value="{{ old('base_url', $baseUrl) }}" required
                    placeholder="http://database-kesiswaan.local"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                @error('base_url') <p class="mt-1 text-sm text-blue-600">{{ $message }}</p> @enderror
                @if($baseUrl)
                    <p class="mt-1.5 text-xs text-green-600 flex items-center">
                        <i class="fa-solid fa-check"></i>
                        URL tersimpan: {{ $baseUrl }}
                    </p>
                @endif
            </div>

            {{-- Token display --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Token Sinkronisasi</label>
                @if($hasToken)
                    <div class="flex items-center gap-2" id="kesiswaan-token-row">
                        <code class="flex-1 block bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm font-mono text-gray-800 select-all" id="kesiswaanTokenDisplay">{{ $savedToken }}</code>
                        <button type="button" onclick="copyKesiswaanToken()"
                            class="shrink-0 px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                            <i class="fa-regular fa-copy"></i> Salin
                        </button>
                        <button type="button" onclick="gantiKesiswaanToken()"
                            class="shrink-0 px-3 py-2 text-xs font-medium text-amber-600 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                            <i class="fa-solid fa-pen"></i> Ganti
                        </button>
                    </div>
                    <input type="hidden" name="token" value="{{ $savedToken }}" id="kesiswaanTokenHidden">
                    <div id="kesiswaan-token-edit" class="hidden">
                        <div class="flex items-center gap-2">
                            <input type="text" name="token_new" value="{{ $savedToken }}"
                                class="flex-1 px-4 py-2.5 border border-amber-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                                id="kesiswaanTokenInput">
                            <button type="button" onclick="simpanKesiswaanToken()"
                                class="shrink-0 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition shadow-sm inline-flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Simpan
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-amber-600">Edit token, lalu klik Simpan.</p>
                    </div>
                    <p class="mt-1.5 text-xs text-green-600 flex items-center">
                        <i class="fa-solid fa-check"></i>
                        Token tersimpan. Klik <strong>"Ganti"</strong> untuk mengubah.
                    </p>
                @else
                    <input type="password" name="token" value=""
                        placeholder="Masukkan token akses"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                        id="tokenInput">
                    <p class="mt-1 text-xs text-gray-500">Token didapatkan dari menu Pengaturan Database Kesiswaan.</p>
                @endif
            </div>

            {{-- Info siswa --}}
            @if($studentCount > 0)
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-info"></i>
                        <div class="text-sm text-blue-700">
                            <strong>{{ $studentCount }}</strong> siswa sudah tersinkron. Sinkronisasi ulang akan memperbarui data yang sudah ada.
                        </div>
                    </div>
                </div>
            @endif

            {{-- Status + Test Connection --}}
            @if($hasToken && $baseUrl)
                <div id="kesiswaan-status" class="p-4 bg-slate-50 border border-slate-200 rounded-xl" data-saved-url="{{ $baseUrl }}" data-saved-token="{{ $savedToken }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span id="kesiswaan-status-dot" class="inline-block w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                <span id="kesiswaan-status-text" class="text-sm font-semibold text-slate-500">Belum dites</span>
                            </div>
                            <p id="kesiswaan-status-detail" class="mt-1 text-xs text-slate-400">Klik "Test Koneksi" untuk verifikasi.</p>
                        </div>
                        <button type="button" id="test-kesiswaan-btn"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition">
                            <i class="fa-solid fa-plug"></i>
                            Test Koneksi
                        </button>
                    </div>
                </div>
            @endif

            {{-- Tombol --}}
            <button type="submit"
                class="w-full px-4 py-3 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm flex items-center justify-center space-x-2">
                <i class="fa-solid fa-rotate"></i>
                <span>Jalankan Sinkronisasi</span>
            </button>
        </form>
    </div>

    {{-- E-Jurnal Sync Token --}}
    <div class="mt-8"></div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-4">
        <div class="p-6 space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Sinkronisasi Presensi E-Jurnal</h2>
                <p class="text-sm text-gray-500 mt-1">Generate token untuk diinput di Aplikasi E-Jurnal Guru agar bisa push data presensi.</p>
            </div>

            {{-- Token display --}}
            @if($hasEjurnalToken)
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5">
                            <i class="fa-solid fa-check-circle text-emerald-500"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-emerald-800">Token Aktif</div>
                            <div class="mt-2 flex items-center gap-2">
                                <code class="flex-1 block bg-white border border-emerald-200 rounded-lg px-3 py-2 text-sm font-mono text-emerald-900 select-all break-all" id="ejurnalTokenDisplay">{{ $ejurnalToken }}</code>
                                <button type="button" id="copyTokenBtn" onclick="copyToken2()"
                                    class="shrink-0 px-3 py-2 text-xs font-medium text-emerald-700 bg-emerald-100 border border-emerald-200 rounded-lg hover:bg-emerald-200 transition whitespace-nowrap">
                                    <i class="fa-regular fa-copy"></i> Salin
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-emerald-700">Salin token ini dan paste di menu Sinkronisasi Akademik → Aplikasi Ketertiban di E-Jurnal Guru.</p>
                        </div>
                    </div>
                </div>

                {{-- Regenerate --}}
                <form action="{{ route('settings.sync.ejurnal-token.generate') }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Regenerate token? Token lama akan langsung tidak berlaku.')"
                        class="w-full px-4 py-3 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate"></i>
                        Regenerate Token
                    </button>
                </form>

            @else
                {{-- Generate first token --}}
                <form action="{{ route('settings.sync.ejurnal-token.generate') }}" method="POST">
                    @csrf
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center">
                        <div class="text-sm text-slate-500 mb-3">Belum ada token. Generate satu sekarang:</div>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition shadow-sm">
                            <i class="fa-solid fa-key"></i>
                            Generate Token Sekarang
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
    function copyToken2() {
        const el = document.getElementById('ejurnalTokenDisplay');
        const btn = document.getElementById('copyTokenBtn');
        if (!el || !btn) return;

        // Select the text
        const range = document.createRange();
        range.selectNodeContents(el);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);

        // Copy using execCommand (works on HTTP too)
        try {
            document.execCommand('copy');
            sel.removeAllRanges();
            btn.innerHTML = '<i class="fa-regular fa-check"></i> Tersalin';
            setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy"></i> Salin'; }, 2000);
        } catch (err) {
            // Fallback: show manual instruction
            alert('Tekan Ctrl+C (Cmd+C di Mac) untuk menyalin token.');
        }
    }

    function copyKesiswaanToken() {
        const el = document.getElementById('kesiswaanTokenDisplay');
        if (!el) return;
        navigator.clipboard.writeText(el.textContent).then(() => {
            const parent = el.parentElement;
            if (parent) {
                const btns = parent.querySelectorAll('button');
                btns.forEach(b => {
                    if (b.innerHTML.includes('Salin')) {
                        b.innerHTML = '<i class="fa-regular fa-check"></i> Tersalin';
                        setTimeout(() => { b.innerHTML = '<i class="fa-regular fa-copy"></i> Salin'; }, 2000);
                    }
                });
            }
        });
    }

    function gantiKesiswaanToken() {
        document.getElementById('kesiswaan-token-row').classList.add('hidden');
        document.getElementById('kesiswaan-token-edit').classList.remove('hidden');
        document.getElementById('kesiswaanTokenHidden').disabled = true;
    }

    function simpanKesiswaanToken() {
        const newToken = document.getElementById('kesiswaanTokenInput').value;
        document.getElementById('kesiswaanTokenHidden').value = newToken;
        document.getElementById('kesiswaanTokenHidden').disabled = false;
        // Submit the parent form
        const form = document.getElementById('kesiswaanTokenInput').closest('form');
        if (form) form.submit();
    }

    // On form submit: copy token_new to hidden token field
    document.addEventListener('submit', function(e) {
        const editDiv = document.getElementById('kesiswaan-token-edit');
        if (editDiv && !editDiv.classList.contains('hidden')) {
            const newToken = document.getElementById('kesiswaanTokenInput').value;
            document.getElementById('kesiswaanTokenHidden').value = newToken;
            document.getElementById('kesiswaanTokenHidden').disabled = false;
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const testBtn = document.getElementById('test-kesiswaan-btn');
        const st = document.getElementById('kesiswaan-status');
        const dot = document.getElementById('kesiswaan-status-dot');
        const txt = document.getElementById('kesiswaan-status-text');
        const det = document.getElementById('kesiswaan-status-detail');

        if (testBtn && st && dot && txt && det) {
            testBtn.addEventListener('click', async function () {
                const url = (st.dataset.savedUrl || '').replace(/\/+$/, '');
                const token = st.dataset.savedToken || '';

                testBtn.disabled = true;
                testBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Testing...';
                st.className = 'p-4 bg-slate-50 border border-slate-200 rounded-xl';
                dot.className = 'inline-block w-2.5 h-2.5 rounded-full bg-slate-300';
                txt.className = 'text-sm font-semibold text-slate-500';
                txt.textContent = 'Menguji koneksi...';
                det.textContent = 'Menghubungi ' + url + '...';

                try {
                    const resp = await fetch('{{ route('settings.sync.test') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        },
                        body: JSON.stringify({ base_url: url, token: token }),
                    });
                    const data = await resp.json();

                    if (resp.ok && data.success) {
                        st.className = 'p-4 bg-emerald-50 border border-emerald-200 rounded-xl';
                        dot.className = 'inline-block w-2.5 h-2.5 rounded-full bg-emerald-500';
                        txt.className = 'text-sm font-semibold text-emerald-800';
                        txt.textContent = '✅ Terhubung';
                        det.innerHTML = 'Data siswa: ' + (data.data?.students || '?') + ' siswa terdeteksi';
                    } else {
                        throw new Error(data.message || 'Koneksi gagal');
                    }
                } catch (err) {
                    st.className = 'p-4 bg-red-50 border border-red-200 rounded-xl';
                    dot.className = 'inline-block w-2.5 h-2.5 rounded-full bg-red-500';
                    txt.className = 'text-sm font-semibold text-red-800';
                    txt.textContent = '❌ Tidak Terhubung';
                    det.textContent = err.message;
                } finally {
                    testBtn.disabled = false;
                    testBtn.innerHTML = '<i class="fa-solid fa-plug"></i> Test Koneksi';
                }
            });
        }

    });
    </script>
</div>
@endsection
