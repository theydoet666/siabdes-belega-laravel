<?php

namespace App\Http\Livewire\Pengaturan;

use App\Models\AuditLog;
use App\Models\PengaturanSistem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class PengaturanIdentitas extends Component
{
    use WithFileUploads;

    public string $nama_sistem = 'SIABDES Belega';

    public string $subjudul_sistem = 'Sistem Absensi Rapat Kantor Desa Belega';

    public string $nama_desa = 'Pemerintah Desa Belega';

    public string $alamat_desa = 'Jalan Raya Belega, Blahbatuh, Gianyar, Bali — Kode Pos 80581';

    public string $kecamatan = 'Kecamatan Blahbatuh';

    public string $kabupaten = 'Kabupaten Gianyar';

    public string $provinsi = 'Bali';

    public ?string $logo_url = null;

    public ?string $favicon_url = null;

    // Temporary uploaded files
    public $logoBaru = null;

    public $faviconBaru = null;

    public ?string $pesanSukses = null;

    public ?string $pesanError = null;

    public function mount(): void
    {
        $pengaturan = PengaturanSistem::first();
        if ($pengaturan) {
            $this->nama_sistem = $pengaturan->nama_sistem ?? $this->nama_sistem;
            $this->subjudul_sistem = $pengaturan->subjudul_sistem ?? $this->subjudul_sistem;
            $this->nama_desa = $pengaturan->nama_desa ?? $this->nama_desa;
            $this->alamat_desa = $pengaturan->alamat_desa ?? $this->alamat_desa;
            $this->kecamatan = $pengaturan->kecamatan ?? $this->kecamatan;
            $this->kabupaten = $pengaturan->kabupaten ?? $this->kabupaten;
            $this->provinsi = $pengaturan->provinsi ?? $this->provinsi;
            $this->logo_url = $pengaturan->logo_url;
            $this->favicon_url = $pengaturan->favicon_url;
        }
    }

    public function hapusLogo(): void
    {
        $pengaturan = PengaturanSistem::firstOrNew(['id' => 1]);
        $pengaturan->logo_url = null;
        $pengaturan->save();

        $this->logo_url = null;
        $this->logoBaru = null;
        $this->pesanSukses = 'Logo instansi berhasil dihapus (kembali ke default).';
        $this->dispatch('swal:alert', [
            'title' => 'Logo Direset',
            'text' => 'Logo instansi telah berhasil dihapus dan dikembalikan ke default.',
            'icon' => 'success',
        ]);
    }

    public function hapusFavicon(): void
    {
        $pengaturan = PengaturanSistem::firstOrNew(['id' => 1]);
        $pengaturan->favicon_url = null;
        $pengaturan->save();

        $this->favicon_url = null;
        $this->faviconBaru = null;
        $this->pesanSukses = 'Favicon sistem berhasil dihapus (kembali ke default).';
        $this->dispatch('swal:alert', [
            'title' => 'Favicon Direset',
            'text' => 'Favicon sistem telah berhasil dihapus dan dikembalikan ke default.',
            'icon' => 'success',
        ]);
    }

    public function getLogoPreviewProperty(): ?string
    {
        if (! $this->logoBaru) {
            return null;
        }
        try {
            if (method_exists($this->logoBaru, 'isPreviewable') && $this->logoBaru->isPreviewable()) {
                return $this->logoBaru->temporaryUrl();
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public function getFaviconPreviewProperty(): ?string
    {
        if (! $this->faviconBaru) {
            return null;
        }
        try {
            if (method_exists($this->faviconBaru, 'isPreviewable') && $this->faviconBaru->isPreviewable()) {
                return $this->faviconBaru->temporaryUrl();
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public function simpan(): void
    {
        $this->validate([
            'nama_sistem' => 'required|string|max:255',
            'subjudul_sistem' => 'required|string|max:255',
            'nama_desa' => 'required|string|max:255',
            'alamat_desa' => 'required|string|max:500',
            'kecamatan' => 'required|string|max:255',
            'kabupaten' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'logoBaru' => 'nullable|image|max:2048',
            'faviconBaru' => 'nullable|file|mimes:ico,png,jpg,jpeg,svg,webp|max:1024',
        ]);

        $profilId = Auth::user()?->profil?->id;

        $pengaturan = PengaturanSistem::firstOrNew(['id' => 1]);
        $pengaturan->nama_sistem = $this->nama_sistem;
        $pengaturan->subjudul_sistem = $this->subjudul_sistem;
        $pengaturan->nama_desa = $this->nama_desa;
        $pengaturan->alamat_desa = $this->alamat_desa;
        $pengaturan->kecamatan = $this->kecamatan;
        $pengaturan->kabupaten = $this->kabupaten;
        $pengaturan->provinsi = $this->provinsi;

        // Proses upload Logo baru
        if ($this->logoBaru) {
            $pathLogo = $this->logoBaru->store('identitas', 'public');
            $pengaturan->logo_url = '/storage/'.$pathLogo;
            $this->logo_url = $pengaturan->logo_url;
            $this->logoBaru = null;
        }

        // Proses upload Favicon baru
        if ($this->faviconBaru) {
            $pathFavicon = $this->faviconBaru->store('identitas', 'public');
            $pengaturan->favicon_url = '/storage/'.$pathFavicon;
            $this->favicon_url = $pengaturan->favicon_url;
            $this->faviconBaru = null;
        }

        $pengaturan->diperbarui_oleh = $profilId;
        $pengaturan->save();

        AuditLog::create([
            'aktor' => $profilId,
            'aksi' => 'Memperbarui konfigurasi identitas instansi & kop dinas sistem',
            'tabel_target' => 'pengaturan_sistem',
            'baris_id' => '1',
            'rincian' => [
                'nama_sistem' => $this->nama_sistem,
                'nama_desa' => $this->nama_desa,
                'kecamatan' => $this->kecamatan,
                'kabupaten' => $this->kabupaten,
                'alamat_desa' => $this->alamat_desa,
                'logo_url' => $this->logo_url,
                'favicon_url' => $this->favicon_url,
            ],
        ]);

        $this->pesanSukses = 'Pengaturan identitas desa dan kop dinas berhasil disimpan.';
        $this->dispatch('swal:alert', [
            'title' => 'Berhasil Disimpan',
            'text' => 'Pengaturan identitas desa dan kop dinas berhasil diperbarui.',
            'icon' => 'success',
        ]);
    }

    public function render()
    {
        return view('livewire.pengaturan.pengaturan-identitas');
    }
}
