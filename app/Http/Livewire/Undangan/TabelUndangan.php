<?php

namespace App\Http\Livewire\Undangan;

use App\Models\AuditLog;
use App\Models\Rapat;
use App\Models\Undangan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TabelUndangan extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $rapatId;

    public ?Rapat $rapat = null;

    public string $cari = '';

    public string $filterSumber = '';

    public string $filterKehadiran = '';

    public ?string $pesanSukses = null;

    public ?string $pesanError = null;

    protected $queryString = [
        'cari' => ['except' => ''],
        'filterSumber' => ['except' => ''],
        'filterKehadiran' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(string $rapatId): void
    {
        $this->rapatId = $rapatId;
        $this->rapat = Rapat::findOrFail($rapatId);
        $this->authorize('view', $this->rapat);
    }

    #[On('undangan-diperbarui')]
    #[On('undangan-diperbarui-tabel')]
    public function tanganiUndanganDiperbarui(?string $pesan = null): void
    {
        if ($pesan) {
            $this->pesanSukses = $pesan;
        }
        $this->resetPage();
    }

    public function segarkanData(): void
    {
        $this->resetPage();
        $this->dispatch('undangan-diperbarui');
        $this->dispatch('swal:alert', [
            'title' => 'Daftar Undangan Disegarkan',
            'text' => 'Daftar peserta dan status kehadiran terkini telah diperbarui.',
            'icon' => 'success',
        ]);
    }

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSumber(): void
    {
        $this->resetPage();
    }

    public function updatingFilterKehadiran(): void
    {
        $this->resetPage();
    }

    public function hapusUndangan(string $id): void
    {
        $this->authorize('update', $this->rapat);

        $undangan = Undangan::where('rapat_id', $this->rapatId)->findOrFail($id);

        $namaUndangan = $undangan->nama;
        $aktor = Auth::user()?->profil;

        AuditLog::create([
            'aktor' => $aktor?->id,
            'aksi' => "Menghapus undangan: {$namaUndangan} dari rapat '{$this->rapat?->judul}'",
            'tabel_target' => 'undangan',
            'baris_id' => $undangan->id,
            'rincian' => [
                'rapat_id' => $this->rapatId,
                'nama' => $namaUndangan,
                'sumber' => $undangan->sumber->value,
            ],
        ]);

        $undangan->delete();

        $this->dispatch('undangan-diperbarui');
        $this->pesanSukses = "Undangan {$namaUndangan} berhasil dihapus.";
        $this->dispatch('swal:alert', [
            'title' => 'Undangan Dihapus',
            'text' => "Peserta '{$namaUndangan}' telah dihapus dari daftar undangan rapat.",
            'icon' => 'success',
        ]);
    }

    public function render()
    {
        $query = Undangan::with('kehadiran')
            ->where('rapat_id', $this->rapatId)
            ->orderBy('urutan', 'asc')
            ->orderBy('dibuat_pada', 'asc');

        if (! empty($this->cari)) {
            $cari = '%'.$this->cari.'%';
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', $cari)
                    ->orWhere('jabatan', 'like', $cari)
                    ->orWhere('instansi', 'like', $cari)
                    ->orWhere('hp', 'like', $cari);
            });
        }

        if (! empty($this->filterSumber)) {
            $query->where('sumber', $this->filterSumber);
        }

        if ($this->filterKehadiran === 'hadir') {
            $query->has('kehadiran');
        } elseif ($this->filterKehadiran === 'belum') {
            $query->doesntHave('kehadiran');
        }

        $daftarUndangan = $query->paginate(15);

        return view('livewire.undangan.tabel-undangan', [
            'daftarUndangan' => $daftarUndangan,
        ]);
    }
}
