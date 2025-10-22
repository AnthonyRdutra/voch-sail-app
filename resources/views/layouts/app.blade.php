<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'VOCH TECH — Painel' }}</title>

    {{-- ✅ Importa Tailwind + JS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ✅ Livewire CSS --}}
    @livewireStyles

    {{-- ✅ Fonte principal --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- ✅ Força o tema dark como base --}}
    <style>
        body {
            @apply bg-slate-950 text-slate-100 font-[Inter] antialiased min-h-screen;
        }
    </style>
</head>

<body class="bg-slate-950 text-slate-100 font-[Inter] antialiased">
    <header class="flex items-center justify-between px-8 py-4 border-b border-slate-800 bg-slate-900/70 backdrop-blur">
        <span class="text-sm text-slate-400">Painel de Cadastros</span>
    </header>

    <main class="p-8">
        {{ $slot }}
    </main>

    {{-- ✅ Scripts Livewire --}}
    @livewireScripts
</body>

</html>