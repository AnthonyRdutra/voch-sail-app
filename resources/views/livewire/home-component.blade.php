<div style="background:#0f172a; padding:20px; border-radius:8px; display:flex; flex-direction:column; gap:20px;">

    <h3 style="color:#f1f5f9;">Painel Geral de Cadastros</h3>

    {{-- Grupo Econômico --}}
    <div style="background:#1e293b; border:1px solid #334155; border-radius:6px; padding:15px;">
        @livewire('grupos-card', [], key('grupos-card'))
    </div>

    {{-- Bandeira --}}
    <div style="background:#1e293b; border:1px solid #334155; border-radius:6px; padding:15px;">
        @livewire('bandeiras-card', [], key('bandeiras-card'))
    </div>

    {{-- Unidade --}}
    <div style="background:#1e293b; border:1px solid #334155; border-radius:6px; padding:15px;">
        @livewire('unidades-card', [], key('unidades-card'))
    </div>

    {{-- Colaborador --}}
    <div style="background:#1e293b; border:1px solid #334155; border-radius:6px; padding:15px;">
        @livewire('colaboradores-card', [], key('colaboradores-card'))
    </div>
</div>