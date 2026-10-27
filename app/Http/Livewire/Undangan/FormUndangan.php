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
    public ?string $undanganId = null;
    public bool $isEdit = false;
    public bool $tampil = false;

    public string $nama = '';
    public string $jabatan = '';
    public string $instansi = '';
    public string $hp = '';
    public string $sumber = 'tambahan';

    #[On('buka-modal-tambah-undangan')]
    public function buka(string $rapatId): void
    {
        $this->undanganId = null;
        $this->isEdit = false;
        $this->rapatId = $rapatId;
        $this->rapat = Rapat::find($rapatId);
        $this->reset(['nama', 'jabatan', 'instansi', 'hp']);
        $this->sumber = 'tambahan';
        $this->resetValidation();
        $this->tampil = true;
    }

    #[On('buka-modal-edit-undangan')]
    public function bukaEdit(string $id): void
    {
        $undangan = Undangan::with('rapat')->findOrFail($id);
        $this->undanganId = $undangan->id;
        $this->rapatId = $undangan->rapat_id;
        $this->rapat = $undangan->rapat;
        $this->nama = $undangan->nama;
        $this->jabatan = $undangan->jabatan ?? '';
        $this->instansi = $undangan->instansi ?? '';
        $this->hp = $undangan->hp ?? '';
        $this->sumber = $undangan->sumber->value;
        $this->isEdit = true;
        $this->resetValidation();
        $this->tampil = true;
    }

    public function tutup(): void
    {
        $this->tampil = false;
        $this->reset(['nama', 'jabatan', 'instansi', 'hp', 'rapatId', 'rapat', 'undanganId', 'isEdit']);
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

        $sumberEnum = $this->sumber === 'import' ? SumberUndangan::IMPORT : SumberUndangan::TAMBAHAN;
        $aktor = Auth::user()?->profil;

        if ($this->isEdit && $this->undanganId) {
            $undangan = Undangan::findOrFail($this->undanganId);
            $namaLama = $undangan->nama;

            $undangan->update([
                'nama' => trim($this->nama),
                'jabatan' => trim($this->jabatan),
                'instansi' => trim($this->instansi),
                'hp' => trim($this->hp),
                'sumber' => $sumberEnum,
            ]);

            AuditLog::create([
                'aktor' => $aktor?->id,
                'aksi' => "Memperbarui data undangan: {$undangan->nama} (sebelumnya: {$namaLama}) pada rapat '{$this->rapat?->judul}'",
                'tabel_target' => 'undangan',
                'baris_id' => $undangan->id,
                'rincian' => [
                    'rapat_id' => $this->rapatId,
                    'nama' => $undangan->nama,
                    'jabatan' => $undangan->jabatan,
                    'instansi' => $undangan->instansi,
                    'sumber' => $undangan->sumber->value,
                ],
            ]);

            $this->dispatch('undangan-diperbarui', pesan: "Data undangan {$undangan->nama} berhasil diperbarui.");
            $this->dispatch('swal:alert', [
                'title' => 'Undangan Diperbarui',
                'text' => "Perubahan data peserta '{$undangan->nama}' telah berhasil disimpan.",
                'icon' => 'success',
            ]);
        } else {
            $maxUrutan = (int) Undangan::where('rapat_id', $this->rapatId)->max('urutan');

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
            $this->dispatch('swal:alert', [
                'title' => 'Undangan Ditambahkan',
                'text' => "Peserta '{$undangan->nama}' berhasil ditambahkan ke daftar undangan rapat.",
                'icon' => 'success',
            ]);
        }

        $this->tutup();
    }

    public function render()
    {
        return view('livewire.undangan.form-undangan');
    }
}
