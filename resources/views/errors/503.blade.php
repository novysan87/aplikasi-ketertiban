@include('errors._error', [
    'code' => '503',
    'title' => 'Sedang Pemeliharaan',
    'message' => 'Sistem sedang dalam pemeliharaan. Silakan kembali beberapa saat lagi. Terima kasih atas kesabaran Anda.',
    'icon' => 'fa-screwdriver-wrench',
    'iconFrom' => '#0ea5e9', 'iconTo' => '#0284c7', 'iconShadow' => 'rgba(14, 165, 233, 0.35)',
    'codeFrom' => '#38bdf8', 'codeTo' => '#0284c7',
    'ctaFrom' => '#0ea5e9', 'ctaTo' => '#0284c7', 'ctaShadow' => 'rgba(14, 165, 233, 0.35)',
    'showBack' => false,
])
