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
                $this->msg = 'Tipo de relatório inválido.';
                $this->dados = [];
                return;
            }

            // Coleta e formata dados conforme o tipo
            switch ($this->tipoRelatorio) {
                case 'grupos':
                    $dados = \App\Models\GrupoEconomico::all()->toArray();
                    $this->dados = $this->mapGrupos($dados);
                    break;

                case 'bandeiras':
                    $dados = \App\Models\Bandeira::with('grupoEconomico')->get()->toArray();
                    $this->dados = $this->mapBandeiras($dados);
                    break;

                case 'unidades':
                    $dados = \App\Models\Unidade::with('bandeira')->get()->toArray();
                    $this->dados = $this->mapUnidades($dados);
                    break;

                case 'colaboradores':
                    $dados = \App\Models\Colaborador::with('unidade')->get()->toArray();
                    $this->dados = $this->mapColaboradores($dados);
                    break;

                default:
                    $this->dados = [];
            }

            // Normaliza as chaves para não quebrar o Blade
            $this->dados = collect($this->dados)
                ->map(function ($item) {
                    return collect($item)->keyBy(
                        fn($v, $k) =>
                        str_replace(
                            [' ', 'á', 'ã', 'â', 'ç', 'é', 'ó', 'í', 'ú', 'Á', 'Ã', 'Ç', 'É', 'Ó', 'Í', 'Ú'],
                            ['_', 'a', 'a', 'a', 'c', 'e', 'o', 'i', 'u', 'A', 'A', 'C', 'E', 'O', 'I', 'U'],
                            mb_strtoupper($k)
                        )
                    )->toArray();
                })
                ->toArray();

            $this->foreignOptions = $this->getForeignOptions($this->tipoRelatorio);
            $this->msg = "Exibindo relatório de " . ucfirst($this->tipoRelatorio) . ".";
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao carregar relatório: ' . $e->getMessage();
            $this->dados = [];
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

    public function saveEdit($id)
    {
        try {
            Log::debug("🟡 [saveEdit] Iniciando", [
                'id' => $id,
                'tipoRelatorio' => $this->tipoRelatorio,
            ]);

            // Busca o item correto dentro do array $this->dados
            $index = collect($this->dados)->search(fn($item) => ($item['ID'] ?? $item['id'] ?? null) == $id);

            if ($index === false) {
                $this->msg = "Registro não encontrado localmente (ID {$id}).";
                Log::warning("⚠️ [saveEdit] ID {$id} não encontrado em dados locais", [
                    'ids_existentes' => collect($this->dados)->pluck('id')->toArray(),
                ]);
                return;
            }

            $item = $this->dados[$index];
            $tipo = $this->tipoRelatorio;

            // Normaliza as chaves para lowercase
            $itemLower = array_change_key_case($item, CASE_LOWER);

            // Agora a busca por 'id' sempre funciona
            $id = $itemLower['id'] ?? null;
            if (!$id) {
                $this->msg = "Registro sem ID.";
                Log::error("ID ausente em registro", ['item' => $item]);
                return;
            }
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
