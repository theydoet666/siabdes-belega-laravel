<?php

namespace App\Http\Livewire\Undangan;

use App\Enums\SumberUndangan;
use App\Models\AuditLog;
use App\Models\Rapat;
use App\Models\Undangan;
use App\Repositories\UndanganRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class ModalPilihRiwayatUndangan extends Component
{
    use AuthorizesRequests;

    public ?string $rapatId = null;

    public ?Rapat $rapat = null;

    public bool $tampil = false;

    public string $cari = '';

    public array $terpilih = []; // array of indices

    public ?string $pesanInfo = null;

    #[On('buka-modal-riwayat')]
    public function buka(string $rapatId): void
    {
        $this->rapatId = $rapatId;
        $this->rapat = Rapat::findOrFail($rapatId);
        $this->authorize('update', $this->rapat);
        $this->reset(['cari', 'terpilih', 'pesanInfo']);
        $this->tampil = true;
    }

    public function tutup(): void
    {
        $this->tampil = false;
        $this->reset(['cari', 'terpilih', 'pesanInfo', 'rapatId', 'rapat']);
    }

    public function toggleSemua(UndanganRepository $repo): void
    {
        $daftar = $repo->riwayatUnik($this->cari);
        if (count($this->terpilih) === count($daftar)) {
            $this->terpilih = [];
        } else {
            $this->terpilih = range(0, count($daftar) - 1);
        }
    }

    public function salinTerpilih(UndanganRepository $repo)
    {
        $this->authorize('update', $this->rapat);

        if (empty($this->terpilih)) {

            $this->pesanInfo = 'Pilih minimal satu nama undangan untuk disalin.';

            return;
        }

        $daftar = $repo->riwayatUnik($this->cari);
        $aktor = Auth::user()?->profil;

        $disalin = DB::transaction(function () use ($daftar) {
            $maxUrutan = (int) Undangan::where('rapat_id', $this->rapatId)->max('urutan');
            $count = 0;

            foreach ($this->terpilih as $idx) {
                if (! isset($daftar[$idx])) {
                    continue;
                }

                $item = $daftar[$idx];
                $maxUrutan++;

                Undangan::create([
                    'id' => (string) Str::uuid(),
                    'rapat_id' => $this->rapatId,
                    'nama' => $item->nama,
                    'jabatan' => $item->jabatan ?? '',
                    'instansi' => $item->instansi ?? '',
                    'hp' => $item->hp ?? '',
                    'sumber' => SumberUndangan::IMPORT,
                    'urutan' => $maxUrutan,
                ]);

                $count++;
            }

            return $count;
        });

        AuditLog::create([
            'aktor' => $aktor?->id,
            'aksi' => "Menyalin {$disalin} undangan dari Bank Riwayat ke rapat '{$this->rapat?->judul}'",
            'tabel_target' => 'undangan',
            'baris_id' => $this->rapatId,
            'rincian' => [
                'rapat_id' => $this->rapatId,
                'jumlah_disalin' => $disalin,
            ],
        ]);

        $this->dispatch('undangan-diperbarui', pesan: "Berhasil menambahkan {$disalin} undangan dari Bank Riwayat.");
        $this->dispatch('swal:alert', [
            'title' => 'Riwayat Disalin',
            'text' => "Sebanyak {$disalin} undangan dari Bank Riwayat telah berhasil ditambahkan ke rapat.",
            'icon' => 'success',
        ]);
        $this->tutup();
    }

    public function render(UndanganRepository $repo)
    {
        $riwayat = $this->tampil ? $repo->riwayatUnik($this->cari) : collect();

        return view('livewire.undangan.modal-pilih-riwayat-undangan', [
            'riwayat' => $riwayat,
        ]);
    }
}
