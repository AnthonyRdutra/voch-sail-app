<?php

namespace App\Livewire\Pages;

use App\Jobs\ExportarRelatorioJob;
use App\Models\{Bandeira, Colaborador, GrupoEconomico, Unidade};
use Livewire\Component;
use App\Traits\{
    ControllerInvoker,
    RelatorioFormatter,
    RelatorioControllerResolver,
    RelatorioExporter,
    RelatorioManager
};

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
        $selecionados = array_keys(array_filter($this->exportar));

        if (empty($selecionados)) {
            $this->msg = 'Selecione ao menos uma opção para poder exportar.';
            return;
        }

        $this->exportConcluido = false;
        $this->pollingAtivo = true;
        $this->pollingTentativas = 0;
        $this->dadosFormatados = [];

        foreach ($selecionados as $tipo) {
            try {
                $controllerClass = $this->getControllerByType($tipo);
                if (!$controllerClass) {
                    $this->msg = 'Controller não encontrado para o tipo selecionado';
                    continue;
                }

                $dados = match ($tipo) {
                    'grupos' => GrupoEconomico::all()->toArray(),
                    'bandeiras' => Bandeira::with('grupoEconomico')->get()->toArray(),
                    'unidades' => Unidade::with('bandeira')->get()->toArray(),
                    'colaboradores' => Colaborador::with('unidade')->get()->toArray(),
                    default => []
                };

                if (empty($dados)) {
                    $this->msg = "Nenhum dado encontrado para {$tipo}";
                    continue;
                }

                $this->dadosFormatados[$tipo] = $this->formatarPorTipo($tipo, $dados);
            } catch (\Throwable $e) {
                $this->msg = "Falha ao processar {$tipo}: " . $e->getMessage();
            }
        }

        if (empty($this->dadosFormatados)) {
            $this->msg = 'Não há dados para exportar.';
            return;
        }

        // Cria diretório com permissões corretas
        $exportPath = storage_path("app/public/{$this->path}");
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0777, true);
        }

        // Dispara o job assíncrono
        ExportarRelatorioJob::dispatch($this->dadosFormatados, 'relatorios_completos', $this->path);

        $this->msg = 'Exportação iniciada. Aguarde o processamento...';
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
