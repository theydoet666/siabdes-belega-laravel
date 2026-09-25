<?php

namespace App\Http\Livewire\Rapat;

use App\Models\Rapat;
use App\Services\RapatService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ModalDuplikasiRapat extends Component
{
    public ?string $rapatId = null;
    public ?Rapat $rapatAsal = null;
    public string $judulBaru = '';
    public bool $salinUndangan = true;
    public int $totalUndangan = 0;
    public bool $tampil = false;

    #[On('buka-modal-duplikasi')]
    public function buka(string $rapatId): void
    {
        $this->rapatId = $rapatId;
        $this->rapatAsal = Rapat::withCount('undangan')->find($rapatId);

        if ($this->rapatAsal) {
            $this->judulBaru = 'Salinan - ' . $this->rapatAsal->judul;
            $this->totalUndangan = $this->rapatAsal->undangan_count ?? 0;
            $this->salinUndangan = true;
            $this->tampil = true;
            $this->resetValidation();
        }
    }

    public function tutup(): void
    {
        $this->tampil = false;
        $this->rapatId = null;
        $this->rapatAsal = null;
        $this->resetValidation();
    }

    public function duplikasi(RapatService $rapatService)
    {
        $this->validate([
            'judulBaru' => 'required|string|max:255',
        ], [
            'judulBaru.required' => 'Judul rapat baru wajib diisi.',
            'judulBaru.max' => 'Judul rapat maksimal 255 karakter.',
        ]);

        if (!$this->rapatAsal) {
            return;
        }

        $aktor = Auth::user()?->profil;

        $rapatBaru = $rapatService->duplikasiRapat(
            rapatAsal: $this->rapatAsal,
            judulBaru: $this->judulBaru,
            salinUndangan: $this->salinUndangan,
            aktor: $aktor
        );

        $this->tutup();

        session()->flash('pesanSukses', "Rapat berhasil diduplikasi menjadi '{$rapatBaru->judul}' (Kode: {$rapatBaru->kode}).");

        return redirect()->route('rapat.detail', $rapatBaru->id);
    }

    public function render()
    {
        return view('livewire.rapat.modal-duplikasi-rapat');
    }
}
