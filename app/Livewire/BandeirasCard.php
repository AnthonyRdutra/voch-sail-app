<?php

namespace App\Livewire;

use Livewire\Component;
use App\Http\Controllers\{GrupoEconomicoController, BandeiraController};
use App\Traits\ControllerInvoker;

class BandeirasCard extends Component
{
    use ControllerInvoker;

    public $msg;
    public $grupoEconomico;
    public $grupo_economico_id;
    public $bandeira_nome;

    public function mount()
    {
        if (empty($this->grupoEconomico)) {
            $this->grupoEconomico = $this->listGrupoEconomico();
        }
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
            $this->reset(['bandeira', 'grupo_economico_id']);
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }

    public function listGrupoEconomico()
    {
        try {
            $response = $this->callController(GrupoEconomicoController::class, 'index');
            $data = $response->getData(true);
            $this->msg = $data['message'] ?? $data ?? [];
            return $this->grupoEconomico = $data['data'];
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e;
            return $this->grupoEconomico = [];
        }
    }
}
