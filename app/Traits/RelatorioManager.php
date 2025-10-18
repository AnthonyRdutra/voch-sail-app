<?php

namespace App\Traits;

use Illuminate\Http\Client\Request;

trait RelatorioManager
{
    public function relatorio()
    {
        try {
            $controller = $this->getController();

            if (!$controller) {
                $this->msg = 'Tipo de relatório inválido';
                $this->dados = [];
                return;
            }

            $response = $this->callController($controller, 'index');
            $payload = $response->getData(true);
            $items = $payload['data'] ?? $payload;

            if (isset($items['id'])) $items = [$items];

            $this->dados = match ($this->tipoRelatorio) {
                'grupos' => $this->mapGrupos($items),
                'bandeiras' => $this->mapBandeiras($items),
                'unidades' => $this->mapUnidades($items),
                'colaboradores' => $this->mapColaboradores($items),
            };

            $this->msg = null;
        } catch (\Throwable $e) {
            $this->dados = [];
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }

    public function edit($index)
    {
        $this->editIndex = $index;
    }

    public function cancelEdit()
    {
        $this->editIndex = null;
    }

    public function saveEdit($index)
    {
        $registro = $this->dados[$index] ?? null;
        if (!$registro) return;

        $controller = $this->getController();

        $clean = collect($registro)
            ->reject(fn($v, $k) => is_array($v) || $k === 'id')
            ->toArray();

        try {
            $this->callController($controller, 'update', [
                'id' => $registro['id'] ?? null,
                'request' => new Request($clean)
            ]);
            $this->msg = 'Registro atualizado com sucesso!';
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
        }

        $this->editIndex = null;
        $this->relatorio();
    }

    public function delete($id)
    {
        try {
            $controller = $this->getController();
            $response = $this->callController($controller, 'destroy', ['id' => $id]);
            $data = $response->getData(true);

            $this->msg = $data['message'] ?? 'Registro excluído com sucesso';
            $this->relatorio();
        } catch (\Throwable $e) {
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }
}
