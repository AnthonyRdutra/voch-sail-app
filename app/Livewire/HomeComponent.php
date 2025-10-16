<?php

namespace App\Livewire; 

use Livewire\Component; 

class HomeComponent extends Component {
    public $activeCard = 'grupos'; 

    public function setCard($card)
    {
        $this->activeCard = $card; 
    }

    public function render(){
        return view('livewire.home-component')->layout('layouts.app');
    }
}