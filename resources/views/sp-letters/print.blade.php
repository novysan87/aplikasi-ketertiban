<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak SP - {{ $spLetter->student->full_name }}</title>
    <style>
        @page { margin: 2cm; size: A4; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; }
        .kop-surat { text-align: center; margin-bottom: 30px; }
        .kop-surat h1 { font-size: 16pt; font-weight: bold; margin: 0; text-transform: uppercase; }
        .kop-surat p { font-size: 11pt; margin: 2px 0; }
        .kop-surat .line { border-top: 2px solid #000; margin-top: 8px; }
        .kop-surat .sub-line { border-top: 1px solid #000; margin-top: 2px; }
        .letter-number { text-align: center; margin: 25px 0; font-weight: bold; font-size: 13pt; }
        .to-title { margin-bottom: 15px; }
        .body-text { text-align: justify; margin-bottom: 20px; }
        .violation-list { margin: 15px 0 15px 20px; }
        .violation-list li { margin-bottom: 3px; }
        .signature { margin-top: 50px; }
        .signature .date { text-align: center; margin-bottom: 5px; }
        .signature .title { text-align: center; margin-bottom: 60px; }
        .signature .name { text-align: center; font-weight: bold; }
        .signature .nip { text-align: center; font-size: 11pt; }
    </style>
</head>
<body>
    <div class="kop-surat">
        <h1>{{ $school['name'] }}</h1>
        <p>{{ $school['address'] }}</p>
        <p>Telp: {{ $school['phone'] }}</p>
        <div class="line"></div>
        <div class="sub-line"></div>
    </div>

    <div class="letter-number">
        <p>SURAT PERINGATAN</p>
        <p>Nomor: {{ $spLetter->letter_number }}</p>
    </div>

    <div class="to-title">
        <p>Kepada Yth.</p>
        <p><strong>Saudara/i {{ $spLetter->student->full_name }}</strong></p>
        <p>Kelas: {{ $spLetter->student->class_name ?? '-' }}</p>
        <p>NISN: {{ $spLetter->student->nisn }}</p>
        <p>di tempat</p>
    </div>

    <div class="body-text">
        <p>Berdasarkan pencatatan pelanggaran yang telah dilakukan, dengan ini kami menyampaikan Surat Peringatan {{ $spLetter->spThreshold->name }} kepada Saudara/i dengan rincian sebagai berikut:</p>
    </div>

    <div class="body-text">
        <p>Total akumulasi poin pelanggaran: <strong>{{ $spLetter->total_points_at_time }} poin</strong></p>
        <p>Daftar pelanggaran:</p>
        <ul class="violation-list">
            @php $violations = is_array($spLetter->violations_included) ? $spLetter->violations_included : json_decode($spLetter->violations_included, true); @endphp
            @if($violations)
                @foreach($violations as $v)
                    <li>{{ $v['violation_date'] ?? '-' }} - {{ $v['description'] ?? '-' }} ({{ $v['points'] ?? 0 }} poin)</li>
                @endforeach
            @endif
        </ul>
    </div>

    <div class="body-text">
        <p>Dengan adanya Surat Peringatan ini, Saudara/i diharapkan dapat memperbaiki sikap dan perilaku serta tidak mengulangi pelanggaran yang telah dilakukan. Apabila Saudara/i kembali melakukan pelanggaran, maka pihak sekolah akan memberikan sanksi yang lebih tegas.</p>
    </div>

    <div class="signature">
        <div class="date">
            <p>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</p>
        </div>
        <div class="title">
            <p>Kepala {{ $school['name'] }},</p>
        </div>
        <div class="name">
            <p><u><strong>{{ $school['kepala_sekolah'] }}</strong></u></p>
        </div>
        <div class="nip">
            <p>NIP. {{ $school['kepala_sekolah_nip'] }}</p>
        </div>
    </div>

    <script>
        window.onload = function() { window.print(); };
    </script>
</body>
</html>
