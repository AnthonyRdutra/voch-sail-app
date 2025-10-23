<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;justify-items:center;">

    {{-- =================== GRUPOS =================== --}}
    <section style="background:#121623;border-radius:12px;padding:20px;width:100%;max-width:420px;color:#f3f4f6;">
        <h2 style="font-size:18px;font-weight:bold;color:#facc15;margin-bottom:20px;">
            Cadastro de Grupo Econômico
        </h2>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <input type="text" wire:model.defer="grupo_nome" placeholder="Digite o nome do grupo"
                style="padding:8px;border:1px solid #2a3248;border-radius:6px;background:#1a2030;color:#f3f4f6;">

            <button
                type="button"
                wire:click="grupoStore"
                wire:loading.attr="disabled"
                class="bg-[#e8c153] hover:bg-[#f3d173] text-[#0c0f16] font-semibold
           px-4 py-1.5 rounded-md shadow-sm flex items-center gap-1.5
           text-sm transition disabled:opacity-70 disabled:cursor-not-allowed">

                {{-- Estado normal --}}
                <span wire:loading.remove wire:target="grupoStore" class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Salvar Grupo</span>
                </span>

                {{-- Estado carregando --}}
                <span wire:loading.flex wire:target="grupoStore" class="items-center gap-2">
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

            <button
                type="button"
                wire:click="bandeiraStore"
                wire:loading.attr="disabled"
                class="bg-[#e8c153] hover:bg-[#f3d173] text-[#0c0f16] font-semibold
           px-4 py-1.5 rounded-md shadow-sm flex items-center gap-1.5
           text-sm transition disabled:opacity-70 disabled:cursor-not-allowed">

                {{-- Estado normal --}}
                <span wire:loading.remove wire:target="bandeiraStore" class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Salvar Bandeira</span>
                </span>

                {{-- Estado carregando --}}
                <span wire:loading.flex wire:target="bandeiraStore" class="items-center gap-2">
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

            <button
                type="button"
                wire:click="unidadeStore"
                wire:loading.attr="disabled"
                class="bg-[#e8c153] hover:bg-[#f3d173] text-[#0c0f16] font-semibold
           px-4 py-1.5 rounded-md shadow-sm flex items-center gap-1.5
           text-sm transition disabled:opacity-70 disabled:cursor-not-allowed">

                {{-- Estado normal --}}
                <span wire:loading.remove wire:target="unidadeStore" class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Salvar Unidade</span>
                </span>

                {{-- Estado carregando --}}
                <span wire:loading.flex wire:target="unidadeStore" class="items-center gap-2">
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

            <button
                type="button"
                wire:click="colaboradorStore"
                wire:loading.attr="disabled"
                class="bg-[#e8c153] hover:bg-[#f3d173] text-[#0c0f16] font-semibold
           px-4 py-1.5 rounded-md shadow-sm flex items-center gap-1.5
           text-sm transition disabled:opacity-70 disabled:cursor-not-allowed">

                {{-- Estado normal --}}
                <span wire:loading.remove wire:target="colaboradorStore" class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Salvar Colaborador</span>
                </span>

                {{-- Estado carregando --}}
                <span wire:loading.flex wire:target="colaboradorStore" class="items-center gap-2">
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
    </section>
</div>