<div wire:key="relatorios" style="padding: 20px; color: #f1f5f9;">
    <h2 style="margin-bottom: 15px;">Relatórios do Sistema</h2>

    {{-- ===========================
        SELETOR DE RELATÓRIO
    ============================ --}}
    <div style="margin-bottom: 15px;">
        <label style="margin-right: 10px;">Selecione o tipo de relatório:</label>
        <select wire:model.live="tipoRelatorio"
            style="padding: 6px 10px; border-radius: 6px; background: #1e293b; color: #f1f5f9; border: 1px solid #334155;">
            <option value="">Selecione...</option>
            <option value="grupos">Grupos Econômicos</option>
            <option value="bandeiras">Bandeiras</option>
            <option value="unidades">Unidades</option>
            <option value="colaboradores">Colaboradores</option>
        </select>
    </div>

    {{-- ===========================
        MENSAGEM DE STATUS
    ============================ --}}
    @if($msg)
    <p style="color: #22c55e; margin-bottom: 10px;">{{ $msg }}</p>
    @endif

    {{-- ===========================
        TABELA DE DADOS
    ============================ --}}
    @if(!empty($dados) && is_array($dados) && isset($dados[0]) && is_array($dados[0]))
    <table style="width: 100%; border-collapse: collapse; color:#f8fafc;">
        <thead>
            <tr style="background:#1e293b; color:#facc15;">
                @foreach(array_keys($dados[0]) as $coluna)
                @continue(str_ends_with($coluna, '_id'))
                <th style="padding:8px; text-align:left;">
                    {{ ucfirst(str_replace('_',' ',$coluna)) }}
                </th>
                @endforeach
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>
            @foreach($dados as $i => $linha)
            @if(is_array($linha))
            <tr x-data="{ editando: false }" x-bind:class="editando ? 'bg-slate-700' : ''">
                @foreach($linha as $coluna => $valor)
                @continue(str_ends_with($coluna, '_id'))
                <td style="vertical-align: middle; padding:6px;">
                    {{-- VISUALIZAÇÃO --}}
                    <div x-show="!editando">
                        {{ is_array($valor) ? json_encode($valor) : ($valor ?? '—') }}
                    </div>

                    {{-- EDIÇÃO --}}
                    <div x-show="editando" x-cloak>
                        @if($tipoRelatorio === 'bandeiras' && $coluna === 'grupo_economico')
                        <select wire:model.lazy="dados.{{ $i }}.grupo_economico_id" style="width:100%;">
                            <option value="">-- Selecione um grupo --</option>
                            @foreach($foreignOptions ?? [] as $op)
                            <option value="{{ $op['id'] ?? '' }}">{{ $op['nome'] ?? '—' }}</option>
                            @endforeach
                        </select>

                        @elseif($tipoRelatorio === 'unidades' && $coluna === 'bandeira')
                        <select wire:model.lazy="dados.{{ $i }}.bandeira_id" style="width:100%;">
                            <option value="">-- Selecione uma bandeira --</option>
                            @foreach($foreignOptions ?? [] as $op)
                            <option value="{{ $op['id'] ?? '' }}">{{ $op['nome'] ?? '—' }}</option>
                            @endforeach
                        </select>

                        @elseif($tipoRelatorio === 'colaboradores' && $coluna === 'unidade')
                        <select wire:model.lazy="dados.{{ $i }}.unidade_id" style="width:100%;">
                            <option value="">-- Selecione uma unidade --</option>
                            @foreach($foreignOptions ?? [] as $op)
                            <option value="{{ $op['id'] ?? '' }}">{{ $op['nome'] ?? '—' }}</option>
                            @endforeach
                        </select>

                        @elseif(!in_array($coluna, ['id','created_at','updated_at']))
                        <input type="text"
                            wire:model.defer="dados.{{ $i }}.{{ $coluna }}"
                            style="width:100%;">
                        @else
                        {{ $valor ?? '—' }}
                        @endif
                    </div>
                </td>
                @endforeach

                {{-- AÇÕES --}}
                <td style="white-space:nowrap;">
                    <div x-show="!editando">
                        <button @click="editando = true" style="color:#38bdf8;">editar</button>
                        @if(isset($linha['id']))
                        <button wire:click.prevent="delete({{ $linha['id'] }})" style="color:red;">excluir</button>
                        @endif
                    </div>
                    <div x-show="editando" x-cloak>
                        <button wire:click.prevent="saveEdit({{ $i }})" style="color:#22c55e;">salvar</button>
                        <button @click="editando = false" style="color:#f87171;">cancelar</button>
                    </div>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#94a3b8; margin-top:10px;">Nenhum dado encontrado.</p>
    @endif

    {{-- ===========================
        MODAL DE EXPORTAÇÃO
    ============================ --}}
    <div x-data="{ showModal: false }" class="relative">
        <button
            @click="showModal = true"
            class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-full shadow-lg px-5 py-3"
            style="z-index: 50;">
            <i class="fa fa-file-excel mr-1"></i> Exportar
        </button>

        <!-- Modal -->
        <div
            x-show="showModal"
            x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @keydown.escape.window="showModal = false"
            @click.self="showModal = false">

            <div class="bg-slate-800 text-slate-100 rounded-lg shadow-xl p-6 w-96">
                <h2 class="text-lg font-semibold mb-4">Exportar Relatórios</h2>

                <p class="text-sm text-slate-400 mb-3">Selecione os relatórios que deseja exportar:</p>

                <div class="flex flex-col gap-2 mb-4">
                    @foreach(['grupos'=>'Grupos Econômicos','bandeiras'=>'Bandeiras','unidades'=>'Unidades','colaboradores'=>'Colaboradores'] as $tipo => $label)
                    <label>
                        <input type="checkbox" wire:model.defer="exportar.{{ $tipo }}"> {{ $label }}
                    </label>
                    @endforeach
                </div>

                <div class="flex justify-end gap-2">
                    <button @click="showModal = false"
                        class="bg-slate-700 hover:bg-slate-600 text-sm px-3 py-2 rounded-md">Cancelar</button>
                    <button
                        wire:click="confirmarExportacao"
                        @click="showModal = false"
                        class="bg-green-500 hover:bg-green-600 text-sm px-3 py-2 rounded-md">
                        Exportar
                    </button>
                </div>

                <div wire:loading wire:target="confirmarExportacao"
                    class="text-sm text-slate-400 mt-3 text-center">
                    Gerando relatórios...
                </div>
            </div>
        </div>
    </div>
    {{-- ===========================
     STATUS E DOWNLOAD
=========================== --}}
    <div class="mt-6 text-center">

        {{-- Mensagem de status --}}
        @if($msg)
        <p class="text-sm mt-2 text-green-400">{{ $msg }}</p>
        @endif

        {{-- Link de download quando o arquivo for gerado --}}
        @if($arquivoGerado)
        <div class="mt-4">
            <a href="{{ $arquivoGerado }}"
                download
                wire:click="marcarComoBaixado"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-md transition">
                 <span>Baixar Relatório Gerado</span>
            </a>
        </div>
        @endif

        {{-- Atualiza automaticamente a cada 5 segundos enquanto ainda não estiver concluído --}}
        @if($pollingAtivo)
        <div wire:poll.5s="downloadUltimoExcel" wire:loading.remove></div>
        @endif

        {{-- Indicador enquanto gera --}}
        <div wire:loading wire:target="confirmarExportacao" class="text-slate-400 text-sm mt-3">
             Gerando relatórios, aguarde...
        </div>
    </div>
</div>