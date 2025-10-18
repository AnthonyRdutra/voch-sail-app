<div wire:key="grupos-card">
    <h3>Cadastro Grupo econômico</h3>

    <div>
        <input type="text" wire:model.defer="grupo_nome" placeholder="Nome do grupo">
        <button wire:click="grupoStore">Salvar</button>
    </div>

    @if($msg)
        <p>{{$msg}}</p>
    @endif
</div>