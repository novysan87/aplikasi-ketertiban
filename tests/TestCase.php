<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Pengaman: pastikan test HANYA boleh jalan di database test.
     *
     * Jika konfigurasi ter-cache (config:cache) membuat test menunjuk ke
     * database produksi, RefreshDatabase akan menghancurkan data asli —
     * guard ini menghentikan suite sebelum itu terjadi.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $dbName = config('database.connections.'.config('database.default').'.database');

        if (! str_ends_with((string) $dbName, '_test')) {
            throw new \RuntimeException(
                "TEST DIBLOKIR: database aktif '{$dbName}' bukan database test (harus berakhiran _test). ".
                'Jalankan `php artisan config:clear` lalu ulangi test.'
            );
        }
    }
}
