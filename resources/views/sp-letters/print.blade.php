<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak SP - {{ $spLetter->student->full_name }}</title>
    <style>
        @page { margin: 2cm 1cm; size: 210mm 330mm; } /* F4 — kiri/kanan 1cm */
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; }
        .kop-surat { margin-bottom: 24px; }
        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .kop-table td { border: none; padding: 0; }
        .kop-logo { width: 112px; text-align: center; vertical-align: middle; }
        .kop-logo img { height: 86px; width: auto; max-width: 104px; object-fit: contain; }
        .kop-text { text-align: center; vertical-align: middle; }
        .kop-1, .kop-2 { font-size: 13.5pt; font-weight: bold; margin: 0; text-transform: uppercase; }
        .kop-3 { font-size: 11.5pt; font-weight: bold; margin: 1px 0; text-transform: uppercase; white-space: nowrap; }
        .kop-4, .kop-5, .kop-6 { font-size: 10.5pt; margin: 0; }
        .kop-surat .line { border-top: 2.5px solid #000; margin-top: 6px; }
        .kop-surat .sub-line { border-top: 1px solid #000; margin-top: 2px; }
        .letter-number { text-align: center; margin: 25px 0; font-weight: bold; font-size: 13pt; }
        .to-title { margin-bottom: 15px; }
        .body-text { text-align: justify; margin-bottom: 20px; }
        .violation-list { margin: 15px 0 15px 20px; }
        .violation-list li { margin-bottom: 3px; }
        .violation-table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .violation-table th, .violation-table td { border: 1px solid #000; padding: 6px 10px; text-align: left; font-size: 11pt; }
        .violation-table th { font-weight: bold; }
        .violation-table .total-row td { font-weight: bold; }
        .signature { margin-top: 50px; padding-left: 24em; }
        .signature .date { text-align: left; margin-bottom: 5px; }
        .signature .title { text-align: left; margin-bottom: 60px; }
        .signature .name { text-align: left; font-weight: bold; }
        .signature .nip { text-align: left; font-size: 11pt; }
    </style>
</head>
<body>
    {{-- Nama sekolah: satu baris (nowrap) sesuai format kop resmi --}}
    <div class="kop-surat">
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    @if (! empty($school['kop_logo']) && file_exists(public_path('storage/'.$school['kop_logo'])))
                        <img src="{{ asset('storage/'.$school['kop_logo']) }}" alt="logo">
                    @elseif (! empty($school['logo']) && file_exists(public_path('storage/'.$school['logo'])))
                        <img src="{{ asset('storage/'.$school['logo']) }}" alt="logo">
                    @endif
                </td>
                <td class="kop-text">
                    <p class="kop-1">{{ $school['government'] }}</p>
                    <p class="kop-2">{{ $school['agency'] }}</p>
                    <p class="kop-3">{{ $school['full_name'] }}</p>
                    <p class="kop-4">{{ $school['address_detail'] }}</p>
                    <p class="kop-5">{{ $school['website_email'] }}</p>
                    <p class="kop-6">{{ $school['postal'] }}</p>
                </td>
            </tr>
        </table>
        <div class="line"></div>
        <div class="sub-line"></div>
    </div>

    <div class="letter-number">
        <p style="margin-bottom: 0;"><u>SURAT PERINGATAN</u></p>
        <p style="margin-top: 0;">Nomor: {{ $spLetter->letter_number }}</p>
    </div>

    <div class="to-title">
        <p style="margin-bottom: 0;">Kepada Yth. Bapak/Ibu/Wali Murid</p>
        <p style="margin-top: 0; margin-bottom: 0;"><strong>{{ strtoupper($spLetter->student->full_name) }}</strong></p>
        <p style="margin-top: 0; margin-bottom: 0;">Kelas: {{ $spLetter->student->class_name ?? '-' }}</p>
        <p style="margin-top: 0; margin-bottom: 0;">NISN: {{ $spLetter->student->nisn }}</p>
        <p style="margin-top: 0;">di Tempat</p>
    </div>

    <div class="body-text">
        <p style="text-indent: 2em;">Berdasarkan pencatatan pelanggaran yang telah dilakukan, dengan ini kami menyampaikan Surat Peringatan {{ $spLetter->spThreshold->name }} kepada ananda <strong>{{ strtoupper($spLetter->student->full_name) }}</strong> dengan rincian sebagai berikut:</p>
    </div>

    <div class="body-text">
        <p>Total akumulasi poin pelanggaran: <strong>{{ $spLetter->total_points_at_time }} poin</strong></p>
        <p>Berikut daftar pelanggaran :</p>
        @php $violations = is_array($spLetter->violations_included) ? $spLetter->violations_included : json_decode($spLetter->violations_included, true); @endphp
        @if($violations)
            <table class="violation-table">
                <thead>
                    <tr>
                        <th style="width: 22%;">Tanggal Pelanggaran</th>
                        <th>Jenis Pelanggaran</th>
                        <th style="width: 12%; text-align: center;">Point</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($violations as $v)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($v['violation_date'] ?? now())->setTimezone('Asia/Jakarta')->locale('id')->isoFormat('dddd, DD MMMM Y') }}</td>
                            <td>
                                {{ \App\Models\Violation::with('violationType')->find($v['id'] ?? null)?->violationType?->name ?? '-' }}
                                @if (! empty($v['description']))
                                    <div style="font-style: italic; font-size: 9pt; font-weight: normal; margin-top: 2px;">{{ $v['description'] }}</div>
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $v['points'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right;">Total</td>
                        <td style="text-align: center;">{{ array_sum(array_column($violations, 'points')) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>

    <div class="body-text">
        <p style="text-indent: 2em;">Dengan adanya Surat Peringatan ini, <strong>{{ strtoupper($spLetter->student->full_name) }}</strong> diharapkan dapat memperbaiki sikap dan perilaku serta tidak mengulangi pelanggaran yang telah dilakukan. Apabila <strong>{{ strtoupper($spLetter->student->full_name) }}</strong> kembali melakukan pelanggaran, maka pihak sekolah akan memberikan sanksi yang lebih tegas.</p>
    </div>

    <div class="signature">
        <div class="date">
            <p style="margin-bottom: 0;">{{ $school['place'] }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</p>
        </div>
        <div class="title">
            <p style="margin-top: 0; margin-bottom: 0;">Kepala {{ $school['name'] }},</p>
        </div>
        <div class="name">
            <p style="margin-top: 0; margin-bottom: 0;"><u><strong>{{ $school['kepala_sekolah'] }}</strong></u></p>
        </div>
        <div class="nip">
            <p style="margin-top: 0;">NIP. {{ $school['kepala_sekolah_nip'] }}</p>
        </div>
    </div>

    <script>
        window.onload = function() { window.print(); };
    </script>
</body>
</html>
