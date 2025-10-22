<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Jobs\ExportarRelatorioJob;
use App\Models\{Bandeira, Colaborador, GrupoEconomico, Unidade};
use App\Traits\{
    ControllerInvoker,
    RelatorioFormatter,
    RelatorioControllerResolver,
    RelatorioExporter,
    RelatorioManager
};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RelatoriosComponent extends Component
{

     use ControllerInvoker, RelatorioFormatter, RelatorioControllerResolver, RelatorioExporter, RelatorioManager;

    public $tipoRelatorio = 'grupos';
    public $dados = [];
    public $msg;
    public $editIndex = null;
    public $foreignOptions;
    public $exportar = [
        'grupos' => false,
        'bandeiras' => false,
        'unidades' => false,
        'colaboradores' => false,
    ];
    public $dadosFormatados;
    public $arquivoGerado;
    public $exportConcluido = false;
    public $pollingAtivo = false;
    public $pollingTentativas = 0;
    public $pollingMaxTentativas = 10;
    public $path = 'exports';

    public function mount()
    {
        $this->relatorio();
        $this->foreignOptions = $this->getForeignOptions($this->tipoRelatorio);
    }

    public function updatedTipoRelatorio()
    {
        $this->relatorio();
        $this->foreignOptions = $this->getForeignOptions($this->tipoRelatorio);
    }

    public function confirmarExportacao()
    {
        Log::debug('[EXPORTAÇÃO] Clique recebido - iniciando processamento');

        $selecionados = array_keys(array_filter($this->exportar));
        Log::debug('[EXPORTAÇÃO] Opções selecionadas:', $selecionados);

        if (empty($selecionados)) {
            $this->msg = 'Selecione ao menos uma opção para poder exportar.';
            Log::warning('[EXPORTAÇÃO] Nenhuma opção marcada para exportação');
            return;
        }

        $this->exportConcluido = false;
        $this->pollingAtivo = true;
        $this->pollingTentativas = 0;
        $this->dadosFormatados = [];
        Log::debug('[EXPORTAÇÃO] Estado resetado. Polling iniciado.');

        // === Etapa 3: coletar dados conforme os tipos ===
        foreach ($selecionados as $tipo) {
            try {
                Log::debug("[EXPORTAÇÃO] Coletando dados para tipo: {$tipo}");

                $dados = match ($tipo) {
                    'grupos' => GrupoEconomico::all()->toArray(),
                    'bandeiras' => Bandeira::with('grupoEconomico')->get()->toArray(),
                    'unidades' => Unidade::with('bandeira')->get()->toArray(),
                    'colaboradores' => Colaborador::with('unidade')->get()->toArray(),
                    default => [],
                };

                Log::debug("[EXPORTAÇÃO] {$tipo} - registros obtidos: " . count($dados));

                if (empty($dados)) {
                    Log::warning("[EXPORTAÇÃO] Nenhum registro encontrado para {$tipo}");
                    continue;
                }

                $this->dadosFormatados[$tipo] = $this->formatarPorTipo($tipo, $dados);
                Log::debug("[EXPORTAÇÃO] {$tipo} - dados formatados com sucesso");
            } catch (\Throwable $e) {
                Log::error("[EXPORTAÇÃO] Erro ao processar {$tipo}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                $this->msg = "Falha ao processar {$tipo}: " . $e->getMessage();
            }
        }

        if (empty($this->dadosFormatados)) {
            $this->msg = 'Não há dados para exportar.';
            Log::warning('[EXPORTAÇÃO] Nenhum dado formatado disponível.');
            return;
        }

        $exportPath = storage_path("app/public/{$this->path}");
        Log::debug("[EXPORTAÇÃO] Verificando diretório: {$exportPath}");

        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0777, true);
            Log::debug("[EXPORTAÇÃO] Diretório criado com sucesso.");
        }

        try {
            ExportarRelatorioJob::dispatch($this->dadosFormatados, 'relatorios_completos', $this->path);
            Log::debug('[EXPORTAÇÃO] Job ExportarRelatorioJob despachado com sucesso.');
        } catch (\Throwable $e) {
            Log::error('[EXPORTAÇÃO] Falha ao despachar job: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->msg = 'Erro ao iniciar exportação: ' . $e->getMessage();
            return;
        }

        $this->msg = 'Exportação iniciada. Aguarde o processamento...';
        Log::debug('[EXPORTAÇÃO] Processo de exportação iniciado com sucesso.');

        $this->dispatch('$refresh');
    }

    public function verificarExportacao()
    {
        if (!$this->pollingAtivo) return;

        $this->pollingTentativas++;

        // Interrompe após o máximo
        if ($this->pollingTentativas >= $this->pollingMaxTentativas) {
            $this->pollingAtivo = false;
            $this->msg = 'Tempo limite atingido. A exportação demorou demais ou falhou.';
            return;
        }

        // Verifica se há arquivos prontos
        $arquivos = glob(storage_path("app/public/{$this->path}/relatorios_completos_*.xlsx"));

        if (!empty($arquivos)) {
            $ultimo = collect($arquivos)->sortDesc()->first();
            $nomeArquivo = basename($ultimo);
            $this->arquivoGerado = asset("storage/{$this->path}/{$nomeArquivo}");
            $this->msg = 'Relatório pronto para download.';
            $this->exportConcluido = true;
            $this->pollingAtivo = false;
        }
    }

    public function marcarComoBaixado()
    {
        $this->msg = 'Relatório baixado com sucesso.';
        $this->exportConcluido = true;
        $this->pollingAtivo = false;
    }

    public function render()
    {
        return view('livewire.pages.relatorios-component');
    }
}
