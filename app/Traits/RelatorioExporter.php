<?php

namespace App\Traits;

trait RelatorioExporter
{
    /**
     * Retorna os dados formatados conforme o tipo de relatório
     */
    public function formatarPorTipo(string $tipo, array $dados): array
    {
        return match ($tipo) {
            'grupos' => $this->formatarGrupos($dados),
            'bandeiras' => $this->formatarBandeiras($dados),
            'unidades' => $this->formatarUnidades($dados),
            'colaboradores' => $this->formatarColaboradores($dados),
            default => [],
        };
    }

    /**
     * Formatação do relatório de Grupos Econômicos
     */
    private function formatarGrupos(array $dados): array
    {
        $out = [];
        foreach ($dados as $item) {
            $out[] = [
                'ID' => $item['id'],
                'Nome do Grupo' => $item['nome'],
                'Criado em' => $this->fmtData($item['created_at'] ?? null),
                'Atualizado em' => $this->fmtData($item['updated_at'] ?? null),
            ];
        }

        return $out;
    }

    /**
     * Formatação do relatório de Bandeiras
     */
    private function formatarBandeiras(array $dados): array
    {
        $out = [];

        foreach ($dados as $item) {

            $grupo = $item['grupo_economico'] ?? [];
            $out[] = [
                'ID' => $item['id'],
                'Nome da Bandeira' => $item['nome'],
                'Grupo Econômico' => $grupo['nome'],
                'Criado em' => $this->fmtData($item['created_at'] ?? null),
                'Atualizado em' => $this->fmtData($item['updated_at'] ?? null),
            ];
        }

        return $out;
    }

    /**
     * Formatação do relatório de Unidades
     */
    private function formatarUnidades(array $dados): array
    {
        $out = [];

        foreach ($dados as $item) {

            $bandeira = $item['bandeira'] ?? [];
            $out[] = [
                'ID' => $item['id'],
                'Nome Fantasia' => $item['nome_fantasia'],
                'Razão Social' => $item['razao_social'],
                'CNPJ' => $item['cnpj'],
                'Bandeira' => $bandeira['nome'],
                'Criado em' => $this->fmtData($item['created_at'] ?? null),
                'Atualizado em' => $this->fmtData($item['updated_at'] ?? null),
            ];
        }

        return $out;
    }

    /**
     * Formatação do relatório de Colaboradores
     */
    private function formatarColaboradores(array $dados): array
    {
        $out = [];

        foreach ($dados as $item) {
           
            $unidade = $item['unidade'];
            $out[] = [
                'ID' => $item['id'],
                'Nome do Colaborador' => $item['nome'],
                'Email' => $item['email'],
                'CPF' => $item['cpf'],
                'Unidade' => $unidade['nome_fantasia'],
                'Criado em' => $this->fmtData($item['created_at'] ?? null),
                'Atualizado em' => $this->fmtData($item['updated_at'] ?? null),
            ];
        }

        return $out;
    }

    /**
     * Formata datas para o padrão legível no Excel
     */
    private function fmtData($valor): string
    {
        try {
            return \Carbon\Carbon::parse($valor)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $valor;
        }
    }
}
