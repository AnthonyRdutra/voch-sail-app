<?php

namespace App\Livewire\Pages;

use App\Jobs\ExportarRelatorioJob;
use App\Models\Bandeira;
use App\Models\Colaborador;
use App\Models\GrupoEconomico;
use App\Models\Unidade;
use Livewire\Component;
use App\Traits\{ControllerInvoker, RelatorioFormatter, RelatorioControllerResolver, RelatorioExporter, RelatorioManager};

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
    public $exportConcluido;
    public $pollingAtivo = false;

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
            $this->msg = 'selecione ao menos uma opção para poder exportar.';
            return;
        }
        $this->exportConcluido = false;
        $this->pollingAtivo = true;
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
        }

        $exportPath = storage_path("app/public/{$this->path}");
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0777, true);
        }

        $tipoBase = 'relatorios_completos';
        ExportarRelatorioJob::dispatch($this->dadosFormatados, $tipoBase, 'exports');

        $this->msg = 'Exportação iniciada';
    }

    public function downloadUltimoExcel()
    {
        $arquivos = glob(storage_path('app/exports/public/relatorios_completos_*.xlsx'));
        if (empty($arquivos)) {
            $this->msg = 'Nenhum arquivo de relatório encontrado';
        }

        $ultimo = collect($arquivos)->sortDesc()->first();
        $nomeArquivo = basename($ultimo);

        $this->arquivoGerado = asset('storage/exports/' . $nomeArquivo);
        $this->msg = 'arquivo disponivel, clique para baixar';

        $this->exportConcluido = true;
        $this->pollingAtivo = false;
    }

    public function marcarComoBaixado()
    {
        $this->msg = 'Relatorio baixado com sucesso';
        $this->exportConcluido = true;
        $this->pollingAtivo = false;
    }

    public function render()
    {
        return view('livewire.pages.relatorios-component');
    }
}