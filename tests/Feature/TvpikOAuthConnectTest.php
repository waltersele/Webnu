<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TvpikOAuthConnectTest extends TestCase
{
    use RefreshDatabase;

    protected string $redirectUri = 'http://127.0.0.1:8001/api/v1/integrations/webnu/callback';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.tvpik_oauth.allowed_redirect_uris' => [
                $this->redirectUri,
                'http://localhost:8001/api/v1/integrations/webnu/callback',
            ],
        ]);
    }

    public function test_connect_show_returns_login_form(): void
    {
        $response = $this->get('/integrations/tvpik/connect?' . http_build_query([
            'state' => 'test-state',
            'redirect_uri' => $this->redirectUri,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Conectar con TVPik', false);
        $response->assertSee('Autorizar y conectar', false);
    }

    public function test_connect_show_requires_params(): void
    {
        $this->get('/integrations/tvpik/connect')->assertStatus(400);
    }

    public function test_connect_rejects_disallowed_redirect_uri(): void
    {
        $this->get('/integrations/tvpik/connect?' . http_build_query([
            'state' => 'x',
            'redirect_uri' => 'https://evil.example/callback',
        ]))->assertStatus(400);
    }

    public function test_connect_login_redirects_with_code_and_state(): void
    {
        $user = User::factory()->create([
            'email' => 'oauth-tvpik@webnu.test',
            'password' => Hash::make('secret-pass'),
        ]);

        $response = $this->post('/integrations/tvpik/connect', [
            'email' => 'oauth-tvpik@webnu.test',
            'password' => 'secret-pass',
            'redirect_uri' => $this->redirectUri,
            'state' => 'oauth-state-123',
        ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString($this->redirectUri, $location);
        $this->assertStringContainsString('state=oauth-state-123', $location);
        $this->assertStringContainsString('code=', $location);

        $user->refresh();
        $this->assertNotEmpty($user->api_token);
        $this->assertStringContainsString('code=' . urlencode($user->api_token), $location);
    }

    public function test_authenticated_user_skips_form_on_get(): void
    {
        $user = User::factory()->create([
            'api_token' => 'existing-token-abc',
        ]);

        $response = $this->actingAs($user)->get('/integrations/tvpik/connect?' . http_build_query([
            'state' => 'sess-state',
            'redirect_uri' => $this->redirectUri,
        ]));

        $response->assertRedirect();
        $this->assertStringContainsString('code=existing-token-abc', $response->headers->get('Location'));
        $this->assertStringContainsString('state=sess-state', $response->headers->get('Location'));
    }
}
