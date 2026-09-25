<?php

namespace App\Http\Livewire\Rapat;

use App\Models\Rapat;
use Livewire\Attributes\On;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PanelQr extends Component
{
    public ?string $rapatId = null;
    public ?Rapat $rapat = null;
    public bool $tampil = false;

    #[On('buka-panel-qr')]
    public function buka(string $rapatId): void
    {
        $this->rapatId = $rapatId;
        $this->rapat = Rapat::find($rapatId);
        $this->tampil = true;
    }

    public function tutup(): void
    {
        $this->tampil = false;
        $this->rapatId = null;
        $this->rapat = null;
    }

    public function render()
    {
        $qrSvg = null;
        $urlRegistrasi = null;

        if ($this->rapat) {
            $urlRegistrasi = url('/r/' . $this->rapat->kode);
            $qrSvg = QrCode::size(240)
                ->margin(1)
                ->generate($urlRegistrasi);
        }

        return view('livewire.rapat.panel-qr', [
            'urlRegistrasi' => $urlRegistrasi,
            'qrSvg' => $qrSvg,
        ]);
    }
}
