<div class="min-h-screen flex items-center justify-center bg-[#0c0f16] font-[Inter] text-[#f3f4f6]">
    <div class="bg-[#121623] border border-[#1e2433] rounded-2xl shadow-2xl p-8 w-full max-w-sm">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-semibold text-[#e8c153] tracking-wide">Acesso ao Painel</h2>
            <p class="text-sm text-gray-400 mt-1">Entre com suas credenciais</p>
        </div>

        <form wire:submit.prevent="login" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">E-mail</label>
                <input
                    id="email"
                    wire:model.defer="email"
                    type="email"
                    placeholder="Digite seu e-mail"
                    class="w-full px-3 py-2 rounded-md bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6] focus:outline-none focus:ring-2 focus:ring-[#e8c153] transition">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Senha</label>
                <input
                    id="password"
                    wire:model.defer="password"
                    type="password"
                    placeholder="Digite sua senha"
                    class="w-full px-3 py-2 rounded-md bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6] focus:outline-none focus:ring-2 focus:ring-[#e8c153] transition">
            </div>

            @if ($msg)
                <p class="text-red-400 text-sm text-center mt-1">{{ $msg }}</p>
            @endif

            <button
                type="submit"
                class="w-full py-2 bg-[#e8c153] hover:bg-[#f1d071] text-[#0c0f16] font-semibold rounded-md shadow-md transition">
                Entrar
            </button>
        </form>

        <p class="text-center text-sm mt-5 text-gray-400">
            Não tem conta?
            <a href="{{ route('register') }}" class="text-[#e8c153] hover:text-[#f1d071] underline">
                Registrar
            </a>
        </p>
    </div>
</div>
