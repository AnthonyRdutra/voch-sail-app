<div class="grid grid-cols-1 xl:grid-cols-2 gap-10 justify-items-center">

    {{-- =================== GRUPOS =================== --}}
    <section class="bg-[#121623] rounded-2xl p-5 w-full max-w-[420px] transition">
        <h2 class="text-lg font-semibold text-[#facc15] pb-2 mb-5">
            Cadastro de Grupo Econômico
        </h2>
        <div class="space-y-4">
            <input type="text" wire:model.defer="grupo_nome" placeholder="Digite o nome do grupo"
                class="w-full px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] placeholder:text-[#9ca3af] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition">
            <button wire:click="grupoStore"
                class="w-full py-2 rounded-md bg-gradient-to-r from-[#c9a227] to-[#eab308] text-[#111827] font-semibold hover:brightness-110 transition">
                Salvar Grupo
            </button>
        </div>
    </section>

    {{-- =================== BANDEIRAS =================== --}}
    <section class="bg-[#121623] rounded-2xl p-5 w-full max-w-[420px] transition">
        <h2 class="text-lg font-semibold text-[#facc15] pb-2 mb-5">
            Cadastro de Bandeira
        </h2>
        <div class="space-y-4">
            <input type="text" wire:model.defer="bandeira_nome" placeholder="Digite o nome da bandeira"
                class="w-full px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] placeholder:text-[#9ca3af] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition">

            <div>
                <label class="text-xs text-[#d1d5db] mb-1 block">Grupo Econômico</label>
                <div class="relative">
                    <select wire:model.defer="grupo_economico_id"
                        class="w-full appearance-none px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition cursor-pointer">
                        <option value="">-- Selecione o Grupo Econômico --</option>
                        @foreach($grupoEconomico as $grupo)
                        <option value="{{ $grupo['id'] }}">{{ $grupo['nome'] }}</option>
                        @endforeach
                    </select>
                    <span class="absolute right-3 top-2.5 text-[#eab308] pointer-events-none text-xs">
                        <i class="fa-solid fa-caret-down"></i>
                    </span>
                </div>
            </div>

            <button wire:click="bandeiraStore"
                class="w-full py-2 rounded-md bg-gradient-to-r from-[#c9a227] to-[#eab308] text-[#111827] font-semibold hover:brightness-110 transition">
                Salvar Bandeira
            </button>
        </div>
    </section>

    {{-- =================== UNIDADES =================== --}}
    <section class="bg-[#121623] rounded-2xl p-5 w-full max-w-[420px] transition">
        <h2 class="text-lg font-semibold text-[#facc15] pb-2 mb-5">
            Cadastro de Unidade
        </h2>
        <div class="space-y-4">
            <input type="text" wire:model.defer="nome_fantasia" placeholder="Nome fantasia"
                class="w-full px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] placeholder:text-[#9ca3af] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition">
            <input type="text" wire:model.defer="razao_social" placeholder="Razão social"
                class="w-full px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] placeholder:text-[#9ca3af] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition">
            <input type="text" wire:model.defer="cnpj" placeholder="CNPJ"
                class="w-full px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] placeholder:text-[#9ca3af] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition">

            <div>
                <label class="text-xs text-[#d1d5db] mb-1 block">Bandeira</label>
                <div class="relative">
                    <select wire:model.defer="bandeira_id"
                        class="w-full appearance-none px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition cursor-pointer">
                        <option value="">-- Selecione a Bandeira --</option>
                        @foreach($bandeiras as $bandeira)
                        <option value="{{ $bandeira['id'] }}">{{ $bandeira['nome'] }}</option>
                        @endforeach
                    </select>
                    <span class="absolute right-3 top-2.5 text-[#eab308] pointer-events-none text-xs">
                        <i class="fa-solid fa-caret-down"></i>
                    </span>
                </div>
            </div>

            <button wire:click="unidadeStore"
                class="w-full py-2 rounded-md bg-gradient-to-r from-[#c9a227] to-[#eab308] text-[#111827] font-semibold hover:brightness-110 transition">
                Salvar Unidade
            </button>
        </div>
    </section>

    {{-- =================== COLABORADORES =================== --}}
    <section class="bg-[#121623] rounded-2xl p-5 w-full max-w-[420px] transition">
        <h2 class="text-lg font-semibold text-[#facc15] pb-2 mb-5">
            Cadastro de Colaborador
        </h2>
        <div class="space-y-4">
            <input type="text" wire:model.defer="colaborador_nome" placeholder="Nome completo"
                class="w-full px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] placeholder:text-[#9ca3af] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition">
            <input type="email" wire:model.defer="email" placeholder="Email"
                class="w-full px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] placeholder:text-[#9ca3af] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition">
            <input type="text" wire:model.defer="cpf" placeholder="CPF"
                class="w-full px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] placeholder:text-[#9ca3af] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition">

            <div>
                <label class="text-xs text-[#d1d5db] mb-1 block">Unidade</label>
                <div class="relative">
                    <select wire:model.defer="unidade_id"
                        class="w-full appearance-none px-3 py-2 bg-[#1a2030] border border-[#2a3248] rounded-md text-[#f3f4f6] focus:ring-2 focus:ring-[#eab308] focus:outline-none transition cursor-pointer">
                        <option value="">-- Selecione a Unidade --</option>
                        @foreach($unidades as $unidade)
                        <option value="{{ $unidade['id'] }}">{{ $unidade['nome_fantasia'] ?? 'Sem nome' }}</option>
                        @endforeach
                    </select>
                    <span class="absolute right-3 top-2.5 text-[#eab308] pointer-events-none text-xs">
                        <i class="fa-solid fa-caret-down"></i>
                    </span>
                </div>
            </div>

            <button wire:click="colaboradorStore"
                class="w-full py-2 rounded-md bg-gradient-to-r from-[#c9a227] to-[#eab308] text-[#111827] font-semibold hover:brightness-110 transition">
                Salvar Colaborador
            </button>
        </div>
    </section>
</div>