<?php

namespace App\Http\Livewire\Rapat;

use App\Services\RapatService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FormRapat extends Component
{
    // Data Rapat
    public string $judul = '';
    public string $tanggal = '';
    public string $jam_mulai = '09:00';
    public string $jam_selesai = '12:00';
    public string $tempat = 'Ruang Rapat Kantor Desa Belega';
    public string $penyelenggara = 'Pemerintah Desa Belega';
    public string $catatan = '';
    public int $retensi_hari = 90;
    public string $pin_kiosk = '';

    // Data Penandatangan Laporan (3 Pihak)
    // 1. Verifikator (Sekdes)
    public bool $verifikator_tampil = true;
    public string $verifikator_label = 'Telah dilakukan verifikasi';
    public string $verifikator_jabatan = 'Sekretaris Desa';
    public string $verifikator_nama = 'Gusti Ketut Amertayasa, S.M';
    public string $verifikator_nip = '';

    // 2. Pelaksana Kegiatan (Kasi / Kaur)
    public bool $pelaksana_tampil = true;
    public string $pelaksana_jabatan = 'Kasi Pelayanan';
    public string $pelaksana_nama = 'Ni Made Arini';
    public string $pelaksana_nip = '';

    // 3. Mengetahui / Mengesahkan (Perbekel / Plt. Perbekel)
    public bool $mengetahui_tampil = true;
    public string $mengetahui_label = 'Mengetahui :';
    public string $penandatangan_nama = 'I WAYAN SUDARSANA, S.Sos.';
    public string $penandatangan_jabatan = 'Plt. Perbekel Desa Belega';
    public string $penandatangan_nip = '';
    public string $penandatangan_lokasi = 'Kantor Perbekel Desa Belega';

    public function mount(): void
    {
        $this->tanggal = date('Y-m-d');
        $this->pin_kiosk = (string) random_int(100000, 999999);
    }

    protected function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam_mulai' => 'nullable|string',
            'jam_selesai' => 'nullable|string',
            'tempat' => 'required|string|max:255',
            'penyelenggara' => 'required|string|max:255',
            'catatan' => 'nullable|string',
            'retensi_hari' => 'required|integer|min:1|max:365',
            'pin_kiosk' => 'required|string|regex:/^[0-9]{6}$/',
            'verifikator_tampil' => 'boolean',
            'verifikator_label' => 'nullable|string|max:255',
            'verifikator_jabatan' => 'nullable|string|max:255',
            'verifikator_nama' => 'nullable|string|max:255',
            'verifikator_nip' => 'nullable|string|max:50',
            'pelaksana_tampil' => 'boolean',
            'pelaksana_jabatan' => 'nullable|string|max:255',
            'pelaksana_nama' => 'nullable|string|max:255',
            'pelaksana_nip' => 'nullable|string|max:50',
            'mengetahui_tampil' => 'boolean',
            'mengetahui_label' => 'nullable|string|max:255',
            'penandatangan_nama' => 'nullable|string|max:255',
            'penandatangan_jabatan' => 'nullable|string|max:255',
            'penandatangan_nip' => 'nullable|string|max:50',
            'penandatangan_lokasi' => 'required|string|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'judul.required' => 'Judul rapat wajib diisi.',
            'tanggal.required' => 'Tanggal rapat wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tempat.required' => 'Tempat pelaksanaan rapat wajib diisi.',
            'penyelenggara.required' => 'Penyelenggara rapat wajib diisi.',
            'retensi_hari.required' => 'Masa retensi privasi wajib diisi.',
            'pin_kiosk.required' => 'PIN Kiosk 6 digit wajib diisi.',
            'pin_kiosk.regex' => 'PIN Kiosk harus terdiri dari 6 angka.',
            'penandatangan_lokasi.required' => 'Lokasi pengesahan wajib diisi.',
        ];
    }

    public function generatePinBaru(): void
    {
        $this->pin_kiosk = (string) random_int(100000, 999999);
    }

    public function simpan(RapatService $rapatService)
    {
        $this->validate();

        $aktor = Auth::user()?->profil;

        $rapat = $rapatService->buatRapat([
            'judul' => $this->judul,
            'tanggal' => $this->tanggal,
            'jam_mulai' => $this->jam_mulai ?: null,
            'jam_selesai' => $this->jam_selesai ?: null,
            'tempat' => $this->tempat,
            'penyelenggara' => $this->penyelenggara,
            'catatan' => $this->catatan ?: null,
            'retensi_hari' => $this->retensi_hari,
            'pin_kiosk' => $this->pin_kiosk,
            'verifikator_tampil' => $this->verifikator_tampil,
            'verifikator_label' => $this->verifikator_label,
            'verifikator_jabatan' => $this->verifikator_jabatan,
            'verifikator_nama' => $this->verifikator_nama,
            'verifikator_nip' => $this->verifikator_nip ?: '',
            'pelaksana_tampil' => $this->pelaksana_tampil,
            'pelaksana_jabatan' => $this->pelaksana_jabatan,
            'pelaksana_nama' => $this->pelaksana_nama,
            'pelaksana_nip' => $this->pelaksana_nip ?: '',
            'mengetahui_tampil' => $this->mengetahui_tampil,
            'mengetahui_label' => $this->mengetahui_label,
            'penandatangan_nama' => $this->penandatangan_nama ?: 'I WAYAN SUDARSANA, S.Sos.',
            'penandatangan_jabatan' => $this->penandatangan_jabatan ?: 'Perbekel Belega',
            'penandatangan_nip' => $this->penandatangan_nip ?: '',
            'penandatangan_lokasi' => $this->penandatangan_lokasi,
        ], $aktor);

        session()->flash('pesanSukses', "Rapat '{$rapat->judul}' berhasil dibuat dengan Kode: {$rapat->kode}.");

        return redirect()->route('rapat.detail', $rapat->id);
    }

    public function render()
    {
        return view('livewire.rapat.form-rapat')
            ->layout('layouts.app', ['title' => 'Buat Rapat Baru']);
    }
}
