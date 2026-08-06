<?php

namespace App\Console\Commands;

use App\Models\ParentDevice;
use App\Services\FcmService;
use Illuminate\Console\Command;

class TestFcmPush extends Command
{
    protected $signature = 'fcm:test {--token= : Token FCM perangkat (opsional, default: token web pertama)}';

    protected $description = 'Kirim push notification uji coba via FCM';

    public function handle(FcmService $fcm): int
    {
        if (! $fcm->isEnabled()) {
            $this->error('FCM belum dikonfigurasi. Isi FCM_SERVICE_ACCOUNT & FCM_PROJECT_ID di .env');

            return self::FAILURE;
        }

        $token = $this->option('token');
        if (! $token) {
            $token = ParentDevice::where('platform', 'web')->value('fcm_token');
        }
        if (! $token) {
            $this->error('Tidak ada token perangkat. Daftarkan dulu lewat API /parent/devices atau pakai --token=');

            return self::FAILURE;
        }

        $ok = $fcm->sendToToken($token, [
            'title' => '🔔 Uji Coba SiMURID',
            'body' => 'Push notification berhasil! Ini notifikasi dari server.',
        ], ['type' => 'test']);

        $this->info($ok ? 'Notifikasi terkirim ✅' : 'Gagal mengirim ❌ (cek log)');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
