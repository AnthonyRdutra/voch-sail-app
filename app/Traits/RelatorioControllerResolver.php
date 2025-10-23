<?php

namespace App\Traits;

use App\Http\Controllers\{
    GrupoEconomicoController,
    BandeiraController,
    UnidadeController,
    ColaboradorController
};
use App\Models\{
    GrupoEconomico,
    Bandeira,
    Unidade
};

trait RelatorioControllerResolver
{
    /**
     * Retorna o controller responsável conforme o tipo
     */
    private function getControllerByType(string $tipo): ?string
    {
        return match ($tipo) {
            'grupos'        => GrupoEconomicoController::class,
            'bandeiras'     => BandeiraController::class,
            'unidades'      => UnidadeController::class,
            'colaboradores' => ColaboradorController::class,
            default         => null,
        };
    }

    /**
     * Usa o tipo atual do componente (ex: $this->tipoRelatorio)
     */
    private function getController(): ?string
    {
        return $this->getControllerByType($this->tipoRelatorio ?? '');
    }

    /**
     * Retorna as opções de foreign key (dropdowns)
     */
    public function getForeignOptions(string $tipo): array
    {

        return match ($tipo) {
            'bandeiras'     => GrupoEconomico::select('id', 'nome')->get()->toArray(),
            'unidades'      => Bandeira::select('id', 'nome')->get()->toArray(),
            'colaboradores' => Unidade::select('id', 'nome_fantasia as nome')->get()->toArray(),
            default         => [],
        };
    }
}
