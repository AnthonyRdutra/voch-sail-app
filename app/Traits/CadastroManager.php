<?php

namespace App\Traits;

use App\Http\Controllers\{GrupoEconomicoController, BandeiraController, UnidadeController, ColaboradorController};

use App\Traits\ControllerInvoker;

trait CadastroManager
{

    use ControllerInvoker;

    public $msg;
    public bool $canSave;

    public function salvarGrupo($nome)
    {
        try {
            $response = $this->callController(GrupoEconomicoController::class, 'store', [
                'nome' => $nome
            ]);

            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'Grupo Econômico salvo com sucesso.';
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao salvar grupo: ' . $e->getMessage();
        }
    }

    public function listarGrupos()
    {
        try {
            $response = $this->callController(GrupoEconomicoController::class, 'index');
            $grupos = json_decode(json_encode($response->getData(true)), true)['data'] ?? [];
            $this->canSave = count($grupos) > 0;
            return $grupos;
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao listar grupos: ' . $e->getMessage();
            return;
        }
    }

    public function salvarBandeira($nome, $grupoId)
    {
        try {
            $response = $this->callController(BandeiraController::class, 'store', [
                'nome' => $nome,
                'grupo_economico_id' => $grupoId,
            ]);

            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'Bandeira salva com sucesso.';
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao salvar bandeira: ' . $e->getMessage();
        }
    }

    public function listarBandeiras()
    {
        try {
            $response = $this->callController(BandeiraController::class, 'index');
            $bandeiras = json_decode(json_encode($response->getData(true)), true)['data'] ?? [];
            $this->canSave = count($bandeiras) > 0;
            return $bandeiras;
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao listar bandeiras: ' . $e->getMessage();
            return [];
        }
    }

    public function salvarUnidade($dados)
    {
        try {
            $response = $this->callController(UnidadeController::class, 'store', $dados);
            $data = $response->getData(true);
            $this->msg = $data['message'] ?? 'Unidade salva com sucesso.';
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao salvar unidade: ' . $e->getMessage();
        }
    }

    public function listarUnidades()
    {
        try {
            $response = $this->callController(UnidadeController::class, 'index');
            $unidades = json_decode(json_encode($response->getData(true)), true)['data'] ?? [];
            $this->canSave = count($unidades) > 0;
            return $unidades;
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao listar unidades: ' . $e->getMessage();
            return [];
        }
    }
}
