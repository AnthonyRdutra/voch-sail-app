<div
    x-data="{ aba: @entangle('painel') }"
    style="display:flex; flex-direction:column; align-items:center; gap:20px; padding-top:40px;">
    <div class="window-body" style="width:80%;">
        <menu role="tablist" class="multirows" style="display:flex; flex-wrap:wrap; gap:10px; list-style:none; padding:0; margin:0 0 15px 0;">
            <li role="tab" :aria-selected="aba === 'cadastros'">
                <a href="#cadastros"
                    @click.prevent="aba = 'cadastros'"
                    x-bind:style="aba === 'cadastros' 
                       ? 'background:#1e293b;color:#f1f5f9;border-bottom:2px solid #3b82f6;padding:6px 12px;border-radius:6px;display:inline-block;text-decoration:none;' 
                       : 'background:#334155;color:#94a3b8;padding:6px 12px;border-radius:6px;display:inline-block;text-decoration:none;'">
                    Cadastros
                </a>
            </li>
            <li role="tab" :aria-selected="aba === 'relatorios'">
                <a href="#relatorios"
                    @click.prevent="aba = 'relatorios'"
                    x-bind:style="aba === 'relatorios' 
                       ? 'background:#1e293b;color:#f1f5f9;border-bottom:2px solid #3b82f6;padding:6px 12px;border-radius:6px;display:inline-block;text-decoration:none;' 
                       : 'background:#334155;color:#94a3b8;padding:6px 12px;border-radius:6px;display:inline-block;text-decoration:none;'">
                    Relatórios
                </a>
            </li>
        </menu>

        <div class="window" role="tabpanel" style="background:#0f172a; border:1px solid #334155; border-radius:8px;">
            <div class="window-body" style="padding:20px;">
                <div x-show="aba === 'cadastros'" x-transition.duration.200ms>
                    @livewire('pages.cadastros-component', key('cadastros-component'))
                </div>
                <div x-show="aba === 'relatorios'" x-transition.duration.200ms>
                    @livewire('pages.relatorios-component', key('relatorios-component'))
                </div>
            </div>
        </div>
    </div>
</div>