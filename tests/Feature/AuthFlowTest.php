<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // evita reuso de sessão entre testes
        $this->withoutExceptionHandling();
    }


    public function test_register_login_logout()
    {
        // POST de registro
        $response = $this->postJson('/api/register', [
            'name' => 'Usuário Teste',
            'email' => 'teste@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['user', 'token', 'message']);

        $this->assertDatabaseHas('users', ['email' => 'teste@example.com']);

        //POST de login
        $login = $this->postJson('/api/login', [
            'email' => 'teste@example.com',
            'password' => '123456'
        ]);

        $login->assertStatus(200)->assertJsonStructure(['user', 'token', 'message']);

        $token = $login['token'];
        $this->assertNotEmpty($token, 'token deve ser retornado no login');

        // teste rota protegida
        $protected =  $this->withHeader('Authorization', "Bearer $token")->getJson('/api/grupo-economico');
        $protected->assertStatus(200);

        // POST logout
        $logout = $this->withHeader('Authorizarion', "Bearer $token")->postJson('/api/logout');
        $logout->assertStatus(200)->assertJson(['message' => 'logout realizado com sucesso']);
    }

    public function test_breach_protected_token_route()
    {
        try {
            $this->getJson('/api/grupo-economico');
            $this->fail('Esperava AuthenticationException, mas não foi lançada.');
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            $this->assertEquals('Unauthenticated.', $e->getMessage());
        }
    }
}
