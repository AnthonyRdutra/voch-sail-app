<div wire:key="bandeiras-card" style="padding: 16px;">
    <h3 style="margin-bottom: 10px;">Cadastro de Bandeira</h3>

    {{-- Campo nome da bandeira --}}
    <div style="margin-bottom: 10px;">
        <label for="bandeira_nome">Nome da bandeira:</label><br>
        <input id="bandeira_nome"
            type="text"
            wire:model.defer="bandeira_nome"
            placeholder="Digite o nome da bandeira"
            style="width: 100%; padding: 6px;">
    </div>

    {{-- Dropdown grupo econômico --}}
    @if($canSave && !empty($grupoEconomico) && is_iterable($grupoEconomico))
    <div style="margin-bottom: 10px;">
        <label for="grupo_economico_id">Selecione o Grupo Econômico:</label><br>
        <select id="grupo_economico_id"
            wire:model.defer="grupo_economico_id"
            style="width: 100%; padding: 6px;">
            <option value="">-- Selecione um grupo --</option>

            @foreach($grupoEconomico as $grupo)
            @php
            $id = is_array($grupo) ? ($grupo['id'] ?? 0) : ($grupo->id ?? 0);
            $nome = is_array($grupo) ? ($grupo['nome']) : ($grupo->nome);
            @endphp
            <option value="{{ $id }}">{{ $nome }}</option>
            @endforeach
        </select>
    </div>
    @elseif(!$canSave)
    <p style="color: #f87171; margin-top: 8px;">
        ⚠ É necessário cadastrar um Grupo Econômico antes de criar Bandeiras.
    </p>
    @else
    <p style="color: #f87171; margin-top: 8px;">
        ⚠ Nenhum dado disponível para listar os grupos econômicos.
    </p>
    @endif


    {{-- Botão de ação --}}
    <div style="margin-bottom: 10px;">
        <button wire:click="bandeiraStore"
            style="padding: 8px 12px; cursor:pointer;"
            @disabled(!$canSave)>
            Salvar Bandeira
        </button>
    </div>

    {{-- Mensagem de feedback --}}
    @if($msg)
    <p style="margin-top: 10px;">{{ $msg }}</p>
    @endif
</div>