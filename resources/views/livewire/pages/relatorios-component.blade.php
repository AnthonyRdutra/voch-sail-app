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
            wire:loading.attr="disabled"
            class="bg-[#e8c153] hover:bg-[#f1d071] text-[#0c0f16] font-semibold
           px-5 py-2 rounded-md shadow-md flex items-center gap-2 transition">

            {{-- Estado normal --}}
            <span wire:loading.remove wire:target="relatorio" class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 4v5h.582a8 8 0 0115.418 0H20V4m-9 16a8 8 0 01-8-8H3l3 3-3 3h2a8 8 0 008 8z" />
                </svg>
                <span>Atualizar Relatório</span>
            </span>

            {{-- Estado carregando --}}
            <span wire:loading.flex wire:target="relatorio" class="items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-[#0c0f16]" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span>Atualizando...</span>
            </span>
        </button>
    </div>

    {{-- =========================
    BLOCO DE EDIÇÃO INLINE
========================= --}}
    @if ($modoEdicao)
    <div class="mb-6 rounded-xl border border-[#2a3044] bg-[#0f1422] text-gray-200 shadow-lg overflow-hidden transition-all duration-300 ease-out p-6">

        {{-- Cabeçalho --}}
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-[#e8c153] flex items-center gap-2">
                ✏️ Editar {{ ucfirst($tipoRelatorio) }}
            </h2>

            <button
                wire:click="fecharEdicao"
                wire:loading.attr="disabled"
                class="px-3 py-1 rounded-md bg-gray-600 hover:bg-gray-500 text-white text-sm flex items-center gap-2 transition">

                {{-- Texto padrão --}}
                <span wire:loading.remove wire:target="fecharEdicao">Fechar</span>

                {{-- Spinner durante o fechamento --}}
                <span wire:loading.flex wire:target="fecharEdicao" class="items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>Encerrando...</span>
                </span>
            </button>
        </div>

        @php
        $mapaCampos = [
        'grupos' => ['nome'],
        'bandeiras' => ['nome', 'grupo_economico_id'],
        'unidades' => ['nome_fantasia', 'razao_social', 'cnpj', 'bandeira_id'],
        'colaboradores' => ['nome', 'email', 'cpf', 'unidade_id'],
        ];
        $permitidos = $mapaCampos[$tipoRelatorio] ?? [];

        $fk = match ($tipoRelatorio) {
        'bandeiras' => 'grupo_economico_id',
        'unidades' => 'bandeira_id',
        'colaboradores' => 'unidade_id',
        default => null,
        };
        @endphp

        {{-- Campos editáveis --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            @foreach ($permitidos as $campo)
            @php
            $valorOriginal = $dadosEditaveis[$campo] ?? '';
            @endphp

            {{-- Campo de relação (dropdown) --}}
            @if ($fk && $campo === $fk)
            <div class="flex flex-col">
                <label class="text-sm font-medium text-[#e8c153] mb-1">
                    {{ ucfirst(str_replace('_', ' ', $campo)) }}
                </label>
                <select wire:model.defer="selectedData.{{ $campo }}"
                    class="bg-[#1a1f2d] border border-[#2a3044] rounded-md px-3 py-2 text-gray-100 transition outline-none">
                    <option value="">Selecione...</option>
                    @foreach ($foreignOptions as $item)
                    @php
                    if ($tipoRelatorio === 'colaboradores'){
                    $fkName = 'nome_fantasia';
                    }else{
                    $fkName = 'nome';
                    }
                    $val = $item['id'] ?? null;
                    $label = $item[$fkName] ?? 'não foi possivel capturar dado';
                    @endphp
                    @if(!is_null($val))
                    <option value="{{ $val }}" @selected($val==$valorOriginal)>
                        {{ $label }}
                    </option>
                    @endif
                    @endforeach

                    @if (empty($foreignOptions))
                    <option disabled>Sem opções disponíveis</option>
                    @endif
                </select>
            </div>

            {{-- Campo de texto --}}
            @else
            <div class="flex flex-col">
                <label class="text-sm font-medium text-[#e8c153] mb-1">
                    {{ ucfirst(str_replace('_', ' ', $campo)) }}
                </label>
                <input
                    type="text"
                    value="{{ $valorOriginal }}"
                    wire:model.defer="editData.{{ $campo }}"
                    placeholder="Digite o valor..."
                    class="bg-[#1a1f2d] border border-[#2a3044] rounded-md px-3 py-2
                               focus:ring-2 focus:ring-[#e8c153]/70 focus:border-[#e8c153]/40
                               text-gray-100 placeholder-gray-500 outline-none transition">
            </div>
            @endif
            @endforeach
        </div>

        <div class="flex justify-end mt-5">
            <div class="flex justify-end mt-5 gap-3">

                {{-- Botão Excluir --}}
                <button
                    wire:click="deleteRegistro({{ $editIndex }})"
                    wire:loading.attr="disabled"
                    class="bg-red-600 hover:bg-red-500 text-white font-semibold
               px-4 py-1.5 rounded-md shadow-sm flex items-center gap-1.5
               text-sm transition">

                    {{-- Estado normal --}}
                    <span wire:loading.remove wire:target="deleteRegistro" class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Excluir</span>
                    </span>

                    {{-- Estado carregando --}}
                    <span wire:loading.flex wire:target="deleteRegistro" class="items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>Excluindo...</span>
                    </span>
                </button>

                {{-- Botão Salvar --}}
                <button
                    wire:click="saveEdit({{ $editIndex }})"
                    wire:loading.attr="disabled"
                    class="bg-[#e8c153] hover:bg-[#f3d173] text-[#0c0f16] font-semibold
               px-4 py-1.5 rounded-md shadow-sm flex items-center gap-1.5
               text-sm transition">

                    {{-- Estado normal --}}
                    <span wire:loading.remove wire:target="saveEdit" class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Salvar</span>
                    </span>

                    {{-- Estado carregando --}}
                    <span wire:loading.flex wire:target="saveEdit" class="items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-[#0c0f16]" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>Salvando...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- =========================
        TABELA DE DADOS
    ========================== --}}
    <div class="p-6 text-gray-100">
        <h2 class="text-xl font-semibold text-yellow-400 mb-4">
            Visualização de Relatório Simplificada
        </h2>

        @if (!empty($this->dados))
        <div class="overflow-x-auto rounded-xl border border-gray-700 shadow-md">
            <table class="min-w-full text-sm border-collapse">
                <thead class="bg-[#1a2030] text-yellow-400">
                    <tr>
                        @if ($this->tipoRelatorio != 'colaboradores')
                        <th class="px-4 py-2 text-left border-b border-gray-700 uppercase">Nome</th>
                        @else
                        <th class="px-4 py-2 text-left border-b border-gray-700 uppercase">Nome Fantasia</th>
                        @endif

                        @if ($this->tipoRelatorio === 'unidades')
                        <th class="px-4 py-2 text-left border-b border-gray-700 uppercase">Razão Social</th>
                        <th class="px-4 py-2 text-left border-b border-gray-700 uppercase">CNPJ</th>
                        @endif
                        @if ($this->tipoRelatorio === 'colaboradores')
                        <th class="px-4 py-2 text-left border-b border-gray-700 uppercase">Email</th>
                        <th class="px-4 py-2 text-left border-b border-gray-700 uppercase">CPF</th>
                        @endif

                        @if ($this->tipoRelatorio != 'grupos')
                        <th class="px-4 py-2 text-left border-b border-gray-700 uppercase">Relação</th>
                        @endif

                        <th class="px-4 py-2 text-left border-b border-gray-700 uppercase">Data Criação</th>
                        <th class="px-4 py-2 text-left border-b border-gray-700 uppercase">Última Atualização</th>
                        <th class="px-4 py-2 text-center border-b border-gray-700 uppercase">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($this->dados as $index => $linha)
                    @php
                    $dataCriacao = $linha['Data criação'] ?? $linha['created_at'] ?? 'dado não capturado';
                    $dataAtualizacao = $linha['Última atualização'] ?? $linha['updated_at'] ?? 'dado não capturado';
                    @endphp

                    <tr class="border-b border-gray-800 hover:bg-[#222b3d] transition">
                        <td class="px-4 py-2 text-gray-200 font-medium">
                            {{ $linha['Nome_Fantasia'] ?? $linha['nome'] ?? $linha['Nome'] ?? 'dado não capturado' }}
                        </td>


                        {{-- CAMPOS ESPECÍFICOS DE UNIDADES --}}
                        @if ($this->tipoRelatorio === 'unidades')
                        <td class="px-4 py-2 text-gray-300">{{ $linha['razao_social'] ??  $linha['Razão_Social'] ?? 'dado não capturado' }}</td>
                        <td class="px-4 py-2 text-gray-300">{{ $linha['cnpj'] ?? $linha['CNPJ'] ?? 'dado não capturado' }}</td>
                        @endif

                        {{-- CAMPOS ESPECÍFICOS DE COLABORADORES --}}
                        @if ($this->tipoRelatorio === 'colaboradores')
                        <td class="px-4 py-2 text-gray-300">{{ $linha['email'] ?? $linha['Email'] ?? $linha['e-mail'] ??'dado não capturado' }}</td>
                        <td class="px-4 py-2 text-gray-300">{{ $linha['cpf'] ?? $linha['cpf'] ?? 'dado não capturado' }}</td>
                        @endif


                        {{-- RELAÇÃO --}}
                        @if ($this->tipoRelatorio != 'grupos')
                        <td class="px-4 py-2 text-gray-300">
                            {{ $linha['unidade_nome'] ?? $linha['bandeira_nome'] ?? $linha['grupo_economico_nome'] ?? 'dado não capturado' }}
                        </td>
                        @endif

                        {{-- CAMPOS GERAIS --}}
                        <td class="px-4 py-2 text-gray-400">{{ $dataCriacao }}</td>
                        <td class="px-4 py-2 text-gray-400">{{ $dataAtualizacao }}</td>

                        <td class="px-4 py-2 text-center">
                            <button
                                wire:click="abrirEdicao({{ $index }})"
                                wire:loading.attr="disabled"
                                wire:target="abrirEdicao({{ $index }})"
                                class="relative px-3 py-1 rounded bg-yellow-500 hover:bg-yellow-400 text-black font-semibold transition">
                                {{-- Ícone normal --}}
                                <span wire:loading.remove wire:target="abrirEdicao({{ $index }})">
                                    ✏️ Editar
                                </span>

                                {{-- Spinner de carregamento --}}
                                <span wire:loading wire:target="abrirEdicao({{ $index }})">
                                    <svg class="animate-spin h-4 w-4 mx-auto text-black" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </span>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-400 italic">Nenhum dado disponível.</p>
        @endif
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