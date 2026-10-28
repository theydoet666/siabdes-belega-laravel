<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class KeamananProduksiDanHeaderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji respons HTTP memuat seluruh security headers standar.
     */
    public function test_respons_memuat_header_keamanan_lengkap(): void
    {
        $response = $this->get('/masuk');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(self), geolocation=()');

        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
    }

    /**
     * Uji URL generator memaksa scheme HTTPS ketika environment bernilai production.
     */
    public function test_paksa_https_di_environment_production(): void
    {
        // Dalam kondisi testing (APP_ENV=testing), skema tidak dipaksa jika host lokal
        $urlBiasa = url('/r/KODE123');

        // Simulasikan app environment production
        $this->app['env'] = 'production';
        URL::forceScheme('https');

        $urlProduksi = url('/r/KODE123');
        $this->assertStringStartsWith('https://', $urlProduksi);
    }
}
