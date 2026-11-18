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

    public bool $tampilModalUrutan = false;

    public array $daftarUrutan = [];

    public string $cariDiModal = '';

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

    public function naikkanUrutan(string $id): void
    {
        $this->authorize('update', $this->rapat);

        $this->rapikanUrutanInternal();

        $semua = Undangan::where('rapat_id', $this->rapatId)
            ->orderBy('urutan', 'asc')
            ->orderBy('dibuat_pada', 'asc')
            ->get();

        $index = $semua->search(fn ($item) => $item->id === $id);

        if ($index !== false && $index > 0) {
            $target = $semua[$index];
            $prev = $semua[$index - 1];

            $temp = $target->urutan;
            $target->urutan = $prev->urutan;
            $prev->urutan = $temp;

            $target->save();
            $prev->save();

            $this->dispatch('undangan-diperbarui');
            if ($this->tampilModalUrutan) {
                $this->muatDaftarUrutan();
            }
        }
    }

    public function turunkanUrutan(string $id): void
    {
        $this->authorize('update', $this->rapat);

        $this->rapikanUrutanInternal();

        $semua = Undangan::where('rapat_id', $this->rapatId)
            ->orderBy('urutan', 'asc')
            ->orderBy('dibuat_pada', 'asc')
            ->get();

        $index = $semua->search(fn ($item) => $item->id === $id);

        if ($index !== false && $index < count($semua) - 1) {
            $target = $semua[$index];
            $next = $semua[$index + 1];

            $temp = $target->urutan;
            $target->urutan = $next->urutan;
            $next->urutan = $temp;

            $target->save();
            $next->save();

            $this->dispatch('undangan-diperbarui');
            if ($this->tampilModalUrutan) {
                $this->muatDaftarUrutan();
            }
        }
    }

    public function jadikanPalingAtas(string $id): void
    {
        $this->authorize('update', $this->rapat);

        $target = Undangan::where('rapat_id', $this->rapatId)->findOrFail($id);

        $semua = Undangan::where('rapat_id', $this->rapatId)
            ->where('id', '!=', $id)
            ->orderBy('urutan', 'asc')
            ->orderBy('dibuat_pada', 'asc')
            ->get();

        $target->update(['urutan' => 1]);

        $no = 2;
        foreach ($semua as $item) {
            $item->update(['urutan' => $no++]);
        }

        $this->dispatch('undangan-diperbarui');
        if ($this->tampilModalUrutan) {
            $this->muatDaftarUrutan();
        }
        $this->dispatch('swal:alert', [
            'title' => 'Posisi Diperbarui',
            'text' => "'{$target->nama}' kini berada di posisi nomor urut 1.",
            'icon' => 'success',
        ]);
    }

    public function ubahUrutan(string $id, $urutanBaru): void
    {
        $this->authorize('update', $this->rapat);

        $urutanBaru = (int) $urutanBaru;
        $total = Undangan::where('rapat_id', $this->rapatId)->count();
        if ($total <= 1) {
            return;
        }

        $urutanBaru = max(1, min($urutanBaru, $total));

        $this->rapikanUrutanInternal();

        $target = Undangan::where('rapat_id', $this->rapatId)->findOrFail($id);
        $urutanLama = $target->urutan;

        if ($urutanBaru === $urutanLama) {
            return;
        }

        if ($urutanBaru < $urutanLama) {
            Undangan::where('rapat_id', $this->rapatId)
                ->where('id', '!=', $id)
                ->whereBetween('urutan', [$urutanBaru, $urutanLama - 1])
                ->increment('urutan');
        } else {
            Undangan::where('rapat_id', $this->rapatId)
                ->where('id', '!=', $id)
                ->whereBetween('urutan', [$urutanLama + 1, $urutanBaru])
                ->decrement('urutan');
        }

        $target->update(['urutan' => $urutanBaru]);

        $this->rapikanUrutanInternal();
        $this->dispatch('undangan-diperbarui');
        if ($this->tampilModalUrutan) {
            $this->muatDaftarUrutan();
        }
    }

    public function rapikanUrutan(): void
    {
        $this->authorize('update', $this->rapat);

        $this->rapikanUrutanInternal();

        $this->dispatch('undangan-diperbarui');
        if ($this->tampilModalUrutan) {
            $this->muatDaftarUrutan();
        }
        $this->dispatch('swal:alert', [
            'title' => 'Nomor Urut Dirapikan',
            'text' => 'Seluruh nomor urut peserta telah diurutkan rapi mulai dari 1.',
            'icon' => 'success',
        ]);
    }

    protected function rapikanUrutanInternal(): void
    {
        $daftar = Undangan::where('rapat_id', $this->rapatId)
            ->orderBy('urutan', 'asc')
            ->orderBy('dibuat_pada', 'asc')
            ->get();

        $i = 1;
        foreach ($daftar as $item) {
            if ($item->urutan !== $i) {
                $item->update(['urutan' => $i]);
            }
            $i++;
        }
    }

    public function bukaModalUrutan(): void
    {
        $this->authorize('update', $this->rapat);
        $this->rapikanUrutanInternal();
        $this->cariDiModal = '';
        $this->muatDaftarUrutan();
        $this->tampilModalUrutan = true;
    }

    public function tutupModalUrutan(): void
    {
        $this->tampilModalUrutan = false;
        $this->daftarUrutan = [];
        $this->cariDiModal = '';
    }

    public function muatDaftarUrutan(): void
    {
        $query = Undangan::where('rapat_id', $this->rapatId)
            ->orderBy('urutan', 'asc')
            ->orderBy('dibuat_pada', 'asc');

        if (!empty($this->cariDiModal)) {
            $cari = '%' . $this->cariDiModal . '%';
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', $cari)
                    ->orWhere('jabatan', 'like', $cari)
                    ->orWhere('instansi', 'like', $cari);
            });
        }

        $this->daftarUrutan = $query->get(['id', 'nama', 'jabatan', 'instansi', 'urutan'])
            ->toArray();
    }

    public function updatedCariDiModal(): void
    {
        $this->muatDaftarUrutan();
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
