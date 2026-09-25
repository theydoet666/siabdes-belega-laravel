<?php

namespace App\Http\Livewire\Undangan;

use App\Enums\SumberUndangan;
use App\Models\AuditLog;
use App\Models\Rapat;
use App\Models\Undangan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class FormUndangan extends Component
{
    public ?string $rapatId = null;
    public ?Rapat $rapat = null;
    public bool $tampil = false;

    public string $nama = '';
    public string $jabatan = '';
    public string $instansi = '';
    public string $hp = '';
    public string $sumber = 'tambahan';

    #[On('buka-modal-tambah-undangan')]
    public function buka(string $rapatId): void
    {
        $this->rapatId = $rapatId;
        $this->rapat = Rapat::find($rapatId);
        $this->reset(['nama', 'jabatan', 'instansi', 'hp']);
        $this->sumber = 'tambahan';
        $this->resetValidation();
        $this->tampil = true;
    }

    public function tutup(): void
    {
        $this->tampil = false;
        $this->reset(['nama', 'jabatan', 'instansi', 'hp', 'rapatId', 'rapat']);
        $this->resetValidation();
    }

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:30',
        ];
    }

    protected function messages(): array
    {
        return [
            'nama.required' => 'Nama undangan wajib diisi.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'hp.max' => 'Nomor HP maksimal 30 karakter.',
        ];
    }

    public function simpan()
    {
        $this->validate();

        $maxUrutan = (int) Undangan::where('rapat_id', $this->rapatId)->max('urutan');
        $sumberEnum = $this->sumber === 'import' ? SumberUndangan::IMPORT : SumberUndangan::TAMBAHAN;

        $undangan = Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapatId,
            'nama' => trim($this->nama),
            'jabatan' => trim($this->jabatan),
            'instansi' => trim($this->instansi),
            'hp' => trim($this->hp),
            'sumber' => $sumberEnum,
            'urutan' => $maxUrutan + 1,
        ]);

        $aktor = Auth::user()?->profil;
        AuditLog::create([
            'aktor' => $aktor?->id,
            'aksi' => "Menambahkan undangan manual: {$undangan->nama} ke rapat '{$this->rapat?->judul}'",
            'tabel_target' => 'undangan',
            'baris_id' => $undangan->id,
            'rincian' => [
                'rapat_id' => $this->rapatId,
                'nama' => $undangan->nama,
                'sumber' => $undangan->sumber->value,
            ],
        ]);

        $this->dispatch('undangan-diperbarui', pesan: "Undangan {$undangan->nama} berhasil ditambahkan.");
        $this->tutup();
    }

    public function render()
    {
        return view('livewire.undangan.form-undangan');
    }
}
