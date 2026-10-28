<?php

namespace App\Http\Livewire\Pengaturan;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class TabelAuditLog extends Component
{
    use WithPagination;

    public string $cari = '';

    public string $filterTabel = '';

    public ?string $tanggalMulai = null;

    public ?string $tanggalSelesai = null;

    // Modal detail JSON rincian
    public bool $bukaModalDetail = false;

    public ?AuditLog $logTerpilih = null;

    protected $queryString = [
        'cari' => ['except' => ''],
        'filterTabel' => ['except' => ''],
        'tanggalMulai' => ['except' => null],
        'tanggalSelesai' => ['except' => null],
        'page' => ['except' => 1],
    ];

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTabel(): void
    {
        $this->resetPage();
    }

    public function updatingTanggalMulai(): void
    {
        $this->resetPage();
    }

    public function updatingTanggalSelesai(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->cari = '';
        $this->filterTabel = '';
        $this->tanggalMulai = null;
        $this->tanggalSelesai = null;
        $this->resetPage();
    }

    public function lihatRincian(int $id): void
    {
        $this->logTerpilih = AuditLog::with('pengguna')->find($id);
        if ($this->logTerpilih) {
            $this->bukaModalDetail = true;
        }
    }

    public function tutupModalDetail(): void
    {
        $this->bukaModalDetail = false;
        $this->logTerpilih = null;
    }

    public function render()
    {
        $query = AuditLog::with('pengguna')->orderBy('dibuat_pada', 'desc');

        if (! empty($this->cari)) {
            $cari = '%'.$this->cari.'%';
            $query->where(function ($q) use ($cari) {
                $q->where('aksi', 'like', $cari)
                    ->orWhere('baris_id', 'like', $cari)
                    ->orWhereHas('pengguna', function ($qp) use ($cari) {
                        $qp->where('nama', 'like', $cari)
                            ->orWhere('email', 'like', $cari);
                    });
            });
        }

        if (! empty($this->filterTabel)) {
            $query->where('tabel_target', $this->filterTabel);
        }

        if (! empty($this->tanggalMulai)) {
            $query->whereDate('dibuat_pada', '>=', $this->tanggalMulai);
        }

        if (! empty($this->tanggalSelesai)) {
            $query->whereDate('dibuat_pada', '<=', $this->tanggalSelesai);
        }

        $daftarLog = $query->paginate(15);

        // Ambil daftar tabel_target unik untuk dropdown
        $daftarTabelUnik = AuditLog::select('tabel_target')->distinct()->pluck('tabel_target');

        return view('livewire.pengaturan.tabel-audit-log', [
            'daftarLog' => $daftarLog,
            'daftarTabelUnik' => $daftarTabelUnik,
        ]);
    }
}
