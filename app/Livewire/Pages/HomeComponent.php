<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class HomeComponent extends Component
{
    public function render()
    {
        return view('livewire.pages.home-component')->layout('layouts.app');
    }
}
