<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\{GrupoEconomico, Bandeira, Unidade, Colaborador};

class ControllersCrudTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    protected $grupo;
    protected $bandeiras;
    protected $colaboradores;
    protected $unidades;

    public function setUp(): void
    {
        // Create test data
        parent::setUP();

        // gera dados de teste para grupo economico
        $this->grupo = GrupoEconomico::factory()->create([
            'nome' => 'rede sample'
        ]);

        // gera dados de teste para grupo economico
        $this->bandeiras = Bandeira::factory()->create([
            'nome' => "bandeira beta",
            'grupo_economico_id' => $this->grupo->id
        ]);

        // gera dados de teste para unidades
        $this->unidades = Unidade::factory()->create([
            'nome_fantasia' => 'unidade teste',
            'razao_social' => 'razao teste',
            'cnpj' => '00000000000000',
            'bandeira_id' => $this->bandeiras->id,
        ]);;

        // gera dados de teste para colaborador
        $this->colaboradores = Colaborador::factory()->create([
            'nome'       => fake()->unique()->name(),
            'email'      => fake()->unique()->safeEmail(),
            'cpf'        => fake()->unique()->numerify('###########'),
            'unidade_id' => $this->unidades->id,
        ]);
    }


    // teste CRUD grupo economico
    public function test_grupo_economico_crud(): void
    {
        // search
        $this->assertDatabaseHas('grupo_economico', [
            'nome' => $this->grupo->nome
        ]);

        // index
        $response = $this->getJson('/api/grupo-economico');
        $response->assertStatus(200)->assertJsonFragment([
            'nome' => $this->grupo->nome
        ]);

        // show
        $response = $this->getJson("/api/grupo-economico/{$this->grupo->id}");
        $response->assertStatus(200)->assertJsonFragment([
            'nome' => $this->grupo->nome
        ]);

        // update
        $this->putJson("/api/grupo-economico/{$this->grupo->id}", [
            'nome' => 'rede modificada'
        ])
            ->assertStatus(200);
        $this->assertDatabaseHas('grupo_economico', [
            'nome' => 'rede modificada'
        ]);

        // delete
        $this->deleteJson("/api/grupo-economico/{$this->grupo->id}")
            ->assertStatus(200);
        $this->assertDatabaseMissing('grupo_economico', [
            'id' => $this->grupo->id
        ]);
    }

    // teste CRUD bandeira
    public function test_bandeira_crud(): void
    {
        // search
        $this->assertDatabaseHas('bandeiras', [
            'id' => $this->bandeiras->id,
            'grupo_economico_id' => $this->grupo->id
        ]);

        // index
        $response = $this->getJson('/api/bandeiras');
        $response->assertStatus(200)->assertJsonFragment([
            'nome' => $this->bandeiras->nome,
            'grupo_economico_id' => $this->grupo->id
        ]);

        // show
        $response = $this->getJson("/api/bandeiras/{$this->bandeiras->id}");
        $response->assertStatus(200)->assertJsonFragment([
            'id' => $this->bandeiras->id
        ]);

        // update
        $this->putJson("/api/bandeiras/{$this->bandeiras->id}", [
            'nome' => 'bandeira alterada',
            'grupo_economico_id' => $this->grupo->id
        ])->assertStatus(200);

        $this->assertDatabaseHas('bandeiras', [
            'nome' => 'bandeira alterada',
            'grupo_economico_id' => $this->grupo->id
        ]);

        // delete
        $this->deleteJson("/api/bandeiras/{$this->bandeiras->id}")
            ->assertStatus(200);
        $this->assertDatabaseMissing('bandeiras', [
            'id' => $this->bandeiras->id
        ]);
    }

    // teste CRUD unidade
    public function test_unidade_crud(): void
    {
        // search
        $this->assertDatabaseHas('unidades', [
            'id' => $this->unidades->id,
            'bandeira_id' => $this->bandeiras->id
        ]);

        // index
        $response = $this->getJson('/api/unidades');
        $response->assertStatus(200)->assertJsonFragment([
            'nome_fantasia' => $this->unidades->nome_fantasia,
            'bandeira_id' => $this->bandeiras->id
        ]);

        // show
        $response = $this->getJson("/api/unidades/{$this->unidades->id}");
        $response->assertStatus(200)->assertJsonFragment([
            'id' => $this->bandeiras->id
        ]);

        // update
        $this->putJson("/api/unidades/{$this->unidades->id}", [
            'nome_fantasia' => 'unidade alterada',
            'razao_social' => 'razao alterada',
            'cnpj' => $this->unidades->cnpj,
            'bandeira_id' => $this->unidades->id,
        ])->assertStatus(200);

        $this->assertDatabaseHas('unidades', [
            'nome_fantasia' => 'unidade alterada',
            'bandeira_id' => $this->bandeiras->id
        ]);

        // delete
        $this->deleteJson("/api/unidades/{$this->unidades->id}")
            ->assertStatus(200);
        $this->assertDatabaseMissing('unidades', [
            'id' => $this->colaboradores->id
        ]);
    }

    // test CRUD colaborador
    public function test_colaborador_crud(): void
    {
        // search
        $this->assertDatabaseHas('colaboradores', [
            'id' => $this->unidades->id,
            'unidade_id' => $this->unidades->id
        ]);

        // index
        $response = $this->getJson('/api/colaboradores');
        $response->assertStatus(200)->assertJsonFragment([
            'nome' => $this->colaboradores->nome,
            'unidade_id' => $this->unidades->id
        ]);

        // show
        $response = $this->getJson("/api/colaboradores/{$this->colaboradores->id}");
        $response->assertStatus(200)->assertJsonFragment([
            'id' => $this->unidades->id
        ]);

        // update
        $newName = fake()->unique()->name(); 
        $this->putJson("/api/colaboradores/{$this->colaboradores->id}", [
            'nome' => $newName,
            'email' => fake()->unique()->safeEmail(),
            'cpf' => $this->colaboradores->cpf,
            'unidade_id' => $this->unidades->id,
        ])->assertStatus(200);

        $this->assertDatabaseHas('colaboradores', [
            'nome' => $newName,
            'unidade_id' => $this->unidades->id
        ]);

        // delete
        $this->deleteJson("/api/colaboradores/{$this->colaboradores->id}")
            ->assertStatus(200);
        $this->assertDatabaseMissing('colaboradores', [
            'id' => $this->colaboradores->id
        ]);
    }
}
