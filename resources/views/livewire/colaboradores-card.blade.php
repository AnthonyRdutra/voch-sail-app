<div wire:key="unidades-card" style="padding: 16px;">
    <h3 style="margin-bottom: 10px;">Cadastro de Colaborador</h3>

    {{-- Campo nome da bandeira --}}
    <div style="margin-bottom: 10px;">
        <label for="colaborador_nome">Nome:</label><br>
        <input id="colaborador_nome"
            type="text"
            wire:model.defer="colaborador_nome"
            placeholder="Digite o nome fantasia"
            style="width: 100%; padding: 6px;">

        <label for="email">Email:</label><br>
        <input id="email"
            type="text"
            wire:model.defer="email"
            placeholder="Digite o email"
            style="width: 100%; padding: 6px;">

        <label for="cpf">CPF:</label><br>
        <input id="cpf"
            type="number"
            wire:model.defer="cpf"
            placeholder="Digite o cpf"
            style="width: 100%; padding: 6px;">
    </div>

    {{-- Dropdown grupo econômico --}}
    <div style="margin-bottom: 10px;">
        <label for="unidade_id">Selecione a unidade:</label><br>
        <select wire:model.defer="unidade_id" style="width:100%; padding:6px;">
            <option value="">-- Selecione uma unidade --</option>
            @if(!empty($unidades) && is_iterable($unidades))
            @foreach($unidades as $unidade)
            <option value="{{ is_array($unidade) ? $unidade['id'] : $unidade->id }}">
                {{ is_array($unidade) ? ($unidade['nome_fantasia'] ?? '') : ($unidade->nome_fantasia ?? '') }}
            </option>
            @endforeach
            @else
            <option value="">Nenhuma unidade encontrada</option>
            @endif
        </select>
    </div>

    {{-- Botão de ação --}}
    <div style="margin-bottom: 10px;">
        <button wire:click="colaboradorStore"
            style="padding: 8px 12px; cursor:pointer;">
            Salvar Unidade
        </button>
    </div>

    {{-- Mensagem de feedback --}}
    @if($msg)
    <p style="margin-top: 10px;">{{ $msg }}</p>
    @endif
</div>