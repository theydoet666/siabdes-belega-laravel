<?php

namespace App\Http\Livewire\Rapat;

use App\Enums\PeranPengguna;
use App\Models\Rapat;
use App\Services\RapatService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarRapat extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $cari = '';

    public string $filterStatus = '';

    // Flash message
    public ?string $pesanSukses = null;

    public ?string $pesanError = null;

    // Modal Hapus State
    public bool $bukaModalHapus = false;

    public ?string $rapatIdHapus = null;

    public ?string $judulRapatHapus = null;

    protected $queryString = [
        'cari' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        if (session()->has('pesanSukses')) {
            $this->pesanSukses = session('pesanSukses');
        }
    }

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function segarkanData(): void
    {
        $this->reset(['cari', 'filterStatus']);
        $this->resetPage();
        $this->pesanSukses = 'Data daftar rapat berhasil disegarkan.';
        $this->dispatch('swal:alert', [
            'title' => 'Daftar Rapat Disegarkan',
            'text' => 'Data agenda rapat dan statistik kehadiran berhasil diperbarui.',
            'icon' => 'success',
        ]);
    }

    public function konfirmasiHapus(string $rapatId): void
    {
        $rapat = Rapat::findOrFail($rapatId);
        $this->authorize('delete', $rapat);

        $this->rapatIdHapus = $rapat->id;
        $this->judulRapatHapus = $rapat->judul;
        $this->bukaModalHapus = true;
    }

    public function batalHapus(): void
    {
        $this->bukaModalHapus = false;
        $this->rapatIdHapus = null;
        $this->judulRapatHapus = null;
    }

    public function eksekusiHapus(RapatService $rapatService): void
    {
        if (! $this->rapatIdHapus) {
            return;
        }

        $rapat = Rapat::findOrFail($this->rapatIdHapus);
        $this->authorize('delete', $rapat);

        $aktor = Auth::user()?->profil;
        $judul = $rapat->judul;

        $rapatService->hapusRapat($rapat, $aktor);

        $this->batalHapus();
        $this->pesanSukses = "Rapat '{$judul}' berhasil dihapus secara permanen.";
        $this->dispatch('swal:alert', [
            'title' => 'Rapat Dihapus',
            'text' => "Agenda rapat '{$judul}' beserta data absensinya telah dihapus permanen.",
            'icon' => 'success',
        ]);
    }

    public function render()
    {
        $query = Rapat::withCount(['undangan', 'kehadiran'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('dibuat_pada', 'desc');

        $profil = Auth::user()?->profil;
        if ($profil && $profil->peran !== PeranPengguna::ADMIN) {
            $query->where('dibuat_oleh', $profil->id);
        }

        if (! empty($this->cari)) {

            $cari = '%'.$this->cari.'%';
            $query->where(function ($q) use ($cari) {
                $q->where('judul', 'like', $cari)
                    ->orWhere('kode', 'like', $cari)
                    ->orWhere('tempat', 'like', $cari)
                    ->orWhere('penyelenggara', 'like', $cari);
            });
        }

        if (! empty($this->filterStatus)) {
            $query->where('status', $this->filterStatus);
        }

        $daftarRapat = $query->paginate(10);

        return view('livewire.rapat.daftar-rapat', [
            'daftarRapat' => $daftarRapat,
        ])->layout('layouts.app', ['title' => 'Daftar Rapat']);
    }
}
