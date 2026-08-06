<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FCM HTTP v1 API — kirim push notification via Firebase Cloud Messaging.
 *
 * Konfigurasi di .env:
 *   FCM_SERVICE_ACCOUNT=/path/ke/service-account.json  (Firebase console → Project settings → Service accounts)
 *   FCM_PROJECT_ID=xxx                                 (opsional, otomatis dibaca dari JSON bila kosong)
 *
 * Jika belum dikonfigurasi, semua method jadi no-op (log debug) — aplikasi tetap berjalan normal.
 */
class FcmService
{
    protected ?array $account = null;

    protected ?string $projectId = null;

    protected ?string $accessToken = null;

    public function __construct()
    {
        $path = env('FCM_SERVICE_ACCOUNT');
        if ($path && is_file($path)) {
            $raw = file_get_contents($path);
            $this->account = $raw ? json_decode($raw, true) : null;
            $this->projectId = env('FCM_PROJECT_ID')
                ?: ($this->account['project_id'] ?? null);
        }
    }

    public function isEnabled(): bool
    {
        return $this->account !== null
            && $this->projectId !== null
            && isset($this->account['client_email'])
            && isset($this->account['private_key']);
    }

    /**
     * Kirim push notification ke satu token perangkat.
     */
    public function sendToToken(string $token, array $notification, ?array $data = null): bool
    {
        if (! $this->isEnabled()) {
            Log::debug('FCM disabled — isi FCM_SERVICE_ACCOUNT & FCM_PROJECT_ID di .env');

            return false;
        }

        $title = $notification['title'] ?? 'S1WON DIGI';
        $body = $notification['body'] ?? '';

        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data ?: new \stdClass(),
                'webpush' => [
                    'headers' => ['TTL' => '3600'],
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'icon' => 'https://tatib.smkn1-wonorejo.sch.id/aplikasi-wali/icons/Icon-192.png',
                        'badge' => 'https://tatib.smkn1-wonorejo.sch.id/aplikasi-wali/icons/Icon-192.png',
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($this->getAccessToken())
                ->timeout(10)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $message);

            if (! $response->successful()) {
                Log::error('FCM send failed: '.$response->status().' '.$response->body());

                // Token mati/tercabut (mis. SW browser diganti) → hapus dari database
                // supaya tidak dipakai lagi; aplikasi akan daftarkan token baru saat login berikutnya.
                if ($response->status() === 404) {
                    $body = $response->json();
                    $code = $body['error']['details'][0]['errorCode'] ?? null;
                    if ($code === 'UNREGISTERED') {
                        \App\Models\ParentDevice::where('fcm_token', $token)->delete();
                        Log::error('FCM device unregistered — token dihapus dari database');
                    }
                }

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('FCM send error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Ambil OAuth2 access token dari service account (JWT RS256 → token endpoint).
     */
    protected function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $now = time();
        $payload = [
            'iss' => $this->account['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $jwt = $this->signJwt($payload);

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        $this->accessToken = $response->json('access_token');

        return $this->accessToken;
    }

    protected function signJwt(array $payload): string
    {
        $b64 = fn (array $data) => rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');

        $header = $b64(['alg' => 'RS256', 'typ' => 'JWT']);
        $body = $b64($payload);
        $signingInput = $header.'.'.$body;

        openssl_sign($signingInput, $signature, $this->account['private_key'], OPENSSL_ALGO_SHA256);

        return $signingInput.'.'.rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }
}
