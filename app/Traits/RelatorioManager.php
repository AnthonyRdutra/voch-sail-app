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
            $controllerClass = $this->getControllerByType($this->tipoRelatorio ?? '');
            if (!$controllerClass) {
                $this->msg = "Tipo de relatório inválido.";
                return;
            }

            $this->audit(Auth::user()->name, 'delete', $this->tipoRelatorio, $this->dados);

            // Localiza o item (aceita 'id' ou 'ID')
            $index = collect($this->dados)->search(
                fn($i) => (($i['id'] ?? $i['ID'] ?? null) == $id)
            );

            if ($index === false) {
                $this->msg = "Registro não encontrado localmente.";
                return;
            }

            $item = $this->dados[$index];

            // ==== 1) Mapeia labels → campos reais (por tipo de relatório) ====
            // Ajuste os aliases conforme seus controllers/DB:
            $aliasesPorTipo = [
                'grupos' => [
                    'nome' => ['NOME', 'Nome', 'Grupo', 'GRUPO'],
                ],
                'bandeiras' => [
                    'nome' => ['NOME', 'Nome', 'Bandeira', 'BANDEIRA'],
                    'grupo_economico_id' => ['GRUPO', 'GRUPO_ECONOMICO', 'Grupo Econômico'],
                ],
                'unidades' => [
                    'nome' => ['NOME', 'Nome', 'Unidade', 'UNIDADE'],
                    'bandeira_id' => ['BANDEIRA', 'Bandeira'],
                    'grupo_economico_id' => ['GRUPO', 'GRUPO_ECONOMICO', 'Grupo Econômico'],
                ],
                'colaboradores' => [
                    'nome' => ['NOME', 'Nome'],
                    'unidade_id' => ['UNIDADE', 'Unidade'],
                ],
            ];

            $aliases = $aliasesPorTipo[$this->tipoRelatorio] ?? [];

            // ==== 2) Normaliza as chaves do item e aplica aliases ====
            $dadosNormalizados = [];

            // Primeiro: transforma todas as chaves em snake-case minúsculo
            foreach ($item as $chave => $valor) {
                $k = Str::snake(Str::of($chave)->trim()->lower()->toString());

                // Ignora metacampos exibidos
                if (in_array($k, ['id', 'created_at', 'updated_at', 'data_criacao', 'ultima_atualizacao'])) {
                    continue;
                }

                $dadosNormalizados[$k] = $valor;
            }

            // Depois: aplica aliases para garantir que 'nome' exista, etc.
            foreach ($aliases as $campoReal => $possiveisLabels) {
                if (!array_key_exists($campoReal, $dadosNormalizados)) {
                    foreach ($possiveisLabels as $label) {
                        $lk = Str::snake(Str::of($label)->trim()->lower()->toString());
                        if (array_key_exists($lk, $dadosNormalizados)) {
                            $dadosNormalizados[$campoReal] = $dadosNormalizados[$lk];
                            break;
                        }
                    }
                }
            }

            // Segurança extra: remova chaves “apresentacionais” que tenham
            // escapado, mantendo só o que é snake-case alfanumérico/underscore.
            $dadosNormalizados = collect($dadosNormalizados)
                ->filter(fn($v, $k) => preg_match('/^[a-z0-9_]+$/', $k))
                ->toArray();

            // ==== 3) Forma mais robusta de montar o Request ====
            // Usa POST + _method=PUT para que $request->input() pegue tudo.
            $request = Request::create(
                uri: '',
                method: 'POST',
                parameters: array_merge($dadosNormalizados, ['_method' => 'PUT'])
            );
            $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

            // ==== 4) Chama o controller ====
            $controller = app($controllerClass);
            $response = $controller->update($request, $id);

            // ==== 5) Extrai payload de forma defensiva ====
            if ($response instanceof JsonResponse) {
                $payload = json_decode($response->getContent(), true);
            } elseif (method_exists($response, 'getData')) {
                $payload = $response->getData(true);
            } else {
                $payload = (array) $response;
            }

            // ==== 6) Atualiza a linha localmente (se vier 'data') ====
            $dataAtualizada = $payload['data'] ?? null;
            if ($dataAtualizada) {
                $this->dados[$index] = $dataAtualizada;
            }

            $this->msg = $payload['message'] ?? 'Registro atualizado com sucesso!';

            \Log::info('Atualização concluída', [
                'id' => $id,
                'controller' => $controllerClass,
                'payload_enviado' => $dadosNormalizados,
                'msg' => $this->msg,
            ]);
        } catch (\Throwable $e) {
            $this->msg = "Erro ao atualizar: " . $e->getMessage();
            \Log::error('Erro em saveEdit()', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }


    public function delete($id)
    {
        try {
            Log::info('🧭 MÉTODO DELETE INICIADO', ['id' => $id, 'tipo' => $this->tipoRelatorio]);

            $modelClass = match ($this->tipoRelatorio) {
                'grupos'        => \App\Models\GrupoEconomico::class,
                'bandeiras'     => \App\Models\Bandeira::class,
                'unidades'      => \App\Models\Unidade::class,
                'colaboradores' => \App\Models\Colaborador::class,
                default         => null,
            };

            if (!$modelClass) {
                $this->msg = 'Tipo de relatório inválido.';
                return;
            }

            $registro = $modelClass::find($id);
            if (!$registro) {
                $this->msg = "Registro não encontrado (ID: {$id}).";
                $this->dados = array_values(array_filter(
                    $this->dados,
                    fn($item) => (($item['id'] ?? $item['ID'] ?? null) != $id)
                ));
                return;
            }

            $registro->delete();

            $this->dados = array_values(array_filter(
                $this->dados,
                fn($item) => (($item['id'] ?? $item['ID'] ?? null) != $id)
            ));

            $this->audit(Auth::user()->name, 'delete', $this->tipoRelatorio, [
                'id' => $id,
                'nome' => $registro->nome ?? null,
            ]);

            $this->msg = "Registro excluído com sucesso (ID {$id}).";
            Log::info('✅ Exclusão concluída', ['id' => $id]);
        } catch (\Throwable $e) {
            $this->msg = 'Erro ao excluir: ' . $e->getMessage();
            Log::error('💥 Erro em delete()', [
                'id' => $id,
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
