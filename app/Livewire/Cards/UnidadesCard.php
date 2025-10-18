<?php

namespace App\Livewire\Cards;

use Livewire\Component;
use App\Http\Controllers\{UnidadeController, BandeiraController};
use App\Traits\ControllerInvoker;

class UnidadesCard extends Component
{
    use ControllerInvoker;

    public $nome_fantasia;
    public $razao_social;
    public $cnpj;
    public $bandeira_id;
    public $bandeiras = [];
    public $msg = null;
    public bool $canSave = false;

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
                'bandeira_id' => $this->bandeira_id,
            ]);

            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'Unidade salva com sucesso!';

            // limpa os campos corretos
            $this->reset(['nome_fantasia', 'razao_social', 'cnpj', 'bandeira_id']);
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }

    public function listBandeira()
    {
        try {
            $response = $this->callController(BandeiraController::class, 'index');
            $data = json_decode(json_encode($response->getData(true)), true) ?? [];

            $bandeiras = $data['data'] ?? $data ?? [];
            $this->bandeiras = is_array($bandeiras) ? $bandeiras : [];

            $this->canSave = count($this->bandeiras) > 0;
            $this->msg = $this->canSave
                ? null
                : 'Não há bandeiras cadastradas. Cadastre uma bandeira antes de criar unidades.';
        } catch (\Throwable $e) {
            $this->bandeiras = [];
            $this->canSave = false;
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }
}
