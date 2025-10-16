<div wire:key="bandeiras-card" style="padding: 16px;">
    <h3 style="margin-bottom: 10px;">Cadastro de Bandeira</h3>

    {{-- Campo nome da bandeira --}}
    <div style="margin-bottom: 10px;">
        <label for="bandeira_nome">Nome da bandeira:</label><br>
        <input id="bandeira_nome"
               type="text"
               wire:model.defer="bandeira"
               placeholder="Digite o nome da bandeira"
               style="width: 100%; padding: 6px;">
    </div>

    {{-- Dropdown grupo econômico --}}
    <div style="margin-bottom: 10px;">
        <label for="grupo_economico_id">Selecione o Grupo Econômico:</label><br>
        <select id="grupo_economico_id"
                wire:model.defer="grupo_economico_id"
                style="width: 100%; padding: 6px;">
            <option value="">-- Selecione um grupo --</option>
            @foreach($grupoEconomico as $grupo)
                <option value="{{ is_array($grupo) ? $grupo['id'] : $grupo->id }}">
                    {{ is_array($grupo) ? $grupo['nome'] : $grupo->nome }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Botão de ação --}}
    <div style="margin-bottom: 10px;">
        <button wire:click="bandeiraStore"
                style="padding: 8px 12px; cursor:pointer;">
            Salvar Bandeira
        </button>
    </div>

    {{-- Mensagem de feedback --}}
    @if($msg)
        <p style="margin-top: 10px;">{{ $msg }}</p>
    @endif
</div>