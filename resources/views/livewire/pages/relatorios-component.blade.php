<div
    wire:key="relatorios"
    x-data="relatoriosUI(
        @entangle('dados').live,
        @entangle('tipoRelatorio'),
        @entangle('foreignOptions').live
    )"
    x-init="init()"
    class="p-6 bg-[#0c0f16] text-[#f3f4f6] font-[Inter] rounded-xl border border-[#1e2433] shadow-lg"
>
    {{-- =========================
        SELETOR DE RELATÓRIO
    ========================== --}}
    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <label for="tipoRelatorio" class="text-sm text-[#e8c153] font-semibold">
                Tipo de Relatório:
            </label>
            <select
                id="tipoRelatorio"
                x-model="tipoRelatorio"
                @change="$wire.set('tipoRelatorio', tipoRelatorio); $wire.relatorio()"
                class="bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6] rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#e8c153]"
            >
                <option value="">Selecione...</option>
                <option value="grupos">Grupos Econômicos</option>
                <option value="bandeiras">Bandeiras</option>
                <option value="unidades">Unidades</option>
                <option value="colaboradores">Colaboradores</option>
            </select>
        </div>

        <button
            @click="$wire.relatorio()"
            class="bg-[#e8c153] hover:bg-[#f1d071] text-[#0c0f16] font-semibold px-5 py-2 rounded-md shadow-md transition"
        >
            Atualizar Relatório
        </button>
    </div>

    {{-- =========================
        FILTROS
    ========================== --}}
    <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
        <div class="flex items-center gap-2">
            <input
                type="text"
                placeholder="Buscar..."
                x-model.debounce.300ms="busca"
                class="bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6] placeholder-[#9ca3af] rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#e8c153]"
            />
        </div>

        <div class="flex items-center gap-3">
            <button
                @click="toggleSort('id')"
                :class="ordemCampo === 'id' ? 'ring-2 ring-[#e8c153] text-[#e8c153]' : ''"
                class="px-3 py-2 rounded-md bg-[#121623] border border-[#2a3044] text-[#f3f4f6] hover:text-[#e8c153] transition"
            >
                Ordenar por ID
            </button>
            <button
                @click="toggleSort('nome')"
                :class="ordemCampo === 'nome' ? 'ring-2 ring-[#e8c153] text-[#e8c153]' : ''"
                class="px-3 py-2 rounded-md bg-[#121623] border border-[#2a3044] text-[#f3f4f6] hover:text-[#e8c153] transition"
            >
                Ordenar A–Z
            </button>
        </div>
    </div>

    {{-- =========================
        TABELA
    ========================== --}}
    <div class="overflow-x-auto border border-[#1e2433] rounded-lg shadow-inner">
        <table class="w-full text-sm text-left border-collapse text-[#f3f4f6]">
            <thead class="bg-[#121623] text-[#e8c153] uppercase text-xs tracking-wider border-b border-[#1e2433]">
                <tr>
                    <template x-for="col in colunas" :key="col">
                        <th class="px-4 py-3 text-center" x-text="col"></th>
                    </template>
                    <th class="px-4 py-3 text-center">Ações</th>
                </tr>
            </thead>

            <tbody>
                <template x-if="rows().length === 0">
                    <tr>
                        <td colspan="999" class="text-center py-6 text-gray-400">
                            Nenhum dado encontrado.
                        </td>
                    </tr>
                </template>

                <template x-for="(linha, index) in rows()" :key="linha.id ?? index">
                    <tr
                        class="border-b border-[#1e2433] hover:bg-[#151822] transition-colors"
                        x-data="{ editando: false }"
                    >
                        {{-- Campos --}}
                        <template x-for="col in colunas" :key="col">
                            <td class="px-4 py-2 text-center">
                                {{-- ID --}}
                                <template x-if="col === 'id'">
                                    <span x-text="linha[col]" class="text-gray-400"></span>
                                </template>

                                {{-- Foreign Key --}}
                                <template x-if="col.endsWith('_id')">
                                    <div>
                                        <span
                                            x-show="!editando"
                                            x-text="nomeFK(linha, col)"
                                            class="text-[#f3f4f6]"
                                        ></span>
                                        <select
                                            x-show="editando"
                                            x-model="linha[col]"
                                            @change="syncFK(linha, col)"
                                            class="bg-[#0c0f16] border border-[#2a3044] rounded-md text-[#f3f4f6] px-2 py-1 w-full"
                                        >
                                            <option value="">-- Selecione --</option>
                                            <template x-for="opt in foreignOptions" :key="opt.id">
                                                <option :value="opt.id" x-text="opt.nome"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>

                                {{-- Campos normais --}}
                                <template x-if="!col.endsWith('_id') && col !== 'id'">
                                    <div>
                                        <span
                                            x-show="!editando"
                                            x-text="linha[col]"
                                            class="text-[#f3f4f6]"
                                        ></span>
                                        <input
                                            x-show="editando"
                                            type="text"
                                            x-model="linha[col]"
                                            class="bg-[#0c0f16] border border-[#2a3044] rounded-md px-2 py-1 w-full text-[#f3f4f6]"
                                        />
                                    </div>
                                </template>
                            </td>
                        </template>

                        {{-- Ações --}}
                        <td class="px-4 py-2 text-center whitespace-nowrap">
                            <template x-if="!editando">
                                <div>
                                    <button
                                        @click="editando = true"
                                        class="text-sky-400 hover:text-sky-300 mx-1 font-medium"
                                    >
                                        editar
                                    </button>
                                    <button
                                        @click="$wire.delete(linha.id)"
                                        class="text-red-400 hover:text-red-300 mx-1 font-medium"
                                    >
                                        excluir
                                    </button>
                                </div>
                            </template>

                            <template x-if="editando">
                                <div>
                                    <button
                                        @click="$wire.saveEdit(index); editando=false"
                                        class="text-green-400 hover:text-green-300 mx-1 font-medium"
                                    >
                                        salvar
                                    </button>
                                    <button
                                        @click="editando=false"
                                        class="text-gray-400 hover:text-gray-300 mx-1 font-medium"
                                    >
                                        cancelar
                                    </button>
                                </div>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

