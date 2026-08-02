@include('errors._error', [
    'code' => '500',
    'title' => 'Terjadi Kesalahan',
    'message' => 'Maaf, terjadi kendala pada server kami. Silakan muat ulang halaman atau coba lagi beberapa saat. Jika terus berlanjut, hubungi administrator.',
    'icon' => 'fa-triangle-exclamation',
    'iconFrom' => '#8b5cf6', 'iconTo' => '#7c3aed', 'iconShadow' => 'rgba(139, 92, 246, 0.35)',
    'codeFrom' => '#a78bfa', 'codeTo' => '#7c3aed',
    'ctaFrom' => '#8b5cf6', 'ctaTo' => '#7c3aed', 'ctaShadow' => 'rgba(139, 92, 246, 0.35)',
    'showContact' => true,
])
