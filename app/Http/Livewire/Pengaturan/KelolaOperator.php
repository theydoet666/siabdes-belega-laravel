<?php

namespace App\Http\Livewire\Pengaturan;

use App\Enums\PeranPengguna;
use App\Models\Profil;
use App\Services\OperatorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;

class KelolaOperator extends Component
{
    use WithPagination;

    // Search and filter
    public string $cari = '';

    public string $filterPeran = '';

    // Modal tambah operator
    public bool $bukaModalTambah = false;

    public string $nama = '';

    public string $email = '';

    public string $password = '';

    public string $peran = 'operator';

    // Flash message state
    public ?string $pesanSukses = null;

    public ?string $pesanError = null;

    protected $queryString = [
        'cari' => ['except' => ''],
        'filterPeran' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPeran(): void
    {
        $this->resetPage();
    }

    public function bukaModal(): void
    {
        $this->resetForm();
        $this->bukaModalTambah = true;
    }

    public function tutupModal(): void
    {
        $this->bukaModalTambah = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->nama = '';
        $this->email = '';
        $this->password = '';
        $this->peran = 'operator';
        $this->resetValidation();
        $this->pesanSukses = null;
        $this->pesanError = null;
    }

    public function simpanOperator(OperatorService $operatorService)
    {
        $this->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', Password::min(12)->letters()->numbers()],
            'peran' => ['required', Rule::in(['admin', 'operator'])],
        ], [
            'nama.required' => 'Nama operator wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar di sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 12 karakter serta harus mengandung kombinasi huruf dan angka.',
            'peran.required' => 'Peran pengguna wajib dipilih.',
            'peran.in' => 'Peran pengguna yang dipilih tidak valid.',
        ]);

        $peranEnum = $this->peran === 'admin' ? PeranPengguna::ADMIN : PeranPengguna::OPERATOR;
        $aktor = Auth::user()?->profil;

        $operatorService->tambahOperator(
            email: $this->email,
            password: $this->password,
            nama: $this->nama,
            peran: $peranEnum,
            aktor: $aktor
        );

        $this->tutupModal();
        $this->pesanSukses = "Operator {$this->nama} berhasil ditambahkan.";
    }

    public function gantiPeran(string $profilId, OperatorService $operatorService): void
    {
        $profil = Profil::findOrFail($profilId);
        $aktor = Auth::user()?->profil;

        // Cegah admin mengubah perannya sendiri
        if ($profil->id === $aktor?->id) {
            $this->pesanError = 'Anda tidak dapat mengubah peran akun Anda sendiri.';

            return;
        }

        $peranBaru = $profil->peran === PeranPengguna::ADMIN ? PeranPengguna::OPERATOR : PeranPengguna::ADMIN;
        $operatorService->ubahPeran($profil, $peranBaru, $aktor);

        $this->pesanSukses = "Peran {$profil->nama} berhasil diubah menjadi ".($peranBaru === PeranPengguna::ADMIN ? 'Administrator' : 'Operator').'.';
    }

    public function toggleStatus(string $profilId, OperatorService $operatorService): void
    {
        $profil = Profil::findOrFail($profilId);
        $aktor = Auth::user()?->profil;

        // Cegah admin menonaktifkan akun sendiri
        if ($profil->id === $aktor?->id) {
            $this->pesanError = 'Anda tidak dapat menonaktifkan akun Anda sendiri.';

            return;
        }

        $operatorService->toggleStatus($profil, $aktor);
        $statusTeks = $profil->aktif ? 'diaktifkan' : 'dinonaktifkan';

        $this->pesanSukses = "Akun operator {$profil->nama} berhasil {$statusTeks}.";
    }

    public function render()
    {
        $query = Profil::with('user')
            ->orderBy('dibuat_pada', 'desc');

        if (! empty($this->cari)) {
            $cari = '%'.$this->cari.'%';
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', $cari)
                    ->orWhere('email', 'like', $cari);
            });
        }

        if (! empty($this->filterPeran)) {
            $query->where('peran', $this->filterPeran);
        }

        $operators = $query->paginate(10);

        return view('livewire.pengaturan.kelola-operator', [
            'operators' => $operators,
        ]);
    }
}
