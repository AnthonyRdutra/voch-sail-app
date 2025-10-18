<?php

namespace App\Livewire;

use Livewire\Component;

class HomeComponent extends Component
{

    public $painel;

    public function mount()
    {
        if (empty($this->painel)) {
            $this->painel = 'cadastros';
        }
    }
    
    public function render()
    {
        return view('livewire.home-component')->layout('layouts.app');
    }
}
