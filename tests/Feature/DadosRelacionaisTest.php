<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{GrupoEconomico, Bandeira, Unidade, Colaborador};

class DadosRelacionaisTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     */

    use RefreshDatabase;

    protected $grupo;
    protected $bandeiras;
    protected $colaboradores;
    protected $unidades;

    public function setUp(): void
    {

        parent::setUp();
        $this->grupo = GrupoEconomico::factory()->create();
        $this->bandeiras = Bandeira::factory(3)->create(['grupo_economico_id' => $this->grupo->id]);
        
        $bandeira = $this->bandeiras->first(); 
        $this->unidades = Unidade::factory()->create(['bandeira_id' => $bandeira->id]);
        
        $this->colaboradores = Colaborador::factory(5)->create(['unidade_id' => $this->unidades->id]);
    }

    public function test_group_has_bandeiras(): void
    {
        $this->assertCount(3, $this->grupo->bandeiras);
    }

    
    public function test_dandeiras_belongs_to_group(): void
    {   
        $bandeira = $this->bandeiras->first(); 

        $this->assertEquals($this->grupo->id, $bandeira->grupoEconomico->id);
    }

    public function test_unidade_has_colaboradores(): void
    {
        $this->assertCount(5, $this->unidades->colaboradores);
    }

    public function test_colaborador_belongs_to_unidade(): void
    {
        $colaborador = $this->colaboradores->first(); 
        $this->assertEquals($this->unidades->id, $colaborador->unidade->id);
    }
}
