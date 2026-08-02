<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien microservice deteksi wajah (FastAPI + InsightFace).
 *
 * Prinsip: microservice adalah ASISTEN petugas, bukan hakim.
 * Bila microservice mati / timeout -> method mengembalikan ['ok' => false, 'error' => 'faceid_down'],
 * dan pemanggil WAJIB punya fallback manual (form tetap berfungsi tanpa verifikasi wajah).
 */
class FaceRecognitionService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.faceid.base_url', ''), '/');
        $this->apiKey  = (string) config('services.faceid.api_key', '');
        $this->timeout = (int) config('services.faceid.timeout', 3);
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    /**
     * Daftar siswa yang sudah terdaftar di microservice (embedding count).
     *
     * @return array<string, int>  student_id => embedding_count
     */
    public function enrolledMap(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $resp = Http::timeout($this->timeout)
                ->withHeaders(['X-API-Key' => $this->apiKey])
                ->get($this->baseUrl.'/students');

            if (! $resp->successful()) {
                return [];
            }

            $map = [];
            foreach ($resp->json('students') ?? [] as $s) {
                $map[(string) $s['student_id']] = (int) $s['embedding_count'];
            }

            return $map;
        } catch (ConnectionException $e) {
            Log::warning('FaceID students gagal: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Verifikasi satu foto -> 3 kandidat teratas + skor + flag ambiguous.
     *
     * @return array{ok: bool, data?: array, error?: string}
     */
    public function verify(string $photoPath): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'error' => 'faceid_not_configured'];
        }

        try {
            $resp = Http::timeout($this->timeout)
                ->withHeaders(['X-API-Key' => $this->apiKey])
                ->attach('photo', fopen($photoPath, 'r'), basename($photoPath))
                ->post($this->baseUrl.'/verify');

            if ($resp->successful()) {
                return ['ok' => true, 'data' => $resp->json()];
            }

            Log::warning('FaceID verify HTTP '.$resp->status().': '.substr($resp->body(), 0, 300));

            return ['ok' => false, 'error' => 'faceid_http_'.$resp->status()];
        } catch (ConnectionException $e) {
            Log::warning('FaceID tidak terjangkau, fallback manual: '.$e->getMessage());

            return ['ok' => false, 'error' => 'faceid_down'];
        }
    }

    /**
     * Enroll embedding siswa dari 1-5 foto.
     *
     * @param  array<string>  $photoPaths
     * @return array{ok: bool, data?: array, error?: string}
     */
    public function enroll(string $studentId, string $name, array $photoPaths, ?string $twinGroup = null): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'error' => 'faceid_not_configured'];
        }

        try {
            $request = Http::timeout($this->timeout * 3)
                ->withHeaders(['X-API-Key' => $this->apiKey])
                ->asMultipart();

            foreach ($photoPaths as $path) {
                $request->attach('photos', fopen($path, 'r'), basename($path));
            }

            $resp = $request->post($this->baseUrl.'/enroll', [
                'student_id' => $studentId,
                'name' => $name,
                'twin_group' => $twinGroup ?? '',
            ]);

            if ($resp->successful()) {
                return ['ok' => true, 'data' => $resp->json()];
            }

            return ['ok' => false, 'error' => 'faceid_http_'.$resp->status(), 'detail' => $resp->json()];
        } catch (ConnectionException $e) {
            Log::warning('FaceID enroll gagal (timeout/down): '.$e->getMessage());

            return ['ok' => false, 'error' => 'faceid_down'];
        }
    }
}
