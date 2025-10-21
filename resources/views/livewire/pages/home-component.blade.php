<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VOCH TECH — Painel</title>

  @vite(['resources/css/app.css','resources/js/app.js'])
  @livewireStyles

  <style>
    body {
      background-color: #0c0f16;
      color: #f3f4f6;
      font-family: 'Inter', sans-serif;
    }

    /* === TABS === */
    .tab-link {
      padding: 0.8rem 1.4rem;
      /* 🔼 Aumentada */
      border-radius: 8px;
      font-weight: 500;
      color: #d1d5db;
      transition: all 0.2s ease-in-out;
    }

    .tab-link:hover {
      color: #e8c153;
      background-color: rgba(232, 193, 83, 0.08);
    }

    .active-tab {
      color: #0c0f16;
      background-color: #e8c153;
      font-weight: 600;
      padding: 0.8rem 1.4rem;
      /* mantém coerência */
      border-radius: 8px;
    }

    /* === SCROLL AREA / CONTAINER === */
    .scroll-area {
      min-height: 480px;
      max-height: 960px;
      max-width: 800px;
      margin: auto;
      overflow-y: auto;
      background-color: #121623;
      border-radius: 1rem;
      padding: 1.5rem;
    }

    .scroll-area::-webkit-scrollbar {
      width: 6px;
    }

    .scroll-area::-webkit-scrollbar-thumb {
      background-color: #c9a227cc;
      border-radius: 3px;
    }
  </style>
</head>

<body class="antialiased min-h-screen flex flex-col items-center justify-start p-10">
  <div class="w-full max-w-7xl mx-auto" x-data="{ tab: 'cadastros' }">
    {{-- CABEÇALHO --}}
    <header class="flex items-center justify-between mb-4 w-full">
      <div class="flex items-center gap-3">
        <img src="/images/logo-voch.png" alt="VOCH TECH" class="w-8 h-8">
        <div>
          <h1 class="text-lg font-semibold text-[#e8c153] tracking-wide">
            Painel de Configurações
          </h1>
          <span class="text-xs text-gray-500">Administração</span>
        </div>
      </div>
    </header>

    {{-- ABAS --}}
    <nav role="tablist"
      class="flex justify-center gap-3 mb-5 border-b border-[#1a1e2d] pb-2">
      <button
        @click="tab='cadastros'"
        :class="tab==='cadastros' ? 'active-tab' : 'tab-link'">
        Cadastros
      </button>
      <button
        @click="tab='relatorio'"
        :class="tab==='relatorio' ? 'active-tab' : 'tab-link'">
        Relatórios
      </button>
      <button
        @click="tab='auditoria'"
        :class="tab==='auditoria' ? 'active-tab' : 'tab-link'">
        Auditoria
      </button>
    </nav>

    {{-- CONTEÚDO --}}
    <section class="scroll-area min-h-[600px] max-h-[800px] overflow-y-auto bg-[#121623] rounded-xl p-8">
      <template x-if="tab==='cadastros'">
        <div class="pb-6">
          <livewire:pages.cadastro-component />
        </div>
      </template>

      <template x-if="tab==='relatorio'">
        <div>
          <livewire:pages.relatorios-component />
        </div>
      </template>

      <template x-if="tab==='auditoria'">
        <div class="h-full flex items-center justify-center text-gray-400 italic">
          Módulo de auditoria em desenvolvimento...
        </div>
      </template>
    </section>