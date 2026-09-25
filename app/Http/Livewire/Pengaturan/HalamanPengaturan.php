<?php

namespace App\Http\Livewire\Pengaturan;

use Livewire\Attributes\Url;
use Livewire\Component;

class HalamanPengaturan extends Component
{
    #[Url(as: 'tab')]
    public string $tabAktif = 'operator';

    public function setTab(string $tab): void
    {
        $this->tabAktif = $tab;
    }

    public function render()
    {
        return view('livewire.pengaturan.halaman-pengaturan')
            ->layout('layouts.app', ['title' => 'Pengaturan Sistem']);
    }
}
