<?php

namespace App\Traits;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait LogAuditoria
{
    protected function audit(string $user, string $action, string $entity, $items = null)
    {
        $mensagem = '';

        switch (strtolower($action)) {
            case 'edit':
            case 'update':
                if (is_array($items) && isset($items['antes'], $items['depois'])) {
                    $mensagem = sprintf(
                        "%s editou de %s o valor '%s' para '%s'",
                        $user,
                        $entity,
                        $items['antes'],
                        $items['depois']
                    );
                } else {
                    $mensagem = sprintf("%s editou %s", $user, $entity);
                }
                break;
            case 'add':
            case 'create':
            case 'store':
                $mensagem = sprintf("%s adicionou %s (%s)", $user, $entity, $items);
                break;

            case 'delete':
            case 'remove':
                $mensagem = sprintf("%s removeu %s (%s)", $user, $entity, $items);
                break;

            case 'export':
                $mensagem = sprintf("%s exportou %s (%s)", $user, $entity, $items);
                break;

            default:
                $mensagem = sprintf("%s realizou ação '%s' em %s (%s)", $user, $action, $entity, $items);
                break;
        }

        try {
            Auditoria::create([
                'usuario'  => $user,
                'acao'     => $action,
                'entidade' => $entity,
                'detalhes' => $items ? json_encode($items, JSON_UNESCAPED_UNICODE) : null
            ]);
        } catch (\Throwable $e) {
            Log::error('erro ao registrar audit: '. $e->getMessage());
        }

        Log::info("[Audit] {$mensagem}");
    }
}
