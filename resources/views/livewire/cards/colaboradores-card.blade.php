<div wire:key="colaboradores-card" style="padding: 16px;">
    <h3 style="margin-bottom: 10px;">Cadastro de Colaborador</h3>

    {{-- Campos principais --}}
    <div style="margin-bottom: 10px;">
        <label for="colaborador_nome">Nome:</label><br>
        <input id="colaborador_nome"
               type="text"
               wire:model.defer="colaborador_nome"
               placeholder="Digite o nome"
               style="width: 100%; padding: 6px;">

        <label for="email">Email:</label><br>
        <input id="email"
               type="email"
               wire:model.defer="email"
               placeholder="Digite o email"
               style="width: 100%; padding: 6px;">

        <label for="cpf">CPF:</label><br>
        <input id="cpf"
               type="text"
               wire:model.defer="cpf"
               placeholder="Digite o CPF"
               style="width: 100%; padding: 6px;">
    </div>

    {{-- Dropdown Unidade --}}
    @if($canSave)
        <div style="margin-bottom: 10px;">
            <label for="unidade_id">Selecione a Unidade:</label><br>
            <select id="unidade_id"
                    wire:model.defer="unidade_id"
                    style="width: 100%; padding: 6px;">
                <option value="">-- Selecione uma unidade --</option>
                @foreach($unidades as $unidade)
                    <option value="{{ $unidade['id'] }}">{{ $unidade['nome_fantasia'] ?? 'Sem nome' }}</option>
                @endforeach
            </select>
        </div>
    @else
        <p style="color: #f87171; margin-top: 8px;">
            ⚠ É necessário cadastrar uma Unidade antes de criar um Colaborador.
        </p>
    @endif

    {{-- Botão de ação --}}
    <div style="margin-bottom: 10px;">
        <button wire:click="colaboradorStore"
                style="padding: 8px 12px; cursor:pointer;"
                @disabled(!$canSave)>
            Salvar Colaborador
        </button>
    </div>

    {{-- Mensagem de feedback --}}
    @if($msg)
        <p style="margin-top: 10px;">{{ $msg }}</p>
    @endif
</div>
