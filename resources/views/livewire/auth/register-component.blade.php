<div class="min-h-screen flex items-center justify-center bg-[#0c0f16] font-[Inter] text-[#f3f4f6]">
    <div class="bg-[#121623] border border-[#1e2433] rounded-2xl shadow-2xl p-8 w-full max-w-sm">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-semibold text-[#e8c153] tracking-wide">Criar Conta</h2>
            <p class="text-sm text-gray-400 mt-1">Preencha seus dados para se registrar</p>
        </div>

        <form wire:submit.prevent="register" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Nome</label>
                <input
                    id="name"
                    wire:model.defer="name"
                    type="text"
                    placeholder="Digite seu nome completo"
                    class="w-full px-3 py-2 rounded-md bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6]
                           focus:outline-none focus:ring-2 focus:ring-[#e8c153] transition">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">E-mail</label>
                <input
                    id="email"
                    wire:model.defer="email"
                    type="email"
                    placeholder="Digite seu e-mail"
                    class="w-full px-3 py-2 rounded-md bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6]
                           focus:outline-none focus:ring-2 focus:ring-[#e8c153] transition">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Senha</label>
                <input
                    id="password"
                    wire:model.defer="password"
                    type="password"
                    placeholder="Crie uma senha"
                    class="w-full px-3 py-2 rounded-md bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6]
                           focus:outline-none focus:ring-2 focus:ring-[#e8c153] transition">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">Confirmar Senha</label>
                <input
                    id="password_confirmation"
                    wire:model.defer="password_confirmation"
                    type="password"
                    placeholder="Repita sua senha"
                    class="w-full px-3 py-2 rounded-md bg-[#1a1f2d] border border-[#2a3044] text-[#f3f4f6]
                           focus:outline-none focus:ring-2 focus:ring-[#e8c153] transition">
            </div>

            @if ($msg)
                <p class="text-red-400 text-sm text-center mt-1">{{ $msg }}</p>
            @endif

            <button
                type="submit"
                class="w-full py-2 bg-[#e8c153] hover:bg-[#f1d071] text-[#0c0f16]
                       font-semibold rounded-md shadow-md transition">
                Registrar
            </button>
        </form>

        <p class="text-center text-sm mt-5 text-gray-400">
            Já tem conta?
            <a href="{{ route('login') }}" class="text-[#e8c153] hover:text-[#f1d071] underline">
                Fazer login
            </a>
        </p>
    </div>
</div>
