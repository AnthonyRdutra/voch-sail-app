<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\LogAuditoria;

use Livewire\Component;

class RegisterComponent extends Component
{
    use LogAuditoria; 

    public $name;
    public $email; 
    public $password;
    public $password_confirmation;
    public $msg; 

    public function register()
    {

        $validated = $this->validate([
            'name' => 'required|string|min:4',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|same:password_confirmation',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        Auth::login($user);
        return redirect()->route('dashboard');
    }


    public function render()
    {
        return view('livewire.auth.register-component')->layout('layouts.app');
    }
}
