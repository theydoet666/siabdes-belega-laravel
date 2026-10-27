<?php

namespace App\Http\Livewire\Rapat;

use App\Enums\StatusRapat;
use App\Models\AuditLog;
use App\Models\Rapat;
use App\Services\RapatService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class DetailRapat extends Component
{
    use AuthorizesRequests;

    public Rapat $rapat;
    public string $rapatId;

    // Form fields
    public string $judul = '';
    public string $tanggal = '';
    public ?string $jam_mulai = null;
    public ?string $jam_selesai = null;
    public string $tempat = '';
    public string $penyelenggara = '';
    public ?string $catatan = null;
    public int $retensi_hari = 90;
    public string $pin_kiosk_baru = '';

    // Penandatangan Laporan (3 Pihak)
    // 1. Verifikator (Sekdes)
    public bool $verifikator_tampil = true;
    public string $verifikator_label = 'Telah dilakukan verifikasi';
    public string $verifikator_jabatan = 'Sekretaris Desa';
    public string $verifikator_nama = 'Gusti Ketut Amertayasa, S.M';
    public ?string $verifikator_nip = '';

    // 2. Pelaksana Kegiatan (Kasi / Kaur)
    public bool $pelaksana_tampil = true;
    public string $pelaksana_jabatan = 'Kasi Pelayanan';
    public string $pelaksana_nama = 'Ni Made Arini';
    public ?string $pelaksana_nip = '';

    // 3. Mengetahui / Mengesahkan (Perbekel / Plt. Perbekel)
    public bool $mengetahui_tampil = true;
    public string $mengetahui_label = 'Mengetahui :';
    public string $penandatangan_nama = '';
    public string $penandatangan_jabatan = '';
    public ?string $penandatangan_nip = '';
    public string $penandatangan_lokasi = '';

    // Status & Tabs
    public string $status = 'draft';
    public string $tabAktif = 'undangan';

    // Flash notifications
    public ?string $pesanSukses = null;
    public ?string $pesanError = null;

    // Modal konfirmasi hapus
    public bool $bukaModalHapus = false;

    protected $queryString = [
        'tabAktif' => ['except' => 'undangan', 'as' => 'tab'],
    ];

    #[\Livewire\Attributes\On('undangan-diperbarui')]
    public function segarkanData(): void
    {
        $this->rapat->loadCount(['undangan', 'kehadiran']);
        $this->dispatch('undangan-diperbarui-tabel');
        $this->dispatch('swal:alert', [
            'title' => 'Data Rapat Disegarkan',
            'text' => "Statistik kehadiran ({$this->rapat->kehadiran_count}/{$this->rapat->undangan_count}) dan daftar undangan berhasil diperbarui.",
            'icon' => 'success',
        ]);
    }

    public function mount(string $id): void
    {
        $this->rapat = Rapat::withCount(['undangan', 'kehadiran'])->findOrFail($id);
        $this->rapatId = $this->rapat->id;

        $this->isiDataForm();
    }

    protected function isiDataForm(): void
    {
        $this->judul = $this->rapat->judul;
        $this->tanggal = $this->rapat->tanggal ? $this->rapat->tanggal->format('Y-m-d') : '';
        $this->jam_mulai = $this->rapat->jam_mulai;
        $this->jam_selesai = $this->rapat->jam_selesai;
        $this->tempat = $this->rapat->tempat;
        $this->penyelenggara = $this->rapat->penyelenggara;
        $this->catatan = $this->catatan ?: $this->rapat->catatan;
        $this->retensi_hari = $this->rapat->retensi_hari;

        $this->verifikator_tampil = (bool) ($this->rapat->verifikator_tampil ?? true);
        $this->verifikator_label = $this->rapat->verifikator_label ?? 'Telah dilakukan verifikasi';
        $this->verifikator_jabatan = $this->rapat->verifikator_jabatan ?? 'Sekretaris Desa';
        $this->verifikator_nama = $this->rapat->verifikator_nama ?? 'Gusti Ketut Amertayasa, S.M';
        $this->verifikator_nip = $this->rapat->verifikator_nip ?? '';

        $this->pelaksana_tampil = (bool) ($this->rapat->pelaksana_tampil ?? true);
        $this->pelaksana_jabatan = $this->rapat->pelaksana_jabatan ?? 'Kasi Pelayanan';
        $this->pelaksana_nama = $this->rapat->pelaksana_nama ?? 'Ni Made Arini';
        $this->pelaksana_nip = $this->rapat->pelaksana_nip ?? '';

        $this->mengetahui_tampil = (bool) ($this->rapat->mengetahui_tampil ?? true);
        $this->mengetahui_label = $this->rapat->mengetahui_label ?? 'Mengetahui :';
        $this->penandatangan_nama = $this->rapat->penandatangan_nama ?? '';
        $this->penandatangan_jabatan = $this->rapat->penandatangan_jabatan ?? '';
        $this->penandatangan_nip = $this->rapat->penandatangan_nip ?? '';
        $this->penandatangan_lokasi = $this->rapat->penandatangan_lokasi ?? 'Kantor Perbekel Desa Belega';

        $this->status = $this->rapat->status->value;
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
            'pin_kiosk_baru' => 'nullable|string|regex:/^[0-9]{6}$/',
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
            'tempat.required' => 'Tempat pelaksanaan rapat wajib diisi.',
            'penyelenggara.required' => 'Penyelenggara rapat wajib diisi.',
            'pin_kiosk_baru.regex' => 'PIN Kiosk baru harus terdiri dari 6 digit angka.',
            'penandatangan_nama.required' => 'Nama penandatangan wajib diisi.',
            'penandatangan_jabatan.required' => 'Jabatan penandatangan wajib diisi.',
            'penandatangan_lokasi.required' => 'Lokasi pengesahan wajib diisi.',
        ];
    }

    public function ubahStatus(string $statusBaru): void
    {
        $statusEnum = StatusRapat::tryFrom($statusBaru);

        if (!$statusEnum || $this->rapat->status === $statusEnum) {
            return;
        }

        $statusLama = $this->rapat->status;
        $this->rapat->status = $statusEnum;
        $this->rapat->save();
        $this->status = $statusEnum->value;

        // Catat ke audit log
        $aktor = Auth::user()?->profil;
        AuditLog::create([
            'aktor' => $aktor?->id,
            'aksi' => "Mengubah status rapat '{$this->rapat->judul}' dari {$statusLama->value} menjadi {$statusEnum->value}",
            'tabel_target' => 'rapat',
            'baris_id' => $this->rapat->id,
            'rincian' => [
                'status_lama' => $statusLama->value,
                'status_baru' => $statusEnum->value,
            ],
        ]);

        $this->pesanSukses = "Status rapat berhasil diubah menjadi: " . strtoupper($statusEnum->value) . ".";
        $this->dispatch('swal:alert', [
            'title' => 'Status Rapat Diperbarui',
            'text' => "Status agenda rapat berhasil diubah menjadi " . strtoupper($statusEnum->value) . ".",
            'icon' => 'success',
        ]);
    }

    public function simpanPerubahan(): void
    {
        $this->validate();

        $this->rapat->judul = $this->judul;
        $this->rapat->tanggal = $this->tanggal;
        $this->rapat->jam_mulai = $this->jam_mulai ?: null;
        $this->rapat->jam_selesai = $this->jam_selesai ?: null;
        $this->rapat->tempat = $this->tempat;
        $this->rapat->penyelenggara = $this->penyelenggara;
        $this->rapat->catatan = $this->catatan ?: null;
        $this->rapat->retensi_hari = $this->retensi_hari;

        if (!empty($this->pin_kiosk_baru)) {
            $this->rapat->pin_kiosk = Hash::make($this->pin_kiosk_baru);
            $this->pin_kiosk_baru = '';
        }

        $this->rapat->verifikator_tampil = $this->verifikator_tampil;
        $this->rapat->verifikator_label = $this->verifikator_label;
        $this->rapat->verifikator_jabatan = $this->verifikator_jabatan;
        $this->rapat->verifikator_nama = $this->verifikator_nama;
        $this->rapat->verifikator_nip = $this->verifikator_nip ?: '';

        $this->rapat->pelaksana_tampil = $this->pelaksana_tampil;
        $this->rapat->pelaksana_jabatan = $this->pelaksana_jabatan;
        $this->rapat->pelaksana_nama = $this->pelaksana_nama;
        $this->rapat->pelaksana_nip = $this->pelaksana_nip ?: '';

        $this->rapat->mengetahui_tampil = $this->mengetahui_tampil;
        $this->rapat->mengetahui_label = $this->mengetahui_label;
        $this->rapat->penandatangan_nama = $this->penandatangan_nama ?: 'I WAYAN SUDARSANA, S.Sos.';
        $this->rapat->penandatangan_jabatan = $this->penandatangan_jabatan ?: 'Perbekel Belega';
        $this->rapat->penandatangan_nip = $this->penandatangan_nip ?: '';
        $this->rapat->penandatangan_lokasi = $this->penandatangan_lokasi;

        $this->rapat->save();

        // Audit Log
        $aktor = Auth::user()?->profil;
        AuditLog::create([
            'aktor' => $aktor?->id,
            'aksi' => "Memperbarui konfigurasi rapat: {$this->rapat->judul} (Kode: {$this->rapat->kode})",
            'tabel_target' => 'rapat',
            'baris_id' => $this->rapat->id,
            'rincian' => [
                'judul' => $this->rapat->judul,
                'tanggal' => $this->rapat->tanggal->format('Y-m-d'),
                'tempat' => $this->rapat->tempat,
            ],
        ]);

        $this->pesanSukses = 'Perubahan konfigurasi rapat berhasil disimpan.';
        $this->dispatch('swal:alert', [
            'title' => 'Konfigurasi Tersimpan',
            'text' => 'Seluruh perubahan informasi dan pejabat pengesah rapat telah berhasil disimpan.',
            'icon' => 'success',
        ]);
    }

    public function konfirmasiHapus(): void
    {
        $this->authorize('delete', $this->rapat);
        $this->bukaModalHapus = true;
    }

    public function batalHapus(): void
    {
        $this->bukaModalHapus = false;
    }

    public function hapusRapat(RapatService $rapatService)
    {
        $this->authorize('delete', $this->rapat);

        $aktor = Auth::user()?->profil;
        $judulRapat = $this->rapat->judul;

        $rapatService->hapusRapat($this->rapat, $aktor);

        session()->flash('pesanSukses', "Rapat '{$judulRapat}' beserta seluruh berkas tanda tangan & foto berhasil dihapus permanen.");

        return redirect()->route('rapat.index');
    }

    public function render()
    {
        $this->rapat->loadCount(['undangan', 'kehadiran']);

        return view('livewire.rapat.detail-rapat')
            ->layout('layouts.app', ['title' => $this->rapat->judul]);
    }
}
