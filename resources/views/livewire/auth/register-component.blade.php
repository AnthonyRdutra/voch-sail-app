<div class="max-w-sm mx-auto mt-24 text-slate-100">
    <h2 class="text-xl mb-4 text-center">Registrar</h2>

    <form wire:submit.prevent="register" class="space-y-3 bg-slate-800 p-6 rounded-lg shadow-md">
        <input wire:model.defer="name" type="text" placeholder="Nome"
            class="w-full px-3 py-2 rounded bg-slate-700 border border-slate-600">
        <input wire:model.defer="email" type="email" placeholder="E-mail"
            class="w-full px-3 py-2 rounded bg-slate-700 border border-slate-600">
        <input wire:model.defer="password" type="password" placeholder="Senha"
            class="w-full px-3 py-2 rounded bg-slate-700 border border-slate-600">
        <input wire:model.defer="password_confirmation" type="password" placeholder="Confirmar senha"
            class="w-full px-3 py-2 rounded bg-slate-700 border border-slate-600">

        @if($msg)
            <p class="text-red-400 text-sm">{{ $msg }}</p>
        @endif

        <button class="bg-green-600 hover:bg-green-700 w-full py-2 rounded font-semibold">
            Registrar
        </button>
    </form>

    <p class="text-center text-sm mt-3 text-slate-400">
        Já tem conta?
        <a href="{{ route('login') }}" class="text-blue-400 hover:underline">Fazer login</a>
    </p>
</div>
