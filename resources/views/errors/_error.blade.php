{{-- Template halaman error premium v2 (standalone, CSS murni — tanpa CDN) --}}
@php
    $appName = \App\Models\Setting::getValue('app_name', 'Aplikasi Ketertiban');
    $schoolName = \App\Models\Setting::getValue('school_name', 'SMKN 1 Wonorejo');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $code }} - {{ $title }} | {{ $appName }}</title>
    <link rel="stylesheet" href="/vendor/font-awesome/css/all.min.css">
    <link rel="icon" href="/favicon.ico">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 20px; position: relative; overflow: hidden;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background:
                radial-gradient(1200px 600px at 80% -10%, {{ $ctaFrom }}22, transparent 60%),
                radial-gradient(900px 500px at -10% 110%, {{ $ctaTo }}1f, transparent 60%),
                linear-gradient(135deg, #f8fafc 0%, #eef4ff 50%, #f4f8ff 100%);
        }
        {{-- Pola grid halus --}}
        .bg-grid {
            position: absolute; inset: 0; pointer-events: none; opacity: .5;
            background-image:
                linear-gradient(rgba(100,116,139,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(100,116,139,.06) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(ellipse at center, #000 30%, transparent 75%);
            -webkit-mask-image: radial-gradient(ellipse at center, #000 30%, transparent 75%);
        }
        {{-- Orb mengambang --}}
        .orb { position: absolute; border-radius: 9999px; filter: blur(70px); pointer-events: none; animation: drift 14s ease-in-out infinite; }
        .orb-1 { width: 420px; height: 420px; top: -120px; right: -100px; background: {{ $ctaFrom }}38; }
        .orb-2 { width: 480px; height: 480px; bottom: -160px; left: -120px; background: {{ $ctaTo }}33; animation-delay: -5s; }
        .orb-3 { width: 220px; height: 220px; top: 40%; left: 55%; background: {{ $codeFrom }}26; animation-delay: -9s; }
        @keyframes drift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.08); }
        }
        {{-- Titik dekoratif --}}
        .dots {
            position: absolute; inset: 0; pointer-events: none; opacity: .35;
            background-image: radial-gradient(rgba(148,163,184,.35) 1.2px, transparent 1.2px);
            background-size: 26px 26px;
            mask-image: radial-gradient(ellipse at 50% 40%, #000 10%, transparent 70%);
            -webkit-mask-image: radial-gradient(ellipse at 50% 40%, #000 10%, transparent 70%);
        }

        {{-- Kartu glass + border gradien berputar --}}
        .card {
            position: relative; z-index: 10; width: 100%; max-width: 500px;
            background: rgba(255,255,255,.72); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
            border-radius: 30px; padding: 44px 36px; text-align: center;
            box-shadow: 0 30px 70px -20px rgba(15,23,42,.25), 0 0 0 1px rgba(255,255,255,.6) inset;
            animation: fadeUp .55s cubic-bezier(.22,.9,.3,1.2) both;
        }
        .card::before {
            content: ""; position: absolute; inset: -2px; border-radius: 32px; z-index: -1;
            background: conic-gradient(from var(--angle, 0deg),
                transparent 0%, {{ $ctaTo }} 12%, transparent 26%,
                transparent 50%, {{ $ctaFrom }} 62%, transparent 78%);
            animation: spin 7s linear infinite;
        }
        @property --angle { syntax: '<angle>'; initial-value: 0deg; inherits: false; }
        @keyframes spin { to { --angle: 360deg; } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(22px) scale(.98); } to { opacity: 1; transform: none; } }

        .icon-box {
            width: 92px; height: 92px; margin: 0 auto 26px; position: relative;
            border-radius: 26px; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, {{ $iconFrom }}, {{ $iconTo }});
            box-shadow: 0 16px 34px {{ $iconShadow }}, 0 0 0 6px rgba(255,255,255,.7);
            animation: floaty 3.2s ease-in-out infinite;
        }
        .icon-box::after {
            content: ""; position: absolute; inset: -14px; border-radius: 34px;
            background: linear-gradient(135deg, {{ $iconFrom }}33, transparent 60%);
            filter: blur(8px); z-index: -1;
        }
        .icon-box i { font-size: 34px; color: #fff; filter: drop-shadow(0 3px 6px rgba(0,0,0,.18)); }
        @keyframes floaty { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

        {{-- Angka besar berlapis --}}
        .code-wrap { position: relative; margin-bottom: 14px; }
        .code {
            position: relative; z-index: 2; font-size: 108px; font-weight: 900; letter-spacing: -4px; line-height: 1;
            background: linear-gradient(135deg, {{ $codeFrom }}, {{ $codeTo }});
            -webkit-background-clip: text; background-clip: text; color: transparent;
            filter: drop-shadow(0 10px 24px {{ $iconShadow }});
        }
        .code-ghost {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
            font-size: 190px; font-weight: 900; letter-spacing: -6px; line-height: 1;
            color: transparent; -webkit-text-stroke: 1.5px {{ $codeFrom }}1f;
            pointer-events: none; user-select: none;
        }

        h1 { font-size: 21px; font-weight: 800; color: #0f172a; margin-bottom: 10px; }
        .msg { font-size: 14px; color: #64748b; line-height: 1.7; margin-bottom: 34px; }

        .actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 13px 22px; font-size: 14px; font-weight: 700; border-radius: 16px;
            text-decoration: none; border: 2px solid transparent; cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease;
            animation: fadeUp .55s cubic-bezier(.22,.9,.3,1.2) both;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn:active { transform: translateY(0) scale(.98); }
        .btn-ghost { background: rgba(255,255,255,.9); color: #334155; border-color: #e2e8f0; animation-delay: .08s; }
        .btn-ghost:hover { background: #fff; border-color: #cbd5e1; }
        .btn-primary {
            background: linear-gradient(135deg, {{ $ctaFrom }}, {{ $ctaTo }}); color: #fff;
            box-shadow: 0 12px 28px {{ $ctaShadow }};
        }
        .btn-primary:hover { filter: brightness(1.06); box-shadow: 0 16px 34px {{ $ctaShadow }}; }
        .btn-contact { background: #eff6ff; color: #2563eb; border-color: #dbeafe; animation-delay: .16s; }
        .btn-contact:hover { background: #dbeafe; }

        .footer { text-align: center; margin-top: 26px; font-size: 12px; color: #94a3b8; animation: fadeUp .55s both .22s; }
        .footer i { margin-right: 5px; color: #cbd5e1; }

        @media (max-width: 480px) {
            .card { padding: 34px 22px; }
            .code { font-size: 84px; letter-spacing: -3px; }
            .code-ghost { font-size: 140px; }
            .actions { flex-direction: column; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="dots"></div>

    <div class="card">
        <div class="icon-box"><i class="fa-solid {{ $icon }}"></i></div>
        <div class="code-wrap">
            <div class="code-ghost">{{ $code }}</div>
            <div class="code">{{ $code }}</div>
        </div>
        <h1>{{ $title }}</h1>
        <p class="msg">{{ $message }}</p>

        <div class="actions">
            @if($showBack ?? true)
                <button onclick="history.length > 1 ? history.back() : window.location.href='{{ url('/') }}'" class="btn btn-ghost">
                    <i class="fa-solid fa-arrow-left" style="font-size:12px"></i> Kembali
                </button>
            @endif
            <a href="{{ url('/') }}" class="btn btn-primary">
                <i class="fa-solid fa-house" style="font-size:12px"></i> Ke Beranda
            </a>
            @if($showContact ?? false)
                <a href="mailto:admin@smkn1-wonorejo.sch.id" class="btn btn-contact">
                    <i class="fa-solid fa-headset" style="font-size:12px"></i> Hubungi Admin
                </a>
            @endif
        </div>
    </div>

    <p class="footer"><i class="fa-solid fa-shield-halved"></i>{{ $appName }} • {{ $schoolName }}</p>
</body>
</html>
