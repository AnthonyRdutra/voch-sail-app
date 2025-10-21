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
            $controllerClass = $this->getControllerByType($this->tipoRelatorio);

            if (!$controllerClass) {
                $this->msg = 'Tipo de relatório inválido';
                $this->dados = [];
                return;
            }

            Log::debug("🔹 [Relatorio] Iniciando geração para tipo: {$this->tipoRelatorio}");
            Log::debug("🔹 [Relatorio] Controller: {$controllerClass}");

            // ================================
            // 1️⃣ CHAMADA DO CONTROLLER
            // ================================
            $response = $this->callController($controllerClass, 'index');

            // Padroniza o retorno (JsonResponse, array, View, etc.)
            if (is_object($response) && method_exists($response, 'getData')) {
                $payload = $response->getData(true);
            } elseif (is_array($response)) {
                $payload = $response;
            } elseif (is_object($response) && property_exists($response, 'original')) {
                $payload = $response->original;
            } else {
                $payload = (array) $response;
            }

            Log::debug('🔸 [Relatorio] Payload bruto recebido:', ['payload' => $payload]);

            if (isset($payload['data'])) {
                $items = $payload['data'];
            } elseif (isset($payload['payload'])) {
                $items = $payload['payload'];
            } else {
                $items = $payload;
            }

            if (!is_array($items)) {
                $items = json_decode(json_encode($items), true);
            }

            if (isset($items['id'])) {
                $items = [$items];
            }

            Log::debug('🔹 [Relatorio] Itens antes do flatten:', ['items' => $items]);

            // ================================
            // 2️⃣ ACHATA ARRAYS ANINHADOS
            // ================================
            $items = collect($items)->map(function ($item) {
                if (!is_array($item)) return [];
                foreach ($item as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $subKey => $subVal) {
                            $item["{$key}_{$subKey}"] = $subVal;
                        }
                        unset($item[$key]);
                    }
                }
                return $item;
            })->filter(fn($i) => !empty($i))->values()->toArray();

            Log::debug('🔹 [Relatorio] Itens após flatten:', ['items' => $items]);

            // ================================
            // 3️⃣ MAPEAMENTO FORMATADO
            // ================================
            $this->dados = match ($this->tipoRelatorio) {
                'grupos'        => $this->mapGrupos($items),
                'bandeiras'     => $this->mapBandeiras($items),
                'unidades'      => $this->mapUnidades($items),
                'colaboradores' => $this->mapColaboradores($items),
                default         => [],
            };

            Log::debug("🔸 [Relatorio] Dados após map ({$this->tipoRelatorio}):", ['dados' => $this->dados]);

            // ================================
            // 4️⃣ REMOVE DUPLICADOS
            // ================================
            $this->dados = collect($this->dados)->map(function ($item) {
                $item = (array) $item;
                return collect($item)->reject(function ($value, $key) use ($item) {
                    $campoRelacionado = str_replace('_id', '', $key);
                    return str_ends_with($key, '_id') && array_key_exists($campoRelacionado, $item);
                })->toArray();
            })->values()->toArray();

            Log::debug("✅ [Relatorio] Dados finais formatados:", ['dados' => $this->dados]);

            $this->msg = null;
        } catch (\Throwable $e) {
            $this->dados = [];
            $this->msg = 'Erro ao gerar relatório: ' . $e->getMessage();

            Log::error('❌ [Relatorio] Erro ao gerar relatório', [
                'tipo' => $this->tipoRelatorio,
                'mensagem' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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
                Auth::user()->name,
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

            $this->audit(Auth::user()->name, 'delete', $this->tipo, $registro);
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
    public function confirmarExportacao()
    {
        $this->msg = null;
        $this->arquivoGerado = null;
        $this->exportConcluido = false;
        $this->pollingAtivo = false;

        try {
            $tipo = $this->tipoRelatorio;

            $dados = match ($tipo) {
                'grupos' => GrupoEconomico::all()->toArray(),
                'bandeiras' => Bandeira::with('grupoEconomico')->get()->toArray(),
                'unidades' => Unidade::with('bandeira')->get()->toArray(),
                'colaboradores' => Colaborador::with('unidade')->get()->toArray(),
                default => [],
            };

            if (empty($dados)) {
                $this->msg = 'Nenhum dado encontrado para exportar.';
                return;
            }

            // Formata de acordo com o tipo
            $dadosFormatados = $this->formatarPorTipo($tipo, $dados);
            $this->dadosFormatados = [$tipo => $dadosFormatados];

            // Caminho de exportação
            $exportPath = storage_path('app/exports');
            if (!is_dir($exportPath)) {
                mkdir($exportPath, 0777, true);
            }

            // Nome base fixo (mantendo compatibilidade com o Job existente)
            $tipoBase = 'relatorios_completos';

            // Dispara o Job para a fila
            ExportarRelatorioJob::dispatch($this->dadosFormatados, $tipoBase, 'exports');

            $this->msg = 'Exportação iniciada, o arquivo será gerado em alguns segundos...';
            $this->pollingAtivo = true;
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao iniciar exportação: ' . $e->getMessage();
        }
    }

    public function verificarExportacao()
    {
        $arquivos = glob(storage_path('app/exports/relatorios_completos_*.xlsx'));

        if (!empty($arquivos)) {
            $ultimo = collect($arquivos)->sortDesc()->first();
            $nomeArquivo = basename($ultimo);

            $this->arquivoGerado = asset('storage/exports/' . $nomeArquivo);
            $this->msg = 'Exportação concluída! Clique para baixar.';
            $this->exportConcluido = true;
            $this->pollingAtivo = false;
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
