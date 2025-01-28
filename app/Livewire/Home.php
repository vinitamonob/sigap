<?php

namespace App\Livewire;

use Livewire\Component;

class Home extends Component
{

    public $angka = 1;
 
    public function increment()
    {
        $this->angka++;
    }
 
    public function decrement()
    {
        $this->angka--;
    }

    public function render()
    {
        return view('livewire.home');
    }
}