{{-- =========================
    SCRIPT ALPINE ORIGINAL
========================= --}}
<script>
    function relatoriosUI(dadosLive, tipoRelatorioLive, foreignOpts) {
        return {
            dados: dadosLive || [],
            tipoRelatorio: tipoRelatorioLive,
            foreignOptions: foreignOpts || [],
            busca: '',
            ordemCampo: 'id',
            ordemAsc: true,
            colunas: [],

            init() {
                this.$watch('dados', v => {
                    if (Array.isArray(v) && v.length)
                        this.colunas = Object.keys(v[0]);
                });
                if (Array.isArray(this.dados) && this.dados.length)
                    this.colunas = Object.keys(this.dados[0]);
            },

            nomeFK(linha, campo) {
                const opt = this.foreignOptions.find(o => String(o.id) === String(linha[campo]));
                return opt ? opt.nome : '—';
            },

            syncFK(linha, campo) {
                const opt = this.foreignOptions.find(o => String(o.id) === String(linha[campo]));
                if (!opt) return;
                const nomeCampo = campo.replace('_id', '');
                if (linha[nomeCampo] !== undefined) linha[nomeCampo] = opt.nome;
            },

            toggleSort(campo) {
                if (this.ordemCampo === campo) this.ordemAsc = !this.ordemAsc;
                else {
                    this.ordemCampo = campo;
                    this.ordemAsc = true;
                }
            },

            rows() {
                let out = Array.isArray(this.dados) ? [...this.dados] : [];

                // Filtro de texto
                if (this.busca.trim()) {
                    const termo = this.busca.toLowerCase();
                    out = out.filter(l =>
                        Object.values(l).some(v =>
                            String(v ?? '').toLowerCase().includes(termo)
                        )
                    );
                }

                // Ordenação
                const campo = this.ordemCampo;
                out.sort((a, b) => {
                    let A = a[campo],
                        B = b[campo];
                    if (campo === 'id') {
                        A = Number(A ?? 0);
                        B = Number(B ?? 0);
                        return this.ordemAsc ? A - B : B - A;
                    } else {
                        A = String(A ?? '').toLowerCase();
                        B = String(B ?? '').toLowerCase();
                        return this.ordemAsc ? A.localeCompare(B) : B.localeCompare(A);
                    }
                });
                return out;
            },
        };
    }
</script>
