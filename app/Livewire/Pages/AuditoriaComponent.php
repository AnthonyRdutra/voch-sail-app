<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Log;

class AuditoriaComponent extends Component
{
    public $logs = [];

    public function mount()
    {
        Log::debug('[Auditoria] mount()');
        $this->atualizar();
    }

    public function atualizar()
    {
        Log::debug('[Auditoria] Atualizando logs');

        try {
            $this->logs = Auditoria::orderByDesc('created_at')
                ->get()
                ->map(function ($log) {
                    $detalhes = $log->detalhes;

                    if (is_string($detalhes)) {
                        $detalhes = trim($detalhes);
                        $detalhes = preg_replace('/^"(.*)"$/', '$1', $detalhes);

                        $json = json_decode($detalhes, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $detalhes = $json;
                        }
                    }

                    return [
                        'usuario'    => $log->usuario ?? '—',
                        'acao'       => ucfirst($log->acao ?? '—'),
                        'entidade'   => $log->entidade ?? '—',
                        'detalhes'   => $detalhes ?? '—',
                        'created_at' => $log->created_at?->format('Y-m-d H:i:s') ?? '—',
                    ];
                })
                ->values()        // 🔹 garante reindexação
                ->all();
            Log::debug('Auditoria logs =>', $this->logs);          // 🔹 e converte fora do map
        } catch (\Throwable $e) {
            Log::error('[Auditoria] Erro ao carregar logs: ' . $e->getMessage());
            $this->logs = [];
        }
    }

    public function render()
    {
        Log::debug('[Auditoria] render() executado com ' . count($this->logs) . ' logs');
        return view('livewire.pages.auditoria-component');
    }
}
