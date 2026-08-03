<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Pelanggaran — {{ $school['name'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1a1a1a; margin: 0; padding: 0; }
        .kop { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .kop td { border: none; padding: 0; }
        .kop-logo { width: 118px; text-align: center; vertical-align: middle; }
        .kop-logo img { height: 88px; width: auto; max-width: 106px; object-fit: contain; }
        .kop-text { text-align: center; vertical-align: middle; }
        .kop-1, .kop-2 { font-size: 12px; font-weight: bold; color: #000; }
        .kop-3 { font-size: 14.5px; font-weight: bold; color: #000; margin: 2px 0; }
        .kop-4, .kop-5, .kop-6 { font-size: 8.5px; color: #000; }
        .kop-line { border-bottom: 2.5px solid #000; margin: 8px 0 14px; }
        .judul { text-align: center; margin: 12px 0 4px; }
        .judul h2 { font-size: 13px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .judul p { font-size: 9px; margin: 3px 0 0; color: #444; }
        .meta { width: 100%; border-collapse: collapse; margin: 10px 0 6px; }
        .meta td { padding: 2px 0; }
        .summary { width: 100%; border-collapse: collapse; margin: 8px 0 14px; }
        .summary td { border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; background: #f8fafc; }
        .summary .angka { font-size: 16px; font-weight: bold; color: #1e3a8a; display: block; }
        .summary .label { font-size: 8px; color: #555; text-transform: uppercase; }
        table.detail { width: 100%; border-collapse: collapse; margin: 4px 0 14px; }
        table.detail th { background: #1e3a8a; color: #fff; padding: 5px 6px; text-align: left; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.3px; }
        table.detail td { border: 1px solid #cbd5e1; padding: 4px 6px; }
        table.detail tr:nth-child(even) td { background: #f8fafc; }
        h3 { font-size: 10px; margin: 12px 0 4px; color: #1e3a8a; text-transform: uppercase; }
        .center { text-align: center; }
        .right { text-align: right; }
        .ttd { width: 100%; margin-top: 30px; }
        .ttd td { width: 50%; text-align: center; font-size: 9px; vertical-align: top; }
        .ttd .nama { margin-top: 70px; font-weight: bold; text-decoration: underline; }
        .footer { margin-top: 16px; text-align: center; font-size: 7.5px; color: #888; border-top: 1px solid #e2e8f0; padding-top: 6px; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 7.5px; font-weight: bold; }
        .badge-selesai { background: #dcfce7; color: #166534; }
        .badge-proses { background: #fef3c7; color: #92400e; }
        .badge-baru { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>

    {{-- Kop Surat (format resmi: logo kiri + teks tengah + garis tebal) --}}
    <table class="kop">
        <tr>
            <td class="kop-logo">
                @if (! empty($school['kop_logo']) && file_exists(public_path('storage/'.$school['kop_logo'])))
                    <img src="{{ public_path('storage/'.$school['kop_logo']) }}" alt="logo">
                @elseif (! empty($school['logo']) && file_exists(public_path('storage/'.$school['logo'])))
                    <img src="{{ public_path('storage/'.$school['logo']) }}" alt="logo">
                @endif
            </td>
            <td class="kop-text">
                <div class="kop-1">{{ $school['government'] }}</div>
                <div class="kop-2">{{ $school['agency'] }}</div>
                <div class="kop-3">{{ $school['full_name'] }}</div>
                <div class="kop-4">{{ $school['address_detail'] }}</div>
                <div class="kop-5">{{ $school['website_email'] }}</div>
                <div class="kop-6">{{ $school['postal'] }}</div>
            </td>
        </tr>
    </table>
    <div class="kop-line"></div>

    {{-- Judul --}}
    <div class="judul">
        <h2>Rekap Laporan Pelanggaran Siswa</h2>
        <p>Periode: {{ $periode }} &nbsp;•&nbsp; Kelas: {{ $filterKelas }}</p>
    </div>

    {{-- Ringkasan --}}
    <table class="summary">
        <tr>
            <td><span class="angka">{{ $summary['total_kasus'] }}</span><span class="label">Total Kasus</span></td>
            <td><span class="angka">{{ $summary['total_poin'] }}</span><span class="label">Total Poin</span></td>
            <td><span class="angka">{{ $summary['siswa_terlibat'] }}</span><span class="label">Siswa Terlibat</span></td>
        </tr>
    </table>

    {{-- Rekap per Jenis --}}
    <h3>A. Rekap per Jenis Pelanggaran</h3>
    <table class="detail">
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Jenis Pelanggaran</th>
                <th>Kategori</th>
                <th class="center" style="width:60px">Jumlah</th>
                <th class="center" style="width:70px">Total Poin</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perJenis as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['jenis'] }}</td>
                    <td>{{ $item['kategori'] }}</td>
                    <td class="center">{{ $item['jumlah'] }}</td>
                    <td class="center">{{ $item['poin'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Rekap per Kelas --}}
    <h3>B. Rekap per Kelas</h3>
    <table class="detail">
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Kelas</th>
                <th class="center" style="width:80px">Jumlah</th>
                <th class="center" style="width:90px">Total Poin</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perKelas as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['kelas'] }}</td>
                    <td class="center">{{ $item['jumlah'] }}</td>
                    <td class="center">{{ $item['poin'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Rincian --}}
    <h3>C. Rincian Pelanggaran</h3>
    <table class="detail">
        <thead>
            <tr>
                <th style="width:24px">No</th>
                <th style="width:64px">Tanggal</th>
                <th style="width:70px">NISN</th>
                <th>Nama Siswa</th>
                <th style="width:56px">Kelas</th>
                <th>Jenis Pelanggaran</th>
                <th class="center" style="width:34px">Poin</th>
                <th style="width:66px">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($violations as $i => $v)
                @php
                    $status = $v->handlings->where('status', 'selesai')->count() > 0 ? 'Selesai'
                        : ($v->handlings->count() > 0 ? 'Proses' : 'Baru');
                    $badge = $status === 'Selesai' ? 'badge-selesai' : ($status === 'Proses' ? 'badge-proses' : 'badge-baru');
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $v->violation_date?->format('d/m/Y') }}</td>
                    <td>{{ $v->student?->nisn }}</td>
                    <td>{{ $v->student?->full_name }}</td>
                    <td>{{ $v->student?->class?->name }}</td>
                    <td>
                    <div>{{ $v->violationType?->name }} <span style="color:#666">({{ $v->violationType?->category?->name ?? '-' }})</span></div>
                    @if ($v->description)
                        <div style="font-size:7.5px; color:#555; margin-top:1px;">{{ $v->description }}</div>
                    @endif
                </td>
                    <td class="center">{{ $v->points }}</td>
                    <td class="center"><span class="badge {{ $badge }}">{{ $status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="8" class="center">Tidak ada data pelanggaran pada periode ini</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- TTD --}}
    <table class="ttd">
        <tr>
            <td>
                <div>Petugas / Guru BK</div>
                <div class="nama">&nbsp;</div>
                <div>______________________</div>
            </td>
            <td>
                <div>Mengetahui,<br>Kepala {{ $school['name'] }}</div>
                <div class="nama">{{ $school['kepala_sekolah'] }}</div>
                <div>NIP. {{ $school['kepala_sekolah_nip'] }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY, HH:mm') }} WIB • E-TATIB {{ $school['name'] }}
    </div>

</body>
</html>
