<?php

namespace App\Livewire\Cards;

use Livewire\Component;
use App\Http\Controllers\{GrupoEconomicoController, BandeiraController};
use App\Traits\ControllerInvoker;

class BandeirasCard extends Component
{
    use ControllerInvoker;

    public $grupoEconomico = [];
    public $grupo_economico_id;
    public $bandeira_nome;
    public $canSave = false;
    public $msg = null;

    public function mount()
    {
        $this->listGrupoEconomico();
    }


    public function bandeiraStore()
    {
        try {
            $response = $this->callController(BandeiraController::class, 'store', [
                'nome' => $this->bandeira_nome,
                'grupo_economico_id' => $this->grupo_economico_id
            ]);

            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'bandeira salva com sucesso';
            $this->reset(['bandeira_nome', 'grupo_economico_id']);
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }

    public function listGrupoEconomico()
    {
        try {
            $response = $this->callController(GrupoEconomicoController::class, 'index');
            $grupos = json_decode(json_encode($response->getData(true)), true) ?? [];
            $this->grupoEconomico = $grupos['data'];
            $this->canSave = count($grupos) > 0;
            $this->msg = $this->canSave
                ? null
                : '⚠ Nenhum dado disponível para listar os grupos econômicos.';
        } catch (\Throwable $e) {
            $this->grupoEconomico = [];
            $this->canSave = false;
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }
}
