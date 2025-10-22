<div
    class="p-6 bg-[#0c0f16] text-[#f3f4f6] font-[Inter] not-italic rounded-xl border border-[#1e2433] shadow-lg"
    style="font-style: normal !important;"
    wire:poll.20s="atualizar">
    <style>
        [wire\\:key="auditoria"],
        [wire\\:key="auditoria"] * {
            font-style: normal !important;
        }
    </style>

    {{-- CABEÇALHO --}}
    <div class="flex items-center justify-between mb-6 border-b border-[#1e2433] pb-3">
        <h2 class="text-lg font-semibold text-[#e8c153] flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-[#e8c153]" style="font-style: normal !important;"></i>
            Histórico de Auditoria
        </h2>
        <span class="text-xs text-gray-400">Atualiza automaticamente a cada 20s</span>
    </div>

    {{-- BLOCO ALPINE --}}
    <div
        x-data="auditoriaFront(@js($logs))"
        x-init="init()"
        class="space-y-5">
        {{-- FILTROS --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <input x-model="busca" type="text" placeholder="Buscar..."
                class="bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6] placeholder-[#9ca3af]
                       rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#e8c153]
                       flex-1 sm:flex-none w-full sm:w-auto">

            <div class="flex items-center gap-3 flex-wrap justify-end">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-[#e8c153] font-semibold">Ordenar:</label>
                    <button @click="toggleDirecao()"
                        class="px-4 py-2 rounded-md bg-[#121623] border border-[#2a3044]
                               text-[#f3f4f6] hover:text-[#e8c153] transition">
                        <span x-text="ordemDirecao === 'asc' ? '↑' : '↓'"></span>
                    </button>
                </div>

                {{-- BOTÃO ATUALIZAR --}}
                <button
                    @click="$wire.atualizar()"
                    class="px-4 py-2 rounded-md bg-[#e8c153] text-[#0c0f16] font-semibold
                           hover:bg-[#f1d372] active:scale-95 transition flex items-center gap-2">
                    <i class="fa-solid fa-rotate-right animate-spin-once" wire:loading wire:target="atualizar"></i>
                    <span wire:loading.remove wire:target="atualizar">Atualizar</span>
                    <span wire:loading wire:target="atualizar">Carregando...</span>
                </button>
            </div>
        </div>

        {{-- TABELA --}}
        <div class="overflow-x-auto border border-[#1e2433] rounded-lg shadow-inner">
            <table class="w-full text-sm text-left border-collapse text-[#f3f4f6]">
                <thead class="bg-[#121623] text-[#e8c153] uppercase text-xs tracking-wider border-b border-[#1e2433]">
                    <tr>
                        <th class="px-4 py-3 text-center">Usuário</th>
                        <th class="px-4 py-3 text-center">Ação</th>
                        <th class="px-4 py-3 text-center">Entidade</th>
                        <th class="px-4 py-3 text-center">Detalhes</th>
                        <th class="px-4 py-3 text-center">Data</th>
                    </tr>
                </thead>

                <tbody>
                    <template x-if="filtrados.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-400">
                                Nenhum registro encontrado.
                            </td>
                        </tr>
                    </template>

                    <template x-for="(item, i) in filtrados" :key="i">
                        <tr class="border-b border-[#1e2433] hover:bg-[#151822] transition-colors">
                            <td class="px-4 py-2 text-center text-gray-300" x-text="item.usuario"></td>
                            <td class="px-4 py-2 text-center text-[#e8c153] font-medium" x-text="item.acao"></td>
                            <td class="px-4 py-2 text-center" x-text="item.entidade"></td>
                            <td class="px-4 py-2 text-center text-xs leading-5" x-html="formatarDetalhes(item.detalhes)"></td>
                            <td class="px-4 py-2 text-center text-gray-400" x-text="formatarData(item.created_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ==============================
    SCRIPT FRONTEND AUDITORIA
============================== --}}
<script>
    function auditoriaFront(initialLogs) {
        return {
            logs: initialLogs ?? [],
            busca: '',
            ordemDirecao: 'desc',

            init() {
                // Nenhum watcher necessário, Alpine é reiniciado a cada atualização Livewire
            },

            get filtrados() {
                let lista = this.logs ?? [];
                const termo = this.busca.toLowerCase();

                if (termo) {
                    lista = lista.filter(i =>
                        Object.values(i).some(v =>
                            String(v ?? '').toLowerCase().includes(termo)
                        )
                    );
                }

                return [...lista].sort((a, b) => {
                    const av = (a.created_at || '').toLowerCase();
                    const bv = (b.created_at || '').toLowerCase();
                    if (av < bv) return this.ordemDirecao === 'asc' ? -1 : 1;
                    if (av > bv) return this.ordemDirecao === 'asc' ? 1 : -1;
                    return 0;
                });
            },

            toggleDirecao() {
                this.ordemDirecao = this.ordemDirecao === 'asc' ? 'desc' : 'asc';
            },

            formatarDetalhes(valor) {
                if (!valor) return '—';

                if (typeof valor === 'string') {
                    let texto = valor.trim();
                    if (texto.startsWith('"') && texto.endsWith('"')) {
                        texto = texto.slice(1, -1);
                    }

                    try {
                        const parsed = JSON.parse(texto);
                        if (typeof parsed === 'object' && parsed !== null) {
                            return Object.entries(parsed)
                                .map(([k, v]) => `<span class='font-semibold text-[#e8c153]'>${k}</span>: ${v}`)
                                .join('<br>');
                        }
                    } catch (_) {}

                    return texto;
                }

                if (typeof valor === 'object') {
                    return Object.entries(valor)
                        .map(([k, v]) => `<span class='font-semibold text-[#e8c153]'>${k}</span>: ${v}`)
                        .join('<br>');
                }

                return String(valor);
            },

            formatarData(str) {
                if (!str) return '—';
                const d = new Date(str);
                return d.toLocaleString('pt-BR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        };
    }
</script>