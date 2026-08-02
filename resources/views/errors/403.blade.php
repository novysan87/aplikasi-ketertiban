@include('errors._error', [
    'code' => '403',
    'title' => 'Akses Ditolak',
    'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini. Jika menurut Anda ini keliru, silakan hubungi administrator.',
    'icon' => 'fa-lock',
    'iconFrom' => '#ef4444', 'iconTo' => '#dc2626', 'iconShadow' => 'rgba(239, 68, 68, 0.35)',
    'codeFrom' => '#f87171', 'codeTo' => '#dc2626',
    'ctaFrom' => '#ef4444', 'ctaTo' => '#dc2626', 'ctaShadow' => 'rgba(239, 68, 68, 0.35)',
    'showContact' => true,
])
