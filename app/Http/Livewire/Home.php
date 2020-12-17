<?php

namespace App\Http\Livewire;

use App\Models\Liga;
use App\Models\Product;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.home',[
            'products' => Product::take(4)->get(),
            'ligas' => Liga::all()
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
