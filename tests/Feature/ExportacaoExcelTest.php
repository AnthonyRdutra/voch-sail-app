<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Bandeira, Unidade, Colaborador, GrupoEconomico};
use App\Services\ExportacaoService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExportacaoExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_exportacao_excel_multi_sheet()
    {
        Storage::fake('local');
        $exportPath = storage_path('app/test_exports');

        if (!is_dir($exportPath)) mkdir($exportPath, 0777, true);

        $grupo = GrupoEconomico::factory()->create(['nome' => 'Grupo Teste']);
        $bandeira = Bandeira::factory()->create([
            'nome' => 'Bandeira teste',
            'grupo_economico_id' => $grupo->id
        ]);
        $unidade = Unidade::factory()->create([
            'nome_fantasia' => 'Unidade XPTO',
            'razao_social'  => 'LTDA',
            'cnpj' => '61806495000135',
            'bandeira_id' => $bandeira->id
        ]);
        $colaborador = Colaborador::factory()->create([
            'nome' => 'Colteste',
            'email' => 'testeemail@email.com',
            'cpf' => '62543400036',
            'unidade_id' => $unidade->id
        ]);

        $dadosFormatados = [
            'grupos' => [
                [
                    'ID' => $grupo->id,
                    'Nome do Grupo' => $grupo->nome,
                    'Criado em' => now()->format('d/m/Y H:i'),
                    'Atualizado em' => now()->format('d/m/Y H:i'),
                ],
            ],
            'bandeiras' => [
                [
                    'ID' => $bandeira->id,
                    'Nome da Bandeira' => $bandeira->nome,
                    'Grupo Econômico' => $grupo->nome,
                    'Criado em' => now()->format('d/m/Y H:i'),
                    'Atualizado em' => now()->format('d/m/Y H:i'),
                ],
            ],
            'unidades' => [
                [
                    'ID' => $unidade->id,
                    'Nome Fantasia' => $unidade->nome_fantasia,
                    'Razão Social' => $unidade->razao_social ?? '—',
                    'CNPJ' => $unidade->cnpj ?? '—',
                    'Bandeira' => $bandeira->nome,
                    'Criado em' => now()->format('d/m/Y H:i'),
                    'Atualizado em' => now()->format('d/m/Y H:i'),
                ],
            ],
            'colaboradores' => [
                [
                    'ID' => $colaborador->id,
                    'Nome do Colaborador' => $colaborador->nome,
                    'Email' => $colaborador->email ?? '—',
                    'CPF' => $colaborador->cpf ?? '—',
                    'Unidade' => $unidade->nome_fantasia,
                    'Criado em' => now()->format('d/m/Y H:i'),
                    'Atualizado em' => now()->format('d/m/Y H:i'),
                ],
            ],
        ];


        dispatch_sync(new \App\Jobs\ExportarRelatorioJob($dadosFormatados, 'relatorios_completos', 'test_exports'));

        $arquivo = glob("{$exportPath}/relatorios_completos_*.xlsx");
        $this->assertNotEmpty($arquivo, 'Nenhum arquivo Excel foi gerado.');
        $arquivoGerado = $arquivo[0];

        $this->assertFileExists($arquivoGerado, 'arquivo Excel nao foi criado');
        $this->assertGreaterThan(1024, filesize($arquivoGerado), 'Excel está vazio');

        $spreadsheet = IOFactory::load($arquivoGerado);
        $sheets = $spreadsheet->getSheetNames();

        $abasEsperadas = ['Grupos', 'Bandeiras', 'Unidades', 'Colaboradores'];
        foreach ($abasEsperadas as $aba) {
            $this->assertTrue(in_array($aba, $sheets, true), "Ana {$aba} nao foi encontrada");
        }

        $firstSheet = $spreadsheet->getSheetByName('Grupos');
        $cellValue = $firstSheet->getCell('A2')->getValue();
        $this->assertNotEmpty($cellValue, 'Aba Grupos está vazia');

        echo 'arquivo excel multi-sheet criado com sucesso';
    }
}
