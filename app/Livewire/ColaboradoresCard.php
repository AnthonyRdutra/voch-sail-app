<?php

namespace App\Livewire;

use Livewire\Component;
use App\Http\Controllers\{ColaboradorController, UnidadeController};
use App\Traits\ControllerInvoker;

class ColaboradoresCard extends Component
{
    use ControllerInvoker;

    public $msg;
    public $unidades = [];
    public $unidade_id;
    public $colaborador_nome;
    public $cpf;
    public $email;

    public function mount()
    {
        $this->listUnidade();
    }

    public function colaboradorStore()
    {
        try {
            $response = $this->callController(ColaboradorController::class, 'store', [
                'nome' => $this->colaborador_nome,
                'email' => $this->email,
                'cpf' => $this->cpf,
                'unidade_id' => $this->unidade_id
            ]);

            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'bandeira salva com sucesso';
            $this->reset(['colaborador_nome', 'email', 'cpf', 'unidade_id']);
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }

    public function listUnidade()
    {
        try {
            $response = $this->callController(UnidadeController::class, 'index');
            $data = $response->getData(true);

            $unidades = $data['data'] ?? $data ?? [];

            // Corrige caso o controller retorne array aninhado
            if (isset($unidades[0]) && is_array($unidades[0]) && isset($unidades[0][0])) {
                $unidades = $unidades[0];
            }

            $this->unidades = is_array($unidades) ? $unidades : [];
            $this->msg = $data['message'] ?? null;
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
            $this->unidades = [];
        }
    }
}
