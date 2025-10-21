<div class="max-w-sm mx-auto mt-24 text-slate-100">
    <h2 class="text-xl mb-4 text-center">Login</h2>

    <form wire:submit.prevent="login" class="space-y-3 bg-slate-800 p-6 rounded-lg shadow-md">
        <input wire:model.defer="email" type="email" placeholder="E-mail"
            class="w-full px-3 py-2 rounded bg-slate-700 border border-slate-600">
        <input wire:model.defer="password" type="password" placeholder="Senha"
            class="w-full px-3 py-2 rounded bg-slate-700 border border-slate-600">

        @if($msg)
            <p class="text-red-400 text-sm">{{ $msg }}</p>
        @endif

        <button class="bg-blue-600 hover:bg-blue-700 w-full py-2 rounded font-semibold">
            Entrar
        </button>
    </form>

    <p class="text-center text-sm mt-3 text-slate-400">
        Não tem conta?
        <a href="{{ route('register') }}" class="text-blue-400 hover:underline">Registrar</a>
    </p>
</div>
