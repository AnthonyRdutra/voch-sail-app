<div
    wire:key="relatorios"
    class="p-6 bg-[#0c0f16] text-[#f3f4f6] font-[Inter] rounded-xl border border-[#1e2433] shadow-lg">
    {{-- =========================
        CABEÇALHO
    ========================== --}}
    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <label for="tipoRelatorio" class="text-sm text-[#e8c153] font-semibold">
                Tipo de Relatório:
            </label>
            <select
                id="tipoRelatorio"
                wire:model="tipoRelatorio"
                wire:change="relatorio"
                class="bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6] rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#e8c153]">
                <option value="">Selecione...</option>
                <option value="grupos">Grupos Econômicos</option>
                <option value="bandeiras">Bandeiras</option>
                <option value="unidades">Unidades</option>
                <option value="colaboradores">Colaboradores</option>
            </select>
        </div>

        <button
            wire:click="relatorio"
            class="bg-[#e8c153] hover:bg-[#f1d071] text-[#0c0f16] font-semibold px-5 py-2 rounded-md shadow-md transition">
            Atualizar Relatório
        </button>
    </div>

    {{-- =========================
        STATUS / MENSAGENS / LOADING
    ========================== --}}
    <div class="mb-4">
        @if ($msg)
        <div class="p-3 bg-[#121623] border border-[#2a3044] rounded-md text-sm text-gray-300">
            {{ $msg }}
        </div>
        @endif

        <div wire:loading wire:target="tipoRelatorio"
            class="mt-3 p-3 bg-[#121623] border border-[#2a3044] rounded-md text-sm text-[#e8c153] flex items-center gap-2">
            <svg class="animate-spin w-4 h-4 text-[#e8c153]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            Carregando dados do próximo relatório...
        </div>
    </div>

    {{-- =========================
        TABELA DE DADOS
    ========================== --}}
    <div wire:loading.remove wire:target="tipoRelatorio"
        class="overflow-x-auto border border-[#1e2433] rounded-lg shadow-inner">
        <table class="w-full text-sm text-left border-collapse text-[#f3f4f6]">
            <thead class="bg-[#121623] text-[#e8c153] uppercase text-xs tracking-wider border-b border-[#1e2433]">
                <tr>
                    @foreach (array_keys($dados[0] ?? []) as $coluna)
                    <th class="px-4 py-3 text-center">{{ $coluna }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-center">Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($dados as $index => $linha)
                @php
                // Normaliza as chaves e remove arrays aninhados
                $linha = collect($linha)
                ->map(function ($v) {
                if (is_array($v)) {
                return json_encode($v, JSON_UNESCAPED_UNICODE);
                }
                return $v;
                })
                ->toArray();
                $linha = array_change_key_case($linha, CASE_UPPER);
                @endphp

                <tr class="border-b border-[#1e2433] hover:bg-[#151822] transition-colors"
                    x-data="{ editando: false }">

                    {{-- COLUNAS --}}
                    @foreach ($linha as $campo => $valor)
                    @php
                    $campoBase = str_replace('_ID', '', $campo);
                    $temFK = array_key_exists($campoBase . '_ID', $linha);
                    @endphp

                    {{-- OCULTA NOME DA FK QUANDO EDITANDO --}}
                    @if ($temFK && $campo === $campoBase)
                    <td class="px-4 py-2 text-center text-gray-300" x-show="!editando">
                        {{ $valor }}
                    </td>
                    @continue
                    @endif

                    <td class="px-4 py-2 text-center text-gray-300">
                        {{-- VISUAL --}}
                        <div x-show="!editando">
                            {{ $valor }}
                        </div>

                        {{-- EDIÇÃO --}}
                        <div x-show="editando" x-cloak>
                            @if (str_ends_with(strtolower($campo), '_id') && !empty($foreignOptions))
                            <select wire:model.defer="dados.{{ $index }}.{{ $campo }}"
                                class="bg-[#0c0f16] border border-[#2a3044] rounded-md text-[#f3f4f6] px-2 py-1 w-full">
                                <option value="">-- Selecione --</option>
                                @foreach ($foreignOptions as $opt)
                                <option value="{{ $opt['id'] }}">{{ $opt['nome'] }}</option>
                                @endforeach
                            </select>
                            @elseif (strtolower($campo) === 'id')
                            <span class="text-gray-500 text-xs">{{ $valor }}</span>
                            @elseif (!in_array($campo, ['DATA_CRIACAO', 'ULTIMA_ATUALIZACAO']))
                            <input type="text"
                                wire:model.defer="dados.{{ $index }}.{{ $campo }}"
                                class="bg-[#0c0f16] border border-[#2a3044] rounded-md px-2 py-1 w-full text-[#f3f4f6]" />
                            @else
                            <span class="text-gray-500 text-xs">{{ $valor }}</span>
                            @endif
                        </div>
                    </td>
                    @endforeach

                    {{-- AÇÕES --}}
                    <td class="px-4 py-2 text-center whitespace-nowrap">
                        <template x-if="!editando">
                            <div>
                                <button @click="editando = true"
                                    class="text-sky-400 hover:text-sky-300 mx-1 font-medium">
                                    Editar
                                </button>
                                <button wire:click="delete({{ $linha['ID'] ?? 0 }})"
                                    class="text-red-400 hover:text-red-300 mx-1 font-medium">
                                    Excluir
                                </button>
                            </div>
                        </template>

                        <template x-if="editando">
                            <div>
                                <button wire:click="saveEdit({{ $linha['ID'] ?? 0 }}); editando=false"
                                    class="text-green-400 hover:text-green-300 mx-1 font-medium">
                                    Salvar
                                </button>
                                <button @click="editando=false"
                                    class="text-gray-400 hover:text-gray-300 mx-1 font-medium">
                                    Cancelar
                                </button>
                            </div>
                        </template>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="999" class="text-center py-6 text-gray-400">
                        Nenhum dado encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- INDICADOR DE SALVAMENTO --}}
    <div wire:loading wire:target="saveEdit"
        class="mt-4 p-3 bg-[#121623] border border-[#2a3044] rounded-md text-sm text-[#e8c153] flex items-center gap-2">
        <svg class="animate-spin w-4 h-4 text-[#e8c153]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        Salvando alterações...
    </div>


    {{-- INDICADOR DE SALVAMENTO --}}
    <div wire:loading wire:target="saveEdit"
        class="mt-4 p-3 bg-[#121623] border border-[#2a3044] rounded-md text-sm text-[#e8c153] flex items-center gap-2">
        <svg class="animate-spin w-4 h-4 text-[#e8c153]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        Salvando alterações...
    </div>

    {{-- =========================
        EXPORTAÇÃO (ABAIXO DA TABELA)
    ========================== --}}
    <div class="border-t border-[#2a3044] pt-6 mt-6">
        <h3 class="text-[#e8c153] font-semibold mb-4">Exportar Relatórios</h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="exportar.grupos" class="accent-[#e8c153]">
                Grupos Econômicos
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="exportar.bandeiras" class="accent-[#e8c153]">
                Bandeiras
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="exportar.unidades" class="accent-[#e8c153]">
                Unidades
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="exportar.colaboradores" class="accent-[#e8c153]">
                Colaboradores
            </label>
        </div>

        <button
            wire:click="confirmarExportacao"
            wire:loading.attr="disabled"
            class="bg-[#e8c153] hover:bg-[#f1d071] text-[#0c0f16] font-semibold px-5 py-2 rounded-md shadow-md transition">
            Exportar Excel
        </button>

        @if ($msg)
        <div class="p-3 bg-[#121623] border border-[#2a3044] rounded-md text-sm text-gray-300 mt-4">
            {{ $msg }}
        </div>
        @endif

        @if ($pollingAtivo)
        <div wire:poll.10s="verificarExportacao"
            class="mt-3 p-3 bg-[#121623] border border-[#2a3044]
                        rounded-md text-sm text-[#e8c153]">
            Verificando exportação...
        </div>
        @endif

        @if ($exportConcluido && $arquivoGerado)
        <div class="mt-3 text-sm text-[#e8c153]">
            ✅ Exportação concluída!
            <a href="{{ $arquivoGerado }}" target="_blank" class="underline text-sky-400">
                Baixar Excel
            </a>
        </div>
        @endif
    </div>
</div>