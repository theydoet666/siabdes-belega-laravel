<?php

namespace App\Http\Livewire\Pengaturan;

use App\Models\AuditLog;
use App\Models\Rapat;
use App\Services\RetensiFotoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PengaturanRetensi extends Component
{
    use WithPagination;

    public string $cari = '';

    public string $filterStatus = ''; // '' (semua), 'aktif' (belum dihapus), 'kadaluarsa', 'dibersihkan'

    // Inline edit retensi
    public ?string $editRapatId = null;

    public int $editRetensiHari = 90;

    // Flash feedback
    public ?string $pesanSukses = null;

    public ?string $pesanError = null;

    // Modal konfirmasi pembersihan
    public bool $bukaModalKonfirmasi = false;

    public ?string $rapatTargetId = null;

    public ?string $rapatTargetJudul = null;

    protected $queryString = [
        'cari' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function mulaiEditRetensi(string $rapatId, int $currentRetensi): void
    {
        $this->editRapatId = $rapatId;
        $this->editRetensiHari = $currentRetensi;
    }

    public function batalEditRetensi(): void
    {
        $this->editRapatId = null;
    }

    public function simpanRetensi(string $rapatId): void
    {
        $this->validate([
            'editRetensiHari' => 'required|integer|min:1|max:365',
        ]);

        $rapat = Rapat::findOrFail($rapatId);
        $retensiLama = $rapat->retensi_hari;
        $rapat->retensi_hari = $this->editRetensiHari;
        $rapat->save();

        AuditLog::create([
            'aktor' => Auth::user()?->profil?->id,
            'aksi' => "Mengubah masa retensi foto rapat '{$rapat->judul}' dari {$retensiLama} hari menjadi {$this->editRetensiHari} hari",
            'tabel_target' => 'rapat',
            'baris_id' => $rapat->id,
            'rincian' => [
                'rapat_id' => $rapat->id,
                'retensi_lama' => $retensiLama,
                'retensi_baru' => $this->editRetensiHari,
            ],
        ]);

        $this->editRapatId = null;
        $this->pesanSukses = "Masa retensi untuk rapat '{$rapat->judul}' berhasil diubah menjadi {$this->editRetensiHari} hari.";
    }

    public function konfirmasiBersihkan(string $rapatId): void
    {
        $rapat = Rapat::findOrFail($rapatId);
        $this->rapatTargetId = $rapatId;
        $this->rapatTargetJudul = $rapat->judul;
        $this->bukaModalKonfirmasi = true;
    }

    public function eksekusiBersihkanFoto(RetensiFotoService $retensiService): void
    {
        if (! $this->rapatTargetId) {
            return;
        }

        $fotoDihapus = $retensiService->hapusFotoRapat($this->rapatTargetId);

        $this->pesanSukses = "Berhasil! {$fotoDihapus} file foto kehadiran rapat '{$this->rapatTargetJudul}' telah dibersihkan secara permanen.";
        $this->bukaModalKonfirmasi = false;
        $this->rapatTargetId = null;
        $this->rapatTargetJudul = null;
    }

    public function bersihkanSemuaKadaluarsa(RetensiFotoService $retensiService): void
    {
        $hasil = $retensiService->bersihkanFotoKadaluarsa();

        if ($hasil['rapat_diproses'] > 0) {
            $this->pesanSukses = "Pembersihan selesai! {$hasil['rapat_diproses']} rapat diproses dan total {$hasil['total_foto_dihapus']} file foto berhasil dihapus.";
        } else {
            $this->pesanSukses = 'Tidak ada foto rapat yang melewati batas retensi saat ini.';
        }
    }

    public function render()
    {
        $sekarang = Carbon::today();

        // Query daftar rapat dengan relasi kehadiran
        $query = Rapat::withCount(['kehadiran as foto_count' => function ($q) {
            $q->whereNotNull('foto_path')->where('foto_path', '!=', '');
        }]);

        if (! empty($this->cari)) {
            $cari = '%'.$this->cari.'%';
            $query->where(function ($q) use ($cari) {
                $q->where('judul', 'like', $cari)
                    ->orWhere('kode', 'like', $cari);
            });
        }

        if ($this->filterStatus === 'aktif') {
            $query->whereNull('foto_dihapus_pada');
        } elseif ($this->filterStatus === 'dibersihkan') {
            $query->whereNotNull('foto_dihapus_pada');
        }

        $daftarRapat = $query->orderBy('tanggal', 'desc')->paginate(10);

        // Statistik
        $totalRapat = Rapat::count();
        $totalDibersihkan = Rapat::whereNotNull('foto_dihapus_pada')->count();
        $totalMenunggu = Rapat::whereNull('foto_dihapus_pada')->count();

        return view('livewire.pengaturan.pengaturan-retensi', [
            'daftarRapat' => $daftarRapat,
            'totalRapat' => $totalRapat,
            'totalDibersihkan' => $totalDibersihkan,
            'totalMenunggu' => $totalMenunggu,
            'sekarang' => $sekarang,
        ]);
    }
}
