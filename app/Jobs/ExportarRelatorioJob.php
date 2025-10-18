<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Services\ExportacaoService;
use Illuminate\Support\Facades\Log;

class ExportarRelatorioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $dados;
    protected string $tipo;
    protected string $path;
    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(array $dados, string $tipo, string $path = 'exports')
    {
        $this->dados = $dados;
        $this->tipo = $tipo;
        $this->path = $path;
    }

    public function handle(ExportacaoService $service): void
    {
        $exportDir = storage_path("app/public/{$this->path}");

        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }

        $filename = "{$this->tipo}_" . now()->format('Ymd_His') . ".xlsx";
        $fullPath = "{$exportDir}/{$filename}";

        Log::info('ExportarRelatorioJob: caminho final gerado', [
            'exportDir' => $exportDir,
            'fullPath' => $fullPath,
            'exists?' => file_exists($fullPath)
        ]);

        try {

            $service->gerar($this->dados, $this->tipo, $fullPath);

            Storage::disk('local')->put(
                "{$this->path}/status_{$this->tipo}.json",
                json_encode([
                    'done' => true,
                    'path' => $fullPath,
                    'filename' => $filename,
                    'timestamp' => now()->toDateTimeString(),
                    'sheets' => array_keys($this->dados)
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        } catch (\Throwable $e) {
            $this->registrarErro($e);
            $this->fail($e);
        } finally {
            unset($this->dados);
        }
    }

    private function registrarErro(\Throwable $e): void
    {
        $logPath = storage_path("app/public/{$this->path}/error_{$this->tipo}.log");

        $log = sprintf(
            "[%s] %s in %s:%d\nTrace:\n%s\n\n",
            now()->toDateTimeString(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        file_put_contents($logPath, $log, FILE_APPEND);
    }
}
