<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0c0f16">
    <title>{{ $title ?? 'VOCH TECH' }}</title>

    {{-- =============================
        VITE & LIVEWIRE INTEGRAÇÃO
    ============================== --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- =============================
        META E FONTES GLOBAIS
    ============================== --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- =============================
        ESTILOS CUSTOMIZADOS OPCIONAIS
    ============================== --}}
    @stack('styles')
</head>

<body class="bg-dark-100 text-text-base font-inter antialiased min-h-screen flex flex-col selection:bg-[#e8c15333] selection:text-[#f3f4f6]">

    {{-- =====================================
        CONTEÚDO PRINCIPAL (via slots)
    ====================================== --}}
    <main class="flex-1">
        {{ $slot }}
    </main>
    {{-- =====================================
        LIVEWIRE E STACKS DE SCRIPTS
    ====================================== --}}
    @livewireScripts
    @stack('scripts')
</body>
</html>
