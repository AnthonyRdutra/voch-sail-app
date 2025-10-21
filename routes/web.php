<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\LoginComponent;
use App\Livewire\Auth\RegisterComponent;
use App\Livewire\Pages\HomeComponent;

// Página inicial redireciona para o login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rota de login
Route::get('/login', LoginComponent::class)
    ->name('login')
    ->middleware('guest');

// Rota de registro
Route::get('/register', RegisterComponent::class)
    ->name('register')
    ->middleware('guest');

Route::get('/home', HomeComponent::class)->name('home');
