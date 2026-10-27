<?php

namespace App\Http\Livewire\Undangan;

use App\Models\Rapat;
use App\Services\ImporUndanganService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImporCsv extends Component
{
    use WithFileUploads;

    public ?string $rapatId = null;
    public ?Rapat $rapat = null;
    public bool $tampil = false;

    public $file = null;
    public ?array $previewData = null;
    public bool $abaikanDuplikat = true;
    public ?string $pesanError = null;

    #[On('buka-modal-impor-csv')]
    public function buka(string $rapatId): void
    {
        $this->rapatId = $rapatId;
        $this->rapat = Rapat::find($rapatId);
        $this->reset(['file', 'previewData', 'pesanError']);
        $this->abaikanDuplikat = true;
        $this->tampil = true;
    }

    public function tutup(): void
    {
        $this->tampil = false;
        $this->reset(['file', 'previewData', 'pesanError', 'rapatId', 'rapat']);
    }

    public function updatedFile(ImporUndanganService $service): void
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ], [
            'file.required' => 'Pilih berkas CSV atau Excel terlebih dahulu.',
            'file.mimes' => 'Format berkas harus berupa .csv, .xlsx, atau .xls.',
            'file.max' => 'Ukuran berkas maksimal 5MB.',
        ]);

        try {
            $this->previewData = $service->bacaDanValidasi($this->file->getRealPath(), $this->rapatId);
            $this->pesanError = null;
        } catch (\Exception $e) {
            $this->pesanError = 'Gagal memproses berkas: ' . $e->getMessage();
            $this->previewData = null;
        }
    }

    public function eksekusiImpor(ImporUndanganService $service)
    {
        if (empty($this->previewData) || empty($this->previewData['baris'])) {
            $this->pesanError = 'Tidak ada baris data valid untuk diimpor.';
            return;
        }

        $aktor = Auth::user()?->profil;

        $totalDiimpor = $service->simpan(
            rapatId: $this->rapatId,
            barisData: $this->previewData['baris'],
            abaikanDuplikat: $this->abaikanDuplikat,
            aktor: $aktor
        );

        $this->dispatch('undangan-diperbarui', pesan: "Berhasil mengimpor {$totalDiimpor} nama undangan.");
        $this->dispatch('swal:alert', [
            'title' => 'Impor Undangan Berhasil',
            'text' => "Sebanyak {$totalDiimpor} data undangan baru telah berhasil diimpor ke dalam rapat.",
            'icon' => 'success',
        ]);
        $this->tutup();
    }

    public function render()
    {
        return view('livewire.undangan.impor-csv');
    }
}
