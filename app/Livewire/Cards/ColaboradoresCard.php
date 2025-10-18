<?php

namespace App\Livewire\Cards;

use Livewire\Component;
use App\Http\Controllers\{ColaboradorController, UnidadeController};
use App\Traits\ControllerInvoker;

class ColaboradoresCard extends Component
{
    use ControllerInvoker;

    public $colaborador_nome;
    public $email;
    public $cpf;
    public $unidade_id;
    public $unidades = [];
    public $msg = null;
    public bool $canSave = false;

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
                'unidade_id' => $this->unidade_id,
            ]);

            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'Colaborador salvo com sucesso!';

            // limpa os campos após salvar
            $this->reset(['colaborador_nome', 'email', 'cpf', 'unidade_id']);
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }

    public function listUnidade()
    {
        try {
            $response = $this->callController(UnidadeController::class, 'index');
            $data = json_decode(json_encode($response->getData(true)), true) ?? [];

            $unidades = $data['data'] ?? $data ?? [];
            
            if (isset($unidades[0]) && is_array($unidades[0]) && array_is_list($unidades[0])) {
                $unidades = $unidades[0];
            }

            $this->unidades = $unidades; 
            $this->canSave = count($this->unidades) > 0;
            $this->msg = $this->canSave
                ? null
                : 'Não há unidades cadastradas. Cadastre uma antes de criar colaboradores.';
        } catch (\Throwable $e) {
            $this->unidades = [];
            $this->canSave = false;
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }
}
