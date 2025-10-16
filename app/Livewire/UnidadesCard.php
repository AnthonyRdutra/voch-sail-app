<?php

namespace App\Livewire;

use Livewire\Component;
use App\Http\Controllers\{UnidadeController, BandeiraController};
use App\Traits\ControllerInvoker;

class UnidadesCard extends Component
{
    use ControllerInvoker;

    public $msg;
    public $nome_fantasia;
    public $razao_social;
    public $cnpj;
    public $bandeiras;
    public $bandeira_id; 

    public function mount()
    {
        $this->listBandeira();
    }


    public function unidadeStore()
    {
        try {
            $response = $this->callController(UnidadeController::class, 'store', [
                'nome_fantasia' => $this->nome_fantasia,
                'razao_social' => $this->razao_social,
                'cnpj' => $this->cnpj,
                'bandeira_id' => $this->bandeira_id
            ]);

            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'bandeira salva com sucesso';
            $this->reset(['nome_fantasia', 'razao_social', 'cnpj', 'bandeira_id']);
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }


    public function listBandeira()
    {
        try {
            $response = $this->callController(BandeiraController::class, 'index');
            $data = $response->getData(true);
            

            if (isset($data[0]['id'])) {
                $this->bandeiras = $data;
            } else {
                $this->bandeiras = $data['data'] ?? [];
            }

            $this->msg = $data['message'] ?? null;
            return $this->bandeiras;
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
            return $this->bandeiras = [];
        }
    }
}
