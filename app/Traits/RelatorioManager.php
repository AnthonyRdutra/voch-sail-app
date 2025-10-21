<?php

namespace App\Traits;

use Illuminate\Http\Client\Request;
use App\Jobs\ExportarRelatorioJob;
use Illuminate\Support\Facades\Storage;
use App\Models\{GrupoEconomico, Bandeira, Colaborador, Unidade};
use App\Traits\LogAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait RelatorioManager
{
    use LogAuditoria;
    /**
     * ==============================
     *   GERENCIAMENTO DE RELATÓRIOS
     * ==============================
     */
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
                default => [],
            };

            // Remove campos duplicados (*_id + campo resolvido)
            $this->dados = collect($this->dados)->map(function ($item) {
                $item = (array) $item;
                return collect($item)->reject(function ($value, $key) use ($item) {
                    $campoRelacionado = str_replace('_id', '', $key);
                    return str_ends_with($key, '_id') && array_key_exists($campoRelacionado, $item);
                })->toArray();
            })->values()->toArray();

            $this->msg = null;
        } catch (\Throwable $e) {
            $this->dados = [];
            $this->msg = 'Erro: ' . $e->getMessage();
        }
    }

    /**
     * ==============================
     *   EDIÇÃO / ATUALIZAÇÃO
     * ==============================
     */
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
        try {
            Log::info("Iniciando saveEdit()", [
                'index' => $index,
                'tipoRelatorio' => $this->tipoRelatorio,
            ]);

            if (!isset($this->dados[$index])) {
                $this->msg = "Erro: índice inválido.";
                Log::warning("Índice inexistente em saveEdit", ['index' => $index]);
                return;
            }

            $item = $this->dados[$index];
            $tipo = $this->tipoRelatorio;

            // Identifica o model dinamicamente
            $modelClass = match ($tipo) {
                'grupos' => GrupoEconomico::class,
                'bandeiras' => Bandeira::class,
                'unidades' => Unidade::class,
                'colaboradores' => Colaborador::class,
                default => null,
            };

            if (!$modelClass) {
                $this->msg = "Modelo não encontrado para o tipo {$tipo}.";
                Log::error("Modelo não encontrado", ['tipo' => $tipo]);
                return;
            }

            $id = $item['id'] ?? null;
            if (!$id) {
                $this->msg = "Registro sem ID.";
                Log::error("ID ausente em registro", ['item' => $item]);
                return;
            }

            $registro = $modelClass::find($id);
            if (!$registro) {
                $this->msg = "Registro não encontrado.";
                Log::error("Registro não localizado", ['id' => $id]);
                return;
            }

            Log::info("Dados originais", $registro->toArray());
            Log::info("Dados editados", $item);

            // Remove chaves imutáveis e campos nulos
            $dadosEditados = collect($item)
                ->except(['id', 'created_at', 'updated_at', 'Data Criação', 'Última atualização'])
                ->mapWithKeys(function ($valor, $chave) {
                    // normaliza nomes vindos do front
                    $mapa = [
                        'Nome' => 'nome',
                        'Data Criação' => 'created_at',
                        'Última atualização' => 'updated_at',
                    ];

                    $novoCampo = $mapa[$chave] ?? $chave;

                    return [$novoCampo => $valor];
                })
                ->filter(fn($v) => $v !== null && $v !== '')
                ->toArray();

            if (empty($dadosEditados)) {
                $this->msg = "Nenhuma alteração detectada.";
                Log::info("Nenhum campo alterado", ['item' => $item]);
                return;
            }

            // Atualiza o registro
            $antes = $registro->toArray();
            $registro->fill($dadosEditados);
            $registro->save();

            // Atualiza o array local para refletir no front
            $this->dados[$index] = $registro->fresh()->toArray();

            $this->audit(
                Auth::user()->name ?? 'Sistema',
                'update',
                ucfirst($this->tipoRelatorio),
                ['antes' => $antes, 'depois' => $dadosEditados]
            );

            $this->msg = "Registro atualizado com sucesso!";

            $this->relatorio();
            Log::info("Registro atualizado com sucesso", ['id' => $id]);
        } catch (\Throwable $e) {
            $this->msg = "Erro ao salvar edição: " . $e->getMessage();
            Log::error("Erro em saveEdit()", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }


    public function delete($id)
    {
        try {
            Log::info('Iniciando exclusão (trait RelatorioManager)', [
                'id' => $id,
                'tipoRelatorio' => $this->tipoRelatorio,
            ]);

            // Resolve dinamicamente o Model conforme o tipo selecionado
            $modelClass = match ($this->tipoRelatorio) {
                'grupos'        => \App\Models\GrupoEconomico::class,
                'bandeiras'     => \App\Models\Bandeira::class,
                'unidades'      => \App\Models\Unidade::class,
                'colaboradores' => \App\Models\Colaborador::class,
                default         => null,
            };

            if (!$modelClass) {
                $this->msg = 'Tipo de relatório inválido para exclusão.';
                Log::error('Modelo não mapeado para exclusão', ['tipoRelatorio' => $this->tipoRelatorio]);
                return;
            }

            $registro = $modelClass::find($id);

            if (!$registro) {
                $this->msg = "Registro não encontrado (ID: {$id}).";
                Log::warning('Tentativa de exclusão de registro inexistente', ['id' => $id, 'model' => $modelClass]);
                // Remove da lista local caso já tenha sido removido em outra aba/usuário
                $this->dados = array_values(array_filter($this->dados, fn($item) => ($item['id'] ?? null) != $id));
                return;
            }

            Log::info('Registro localizado para exclusão', ['id' => $id, 'model' => $modelClass]);

            $registro->delete();

            Log::info('Exclusão realizada com sucesso', ['id' => $id, 'model' => $modelClass]);

            // Atualiza a tabela local imediata (mais leve que refazer a consulta toda)
            $this->dados = array_values(array_filter($this->dados, fn($item) => ($item['id'] ?? null) != $id));

            $this->audit(
                Auth::user()->name ?? 'Sistema',
                'delete',
                ucfirst($this->tipoRelatorio),
                $dadosAntes
            );

            $this->msg = 'Registro excluído com sucesso.';
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao excluir: ' . $e->getMessage();
            Log::error('Erro no delete() do RelatorioManager', [
                'id' => $id,
                'tipoRelatorio' => $this->tipoRelatorio,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * ==============================
     *   EXPORTAÇÃO DE RELATÓRIOS
     * ==============================
     */
    public function exportarAtual()
    {
        $tipo = $this->tipoRelatorio;

        if (!isset($this->exportar[$tipo])) {
            $this->msg = 'Tipo de exportação inválido.';
            return;
        }

        if (empty($this->dados)) {
            $this->msg = 'Nenhum dado disponível para exportação.';
            return;
        }

        $this->msg = 'Gerando relatório...';
        $this->exportConcluido = false;
        $this->pollingAtivo = true;

        // Caminho e nome do arquivo
        $timestamp = now()->format('Ymd_His');
        $this->arquivoGerado = "exports/relatorios_completos_{$tipo}_{$timestamp}.xlsx";

        ExportarRelatorioJob::dispatch(
            $this->dados,
            $tipo,
            storage_path("app/{$this->arquivoGerado}")
        );
    }

    public function verificarExportacao()
    {
        if (!$this->pollingAtivo || !$this->arquivoGerado) {
            return;
        }

        if (Storage::exists($this->arquivoGerado)) {
            $this->exportConcluido = true;
            $this->pollingAtivo = false;
            $this->msg = 'Exportação concluída com sucesso.';
        }
    }

    public function baixarRelatorio()
    {
        if (!$this->exportConcluido || !$this->arquivoGerado) {
            $this->msg = 'Nenhum arquivo disponível para download.';
            return;
        }

        $path = storage_path("app/{$this->arquivoGerado}");
        if (!file_exists($path)) {
            $this->msg = 'Arquivo não encontrado no servidor.';
            return;
        }

        return response()->download($path);
    }

    public function resetarExportacao()
    {
        $this->msg = null;
        $this->pollingAtivo = false;
        $this->exportConcluido = false;
        $this->arquivoGerado = null;
    }

    public function toggleExport($tipo)
    {
        if (!array_key_exists($tipo, $this->exportar)) {
            return;
        }

        $this->exportar[$tipo] = !$this->exportar[$tipo];
    }

    public function exportarTodos()
    {
        $ativos = collect($this->exportar)
            ->filter(fn($ativo) => $ativo)
            ->keys()
            ->toArray();

        if (empty($ativos)) {
            $this->msg = 'Nenhum tipo de relatório selecionado para exportação.';
            return;
        }

        foreach ($ativos as $tipo) {
            $this->tipoRelatorio = $tipo;
            $this->relatorio();
            $this->exportarAtual();
        }

        $this->msg = 'Exportações iniciadas.';
    }

    public function atualizarDurantePolling()
    {
        if ($this->pollingAtivo) {
            $this->verificarExportacao();
        }
    }
}
