<?php

namespace App\Traits;

use Carbon\Carbon;

trait FormataRelatorioTrait
{
    public function formatar(string $tipo, array $dados): array
    {
        return match (strtolower($tipo)) {
            'grupos' => $this->fmtGrupos($dados),
            'bandeiras' => $this->fmtBandeiras($dados),
            'unidades' => $this->fmtUnidades($dados),
            'colaboradores' => $this->fmtColaboradores($dados),
            default => $this->fmtGenerico($dados),
        };
    }

    private function fmtGrupos(array $dados): array
    {
        return array_map(fn($d) => [
            'ID' => $d['id'] ?? '',
            'Nome do Grupo' => $d['nome'] ?? '',
            'Criado em' => $this->data($d['created_at'] ?? null),
            'Atualizado em' => $this->data($d['updated_at'] ?? null),
        ], $dados);
    }

    private function fmtBandeiras(array $dados): array
    {
        return array_map(function ($d) {
            $g = $d['grupo_economico'] ?? [];
            return [
                'ID' => $d['id'] ?? '',
                'Nome da Bandeira' => $d['nome'] ?? '',
                'Grupo Econômico' => $g['nome'] ?? '—',
                'Criado em' => $this->data($d['created_at'] ?? null),
                'Atualizado em' => $this->data($d['updated_at'] ?? null),
            ];
        }, $dados);
    }

    private function fmtUnidades(array $dados): array
    {
        return array_map(function ($d) {
            $d = $d[0] ?? $d;
            $b = $d['bandeira'] ?? [];
            return [
                'ID' => $d['id'] ?? '',
                'Nome Fantasia' => $d['nome_fantasia'] ?? '',
                'Razão Social' => $d['razao_social'] ?? '',
                'CNPJ' => $d['cnpj'] ?? '',
                'Bandeira' => $b['nome'] ?? '—',
                'Criado em' => $this->data($d['created_at'] ?? null),
                'Atualizado em' => $this->data($d['updated_at'] ?? null),
            ];
        }, $dados);
    }

    private function fmtColaboradores(array $dados): array
    {
        return array_map(function ($d) {
            $d = $d[0] ?? $d;
            $u = $d['unidade'] ?? [];
            return [
                'ID' => $d['id'] ?? '',
                'Nome' => $d['nome'] ?? '',
                'Email' => $d['email'] ?? '',
                'CPF' => $d['cpf'] ?? '',
                'Unidade' => $u['nome_fantasia'] ?? '—',
                'Criado em' => $this->data($d['created_at'] ?? null),
                'Atualizado em' => $this->data($d['updated_at'] ?? null),
            ];
        }, $dados);
    }

    private function fmtGenerico(array $dados): array
    {
        return array_map(fn($d) => (array) $d, $dados);
    }

    private function data($valor): string
    {
        try {
            return Carbon::parse($valor)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $valor;
        }
    }
}
