<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Http\Controllers\{
    GrupoEconomicoController,
    BandeiraController,
    UnidadeController,
    ColaboradorController
};
use App\Traits\ControllerInvoker;
use App\Traits\LogAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CadastrosComponent extends Component
{
    use ControllerInvoker, LogAuditoria;

    // ====== GRUPOS ======
    public $grupo_nome;

    // ====== BANDEIRAS ======
    public $bandeira_nome;
    public $grupo_economico_id;
    public $grupoEconomico = [];

    // ====== UNIDADES ======
    public $nome_fantasia;
    public $razao_social;
    public $cnpj;
    public $bandeira_id;
    public $bandeiras = [];

    // ====== COLABORADORES ======
    public $colaborador_nome;
    public $email;
    public $cpf;
    public $unidade_id;
    public $unidades = [];

    // ====== CONTROLE ======
    public $msg = null;

    public function mount()
    {
        $this->refreshLists();
    }

    private function refreshLists()
    {
        $this->listGrupoEconomico();
        $this->listBandeira();
        $this->listUnidade();
    }

    // ===========================
    // GRUPOS
    // ===========================
    public function grupoStore()
    {
        $name = $this->grupo_nome;
        try {
            $response = $this->callController(GrupoEconomicoController::class, 'store', [
                'nome' => $this->grupo_nome
            ]);
            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'Grupo salvo com sucesso!';

            $this->reset('grupo_nome');
            $this->audit(Auth::user()->name, 'add', 'grupo_economico', $name);
            $this->refreshLists();
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao salvar grupo: ' . $e->getMessage();
        }
    }

    private function listGrupoEconomico()
    {
        try {
            $res = $this->callController(GrupoEconomicoController::class, 'index');
            $data = json_decode(json_encode($res->getData(true)), true);
            $this->grupoEconomico = $data['data'] ?? [];
        } catch (\Throwable $e) {
            $this->grupoEconomico = [];
        }
    }

    // ===========================
    // BANDEIRAS
    // ===========================
    public function bandeiraStore()
    {
        $name = $this->bandeira_nome; 
        try {
            $response = $this->callController(BandeiraController::class, 'store', [
                'nome' => $this->bandeira_nome,
                'grupo_economico_id' => $this->grupo_economico_id
            ]);
            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'Bandeira salva com sucesso!';
            $this->reset(['bandeira_nome', 'grupo_economico_id']);
            $this->refreshLists();
            $this->audit(Auth::user()->name, 'add', 'bandeira', $name);
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao salvar bandeira: ' . $e->getMessage();
        }
    }

    private function listBandeira()
    {
        try {
            $response = $this->callController(BandeiraController::class, 'index');
            $data = json_decode(json_encode($response->getData(true)), true) ?? [];

            $bandeiras = $data['data'] ?? $data ?? [];
            $this->bandeiras = is_array($bandeiras) ? $bandeiras : [];

            $this->msg = 'Não há bandeiras cadastradas. Cadastre uma bandeira antes de criar unidades.';
        } catch (\Throwable $e) {
            $this->bandeiras = [];
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }

    // ===========================
    // UNIDADES
    // ===========================
    public function unidadeStore()
    {
        $name = $this->nome_fantasia;
        try {
            $response = $this->callController(UnidadeController::class, 'store', [
                'nome_fantasia' => $this->nome_fantasia,
                'razao_social'  => $this->razao_social,
                'cnpj'          => $this->cnpj,
                'bandeira_id'   => $this->bandeira_id,
            ]);

            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'Unidade salva com sucesso!';
            $this->reset(['nome_fantasia', 'razao_social', 'cnpj', 'bandeira_id']);
            $this->refreshLists();
            $this->audit(Auth::user()->name, 'add', 'unidades', $name);
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao salvar unidade: ' . $e->getMessage();
        }
    }

    private function listUnidade()
    {
        try {
            $response = $this->callController(UnidadeController::class, 'index');
            $data = json_decode(json_encode($response->getData(true)), true) ?? [];

            $unidades = $data['data'] ?? $data ?? [];

            if (isset($unidades[0]) && is_array($unidades[0]) && array_is_list($unidades[0])) {
                $unidades = $unidades[0];
            }

            $this->unidades = $unidades;
            $this->msg = 'Não há unidades cadastradas. Cadastre uma antes de criar colaboradores.';
        } catch (\Throwable $e) {
            $this->unidades = [];
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }


    // ===========================
    // COLABORADORES
    // ===========================
    public function colaboradorStore()
    {   
        $name = $this->colaborador_nome; 
        try {
            $response = $this->callController(ColaboradorController::class, 'store', [
                'nome'       => $this->colaborador_nome,
                'email'      => $this->email,
                'cpf'        => $this->cpf,
                'unidade_id' => $this->unidade_id,
            ]);

            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'Colaborador salvo com sucesso!';
            $this->reset(['colaborador_nome', 'email', 'cpf', 'unidade_id']);
            $this->refreshLists();
            $this->audit(Auth::user()->name, 'add', 'colaboradores', $name);
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao salvar colaborador: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.pages.cadastros-component')
            ->layout('layouts.app');
    }
}
