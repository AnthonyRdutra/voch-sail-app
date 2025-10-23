<?php

namespace App\Traits;

use Carbon\Carbon;

trait RelatorioFormatter
{
    private function fmt($value): ?string
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value)->format('d/m/y H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function mapGrupos(iterable $items): array
    {
        return collect($items)->map(fn($data) => [
            'id' => $data['id'],
            'Nome' => $data['nome'],
            'Data criação' => $this->fmt($data['created_at']),
            'Última atualização' => $this->fmt($data['updated_at']),
            'unformatted_data' => $data
        ])->toArray();
    }

    private function mapBandeiras(iterable $items): array
    {
        return collect($items)->map(function ($data) {
            $grupo = $data['grupo_economico'] ?? null;
            return [
                'id' => $data['id'],
                'nome' => $data['nome'],
                'grupo_economico_id' => $grupo['id'] ?? null,
                'grupo_economico_nome' => $grupo['nome'] ?? '—',
                'Data criação' => $this->fmt($data['created_at']),
                'Última atualização' => $this->fmt($data['updated_at']),
                'unformatted_data' => $data
            ];
        })->toArray();
    }

    private function mapUnidades(iterable $items): array
    {
        return collect($items)->map(function ($data) {
            $data = $data[0] ?? $data;
            $bandeira = $data['bandeira'] ?? null;
            return [
                'id' => $data['id'],
                'Nome_Fantasia' => $data['nome_fantasia'],
                'Razão_Social' => $data['razao_social'],
                'CNPJ' => $data['cnpj'],
                'bandeira_id' => $bandeira['id'] ?? null,
                'bandeira_nome' => $bandeira['nome'] ?? '—',
                'Data criação' => $this->fmt($data['created_at']),
                'Última atualização' => $this->fmt($data['updated_at']),
                'unformatted_data' => $data
            ];
        })->toArray();
    }

    private function mapColaboradores(iterable $items): array
    {
        return collect($items)->map(function ($data) {
            $data = $data[0] ?? $data;
            $unidade = $data['unidade'] ?? null;
            return [
                'id' => $data['id'],
                'nome' => $data['nome'],
                'email' => $data['email'],
                'cpf' => $data['cpf'],
                'unidade_id' => $unidade['id'] ?? null,
                'unidade_nome' => $unidade['nome_fantasia'] ?? '—',
                'Data criação' => $this->fmt($data['created_at']),
                'Última atualização' => $this->fmt($data['updated_at']),
                'unformatted_data' => $data
            ];
        })->toArray();
    }
}
