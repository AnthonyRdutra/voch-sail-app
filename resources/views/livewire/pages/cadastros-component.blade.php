<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;justify-items:center;">

    {{-- =================== GRUPOS =================== --}}
    <section style="background:#121623;border-radius:12px;padding:20px;width:100%;max-width:420px;color:#f3f4f6;">
        <h2 style="font-size:18px;font-weight:bold;color:#facc15;margin-bottom:20px;">
            Cadastro de Grupo Econômico
        </h2>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <input type="text" wire:model.defer="grupo_nome" placeholder="Digite o nome do grupo"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">

            <button type="button" wire:click="grupoStore" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="grupoStore">Salvar Grupo</span>
                <span wire:loading wire:target="grupoStore">Salvando...</span>
            </button>
        </div>
    </section>

    {{-- =================== BANDEIRAS =================== --}}
    <section style="background:#121623;border-radius:12px;padding:20px;width:100%;max-width:420px;color:#f3f4f6;">
        <h2 style="font-size:18px;font-weight:bold;color:#facc15;margin-bottom:20px;">
            Cadastro de Bandeira
        </h2>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <input type="text" wire:model.defer="bandeira_nome" placeholder="Digite o nome da bandeira"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">

            <label style="font-size:12px;color:#d1d5db;">Grupo Econômico</label>
            <select wire:model.defer="grupo_economico_id"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">
                <option value="">-- Selecione o Grupo Econômico --</option>
                @foreach($grupoEconomico as $grupo)
                    <option value="{{ $grupo['id'] }}">{{ $grupo['nome'] }}</option>
                @endforeach
            </select>

            <button type="button" wire:click="bandeiraStore" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="bandeiraStore">Salvar Bandeira</span>
                <span wire:loading wire:target="bandeiraStore">Salvando...</span>
            </button>
        </div>
    </section>

    {{-- =================== UNIDADES =================== --}}
    <section style="background:#121623;border-radius:12px;padding:20px;width:100%;max-width:420px;color:#f3f4f6;">
        <h2 style="font-size:18px;font-weight:bold;color:#facc15;margin-bottom:20px;">
            Cadastro de Unidade
        </h2>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <input type="text" wire:model.defer="nome_fantasia" placeholder="Nome fantasia"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">
            <input type="text" wire:model.defer="razao_social" placeholder="Razão social"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">
            <input type="text" wire:model.defer="cnpj" placeholder="CNPJ"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">

            <label style="font-size:12px;color:#d1d5db;">Bandeira</label>
            <select wire:model.defer="bandeira_id"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">
                <option value="">-- Selecione a Bandeira --</option>
                @foreach($bandeiras as $bandeira)
                    <option value="{{ $bandeira['id'] }}">{{ $bandeira['nome'] }}</option>
                @endforeach
            </select>

            <button type="button" wire:click="unidadeStore" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="unidadeStore">Salvar Unidade</span>
                <span wire:loading wire:target="unidadeStore">Salvando...</span>
            </button>
        </div>
    </section>

    {{-- =================== COLABORADORES =================== --}}
    <section style="background:#121623;border-radius:12px;padding:20px;width:100%;max-width:420px;color:#f3f4f6;">
        <h2 style="font-size:18px;font-weight:bold;color:#facc15;margin-bottom:20px;">
            Cadastro de Colaborador
        </h2>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <input type="text" wire:model.defer="colaborador_nome" placeholder="Nome completo"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">
            <input type="email" wire:model.defer="email" placeholder="Email"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">
            <input type="text" wire:model.defer="cpf" placeholder="CPF"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">

            <label style="font-size:12px;color:#d1d5db;">Unidade</label>
            <select wire:model.defer="unidade_id"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">
                <option value="">-- Selecione a Unidade --</option>
                @foreach($unidades as $unidade)
                    <option value="{{ $unidade['id'] }}">{{ $unidade['nome_fantasia'] ?? 'Sem nome' }}</option>
                @endforeach
            </select>

            <button type="button" wire:click="colaboradorStore" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="colaboradorStore">Salvar Colaborador</span>
                <span wire:loading wire:target="colaboradorStore">Salvando...</span>
            </button>
        </div>
    </section>
</div>
