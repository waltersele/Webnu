<?php

namespace Tests\Feature;

use App\MenuPreRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PreAltaIngestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['pre_alta.ingest_key' => 'test-ingest-key']);
    }

    public function test_ingest_requires_api_key(): void
    {
        $response = $this->postJson('/api/pre-alta/ingest', [
            'restaurant_name' => 'Test',
            'sections' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_ingest_creates_staging_record(): void
    {
        $tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Http::fake([
            'https://example.com/*' => Http::response($tinyPng, 200, [
                'Content-Type' => 'image/png',
            ]),
        ]);

        $payload = [
            'restaurant_name' => 'Bar Test',
            'sections' => [
                [
                    'name' => 'Carta',
                    'products' => [
                        [
                            'name' => 'Plato 1',
                            'price_unit' => '10,00',
                            'image_url' => 'https://example.com/plato.jpg',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/pre-alta/ingest', $payload, [
            'X-Pre-Alta-Key' => 'test-ingest-key',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'public_url', 'claim_url', 'expires_at']);

        $this->assertEquals(1, MenuPreRegistration::count());
        $registration = MenuPreRegistration::first();
        $this->assertEquals(MenuPreRegistration::STATUS_PENDING, $registration->status);
        $this->assertStringStartsWith('pa-', $registration->public_slug);
    }

    public function test_v1_demos_create_is_alias_of_ingest(): void
    {
        $payload = [
            'restaurant_name' => 'Bar V1',
            'sections' => [
                [
                    'name' => 'Carta',
                    'products' => [
                        ['name' => 'Tapa', 'price_unit' => '3,50'],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/demos/create', $payload, [
            'X-Webnu-Demo-Key' => 'test-ingest-key',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'public_url', 'claim_url', 'expires_at']);
        $this->assertEquals(1, MenuPreRegistration::count());
    }
}
