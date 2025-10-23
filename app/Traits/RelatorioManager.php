<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Jobs\ExportarRelatorioJob;
use Illuminate\Support\Facades\Storage;
use App\Models\{GrupoEconomico, Bandeira, Colaborador, Unidade};
use App\Traits\LogAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Traits\ControllerInvoker;

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
            };

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


            if (!empty($this->dados)) {
                $this->headers = array_keys(collect($this->dados)->first());
                $this->headers = array_filter($this->headers, fn($h) => $h !== 'unformatted_data');
            }


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

    public function saveEdit($index)
    {
        try {
            if (!isset($this->dados[$index])) {
                Log::warning("Índice inválido em saveEdit: {$index}");
                return;
            }

            $linha = $this->dados[$index];

            $dataOriginal = $linha['unformatted_data'] ?? [];
            $id = $dataOriginal['id'] ?? null;

            if (!$id) {
                Log::error("Nenhum ID encontrado para atualização", $dataOriginal);
                return;
            }

            $editData = $this->editData ?? [];
            if (empty($editData)) {
                Log::warning("Nenhum dado de edição recebido", ['index' => $index]);
                return;
            }


            $controller = $this->getController($this->tipoRelatorio);
            if (!$controller) {
                Log::error("Controller não encontrado para tipo: {$this->tipoRelatorio}");
                return;
            }
            $mudancas = $this->compararArraysSimples($dataOriginal, $editData);

            $this->audit(Auth::user()->name, 'update', $this->tipoRelatorio,$mudancas);
            
            $request = new Request($editData);

            Log::debug("Chamando update em {$controller}::update()", [
                'id' => $id,
                'dados' => $editData,
            ]);

            $arr = $this->compararArrays($this->editData, $editData);
            $response = app($controller)->update($request, $id);

            $this->dados[$index]['unformatted_data'] = array_merge($dataOriginal, $editData);

            $this->msg = 'Registro atualizado com sucesso!';
            $this->fecharEdicao();

            Log::info(" Registro atualizado com sucesso", ['id' => $id]);
        } catch (\Throwable $e) {
            Log::error("Erro ao salvar edição: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->msg = 'Erro ao salvar a edição.';
        }
    }

    protected function compararArraysSimples(array $original, array $atualizado): array
    {
        $ignorar = ['id', 'created_at', 'updated_at'];
        $diferencas = [];

        $original = array_diff_key($original, array_flip($ignorar));
        $atualizado = array_diff_key($atualizado, array_flip($ignorar));

        foreach ($atualizado as $chave => $valorNovo) {
            $valorAntigo = $original[$chave] ?? null;

            if (is_array($valorNovo) || is_array($valorAntigo)) {
                continue; // ignora subarrays
            }

            if ($valorAntigo !== $valorNovo) {
                $diferencas[$chave] = [
                    'antes' => $valorAntigo,
                    'depois' => $valorNovo,
                ];
            }
        }

        return $diferencas;
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

            $dadosFormatados = $this->formatarPorTipo($tipo, $dados);
            $this->dadosFormatados = [$tipo => $dadosFormatados];

            // Caminho de exportação
            $exportPath = storage_path('app/exports');
            if (!is_dir($exportPath)) {
                mkdir($exportPath, 0777, true);
            }

            $tipoBase = 'relatorios_completos';

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
        $this->audit(Auth::user()->name, 'export', $this->tipoRelatorio, 'exportação de relatório');
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
