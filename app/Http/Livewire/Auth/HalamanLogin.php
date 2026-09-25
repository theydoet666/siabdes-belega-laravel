<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class HalamanLogin extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    public function mount(): void
    {
        if (Auth::check()) {
            redirect()->route('rapat.index');
        }
    }

    public function masuk()
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.");
            return;
        }

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('email', 'Email atau kata sandi yang Anda masukkan salah.');
            return;
        }

        RateLimiter::clear($throttleKey);

        $user = Auth::user();
        $profil = $user->profil;

        if (!$profil || !$profil->aktif) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            $this->addError('email', 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator Desa.');
            return;
        }

        session()->regenerate();

        return redirect()->intended(route('rapat.index'));
    }

    public function render()
    {
        return view('livewire.auth.halaman-login')
            ->layout('layouts.guest', ['title' => 'Masuk Operator']);
    }
}
