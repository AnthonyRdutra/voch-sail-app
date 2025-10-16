<div wire:key="unidades-card" style="padding: 16px;">
    <h3 style="margin-bottom: 10px;">Cadastro de Unidade</h3>

    {{-- Campo nome da bandeira --}}
    <div style="margin-bottom: 10px;">

        <label for="nome_fantasia">Nome da bandeira:</label><br>
        <input id="nome_fantasia"
            type="text"
            wire:model.defer="nome_fantasia"
            placeholder="Digite o nome fantasia"
            style="width: 100%; padding: 6px;">

        <label for="razao_social">Razão social:</label><br>
        <input id="razao_social"
            type="text"
            wire:model.defer="razao_social"
            placeholder="Digite a razão social"
            style="width: 100%; padding: 6px;">

        <label for="nome_fantasia">CNPJ:</label><br>
        <input id="cnpj"
            type="number"
            wire:model.defer="cnpj"
            placeholder="Digite o cnpj"
            style="width: 100%; padding: 6px;">
    </div>

    {{-- Dropdown grupo econômico --}}
    <div style="margin-bottom: 10px;">
        <label for="bandeira_id">Selecione a Bandeira:</label><br>
        <select id="bandeira_id"
            wire:model.defer="bandeira_id"
            style="width: 100%; padding: 6px;">
            <option value="">-- Selecione uma bandeira --</option>
            @foreach($bandeiras as $bandeira)
            <option value="{{ is_array($bandeira) ? $bandeira['id'] : $bandeira->id }}">
                {{ is_array($bandeira) ? $bandeira['nome'] : $bandeira->nome }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- Botão de ação --}}
    <div style="margin-bottom: 10px;">
        <button wire:click="unidadeStore"
            style="padding: 8px 12px; cursor:pointer;">
            Salvar Unidade
        </button>
    </div>

    {{-- Mensagem de feedback --}}
    @if($msg)
    <p style="margin-top: 10px;">{{ $msg }}</p>
    @endif
</div>