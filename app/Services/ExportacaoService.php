<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Traits\RelatorioExporter;

class ExportacaoService
{
    use RelatorioExporter;

    public function gerar(array $dados, string $tipo, string $path)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($this->prepareSheets($dados, $tipo) as $sheetName => $content) {
            $nomeAba = $this->sanitizeSheetName($sheetName);
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($nomeAba);

            $this->preencherAba($sheet, $content);
        }

        $exportDir = dirname($path);
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $path;
    }

    private function preencherAba($sheet, array $linhas)
    {
        if (empty($linhas)) {
            $sheet->setCellValue('A1', 'sem dados disponiveis');
        }

        $headers = array_keys($linhas[0]);

        foreach ($headers as $i => $col) {
            $colLetter = Coordinate::stringFromColumnIndex($i + 1);
            $cell = "{$colLetter}1";
            $sheet->setCellValue($cell, $col);

            // estilo cabeçalho
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
            $sheet->getStyle($cell)->getAlignment()->setVertical('center');
        }

        foreach ($linhas as $r => $linha) {
            foreach ($headers as $i => $col) {
                $colLetter = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue("{$colLetter}" . ($r + 2), $linha[$col] ?? '');
            }
        }

        foreach (range(1, count($headers)) as $i) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
        }
    }

    private function prepareSheets(array $dados, string $tipo)
    {
        return $this->isMultisheet($dados) ? $dados : [$tipo => $dados];
    }

    private function isMultisheet(array $dados)
    {
        return !empty($dados) && is_string(array_key_first($dados));
    }

    private function sanitizeSheetName(string $name)
    {
        $name = preg_replace('/[\[\]\*\/\\\?\:]/', '', $name);
        $name = substr($name, 0, 31);
        return ucfirst($name ?: 'Relatorio');
    }
}
