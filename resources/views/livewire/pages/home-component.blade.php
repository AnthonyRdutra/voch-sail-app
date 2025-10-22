<div style="
    display:flex;
    min-height:100vh;
    background:#0c0f16;
    color:#f3f4f6;
    font-family:'Inter',sans-serif;
">
    {{-- ===========================
        PAINEL LATERAL
    ============================ --}}
    <aside style="
        width:260px;
        background:#121623;
        border-right:1px solid #1e2433;
        display:flex;
        flex-direction:column;
        box-shadow:inset -4px 0 12px rgba(0,0,0,0.5);
    ">
        <div style="padding:20px;text-align:center;border-bottom:1px solid #1e2433;">
            <h2 style="color:#e8c153;margin:0;font-size:18px;">VOCH TECH</h2>
            <p style="font-size:12px;color:#9ca3af;">Teste tecnico</p>
        </div>

        <nav style="display:flex;flex-direction:column;gap:8px;padding:15px;">
            <button id="btn-cadastros" onclick="mudarPainel('cadastros')"
                style="text-align:left;padding:10px;border:none;border-radius:6px;
                       background:#eab308;color:#111827;font-weight:600;cursor:pointer;transition:all .25s;">
                🧱 Cadastros
            </button>

            {{-- Botão RELATÓRIOS --}}
            <button id="btn-relatorios" onclick="mudarPainel('relatorios')"
                style="text-align:left;padding:10px;border:none;border-radius:6px;
                       background:#1a2030;color:#f3f4f6;font-weight:600;cursor:pointer;transition:all .25s;">
                📊 Relatórios
            </button>

            <button id="btn-auditoria" onclick="mudarPainel('auditoria')"
                style="text-align:left;padding:10px;border:none;border-radius:6px;
                       background:#1a2030;color:#f3f4f6;font-weight:600;cursor:pointer;transition:all .25s;">
                📜 Auditoria
            </button>
        </nav>

        <div style="flex:1;"></div>

        <div style="text-align:center;font-size:12px;color:#9ca3af;padding:10px;border-top:1px solid #1e2433;">
            © 2025 VOCH TECH
        </div>
    </aside>

    {{-- ===========================
        CONTEÚDO PRINCIPAL
    ============================ --}}
    <main style="flex:1;overflow-y:auto;padding:30px;position:relative;">
        <style>
            .pane {
                position: absolute;
                inset: 0;
                opacity: 0;
                visibility: visible;
                /* 👈 continua visível para Livewire */
                z-index: 0;
                transition: opacity .25s ease;
            }

            .pane.active {
                opacity: 1;
                z-index: 10;
                position: relative;
            }
        </style>

        {{-- === PAINÉIS === --}}
        <div id="pane-cadastros" class="pane active">
            @livewire('pages.cadastros-component', key('cadastros-pane'))
        </div>

        <div id="pane-relatorios" class="pane">
            @livewire('pages.relatorios-component', key('relatorios-pane'))
        </div>

        <div id="pane-auditoria" class="pane">
            @livewire('pages.auditoria-component', key('auditoria-pane'))
        </div>
    </main>
</div>

{{-- ===========================
    SCRIPT DE TROCA INSTANTÂNEA
=========================== --}}
<script>
    function mudarPainel(nome) {
        const paineis = document.querySelectorAll('.pane');
        const botoes = document.querySelectorAll('nav button');

        paineis.forEach(p => p.classList.remove('active'));
        botoes.forEach(b => {
            b.style.background = '#1a2030';
            b.style.color = '#f3f4f6';
        });

        const alvo = document.getElementById('pane-' + nome);
        const botao = document.getElementById('btn-' + nome);

        if (alvo && botao) {
            alvo.classList.add('active');
            botao.style.background = '#eab308';
            botao.style.color = '#111827';
        }
    }
</script>