<?php

namespace App\Livewire\Cards;

use Livewire\Component;
use App\Http\Controllers\GrupoEconomicoController;
use App\Traits\ControllerInvoker;

class GruposCard extends Component
{
    use ControllerInvoker;
    public $msg;
    public $nome;
    public $grupo_nome;

    public function grupoStore()
    {
        try {

            $response = $this->callController(GrupoEconomicoController::class, 'store', [
                'nome' => $this->grupo_nome
            ]);

            $data = $response->getData(true);
            $this->msg = 'OK ' . $data['message'] ?? 'grupo salvo com sucesso';
            $this->reset('nome');
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }
}
