<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LoginComponent extends Component
{

    public $email = '';
    public $password = '';
    public $msg = '';

    public function login()
    {
        $credentials = [
            'email' => $this->email,
            'password' => $this->password
        ];

        if(Auth::attempt($credentials, true)){
            session()->regenerate();
            return redirect()->route('dashboard');
        }

        $this->msg = 'email ou senha incorretos';
    }

    public function render()
    {
        return view('livewire.auth.login-component')->layout('layouts.app');
    }
}
