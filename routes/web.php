<?php

use App\Http\Livewire\Auth\HalamanLogin;
use App\Http\Livewire\Pengaturan\HalamanPengaturan;
use App\Http\Livewire\Rapat\DaftarRapat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('rapat.index');
    }
    return redirect()->route('login');
});

// Autentikasi Publik
Route::get('/masuk', HalamanLogin::class)->name('login');

// Autentikasi Keluar
Route::post('/keluar', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

// Rute Terproteksi Operator & Admin
Route::middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/rapat', \App\Http\Livewire\Rapat\DaftarRapat::class)->name('rapat.index');
    Route::get('/rapat/baru', \App\Http\Livewire\Rapat\FormRapat::class)->name('rapat.baru');
    Route::get('/rapat/{id}', \App\Http\Livewire\Rapat\DetailRapat::class)->name('rapat.detail');
    Route::get('/rapat/{id}/cetak', [\App\Http\Controllers\CetakController::class, 'a4'])->name('rapat.cetak');
    Route::get('/rapat/{id}/ekspor-excel', [\App\Http\Controllers\CetakController::class, 'eksporExcel'])->name('rapat.ekspor-excel');
});

// Rute Terproteksi Admin Saja
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/pengaturan', HalamanPengaturan::class)->name('pengaturan');
});

// Mode Kiosk Meja Registrasi (Terkunci PIN)
Route::middleware(['kiosk.pin'])->group(function () {
    Route::match(['get', 'post'], '/kiosk/{kode}', [\App\Http\Controllers\KioskController::class, 'index'])->name('kiosk.index');
});
Route::post('/kiosk/{kode}/kunci', [\App\Http\Controllers\KioskController::class, 'kunci'])->name('kiosk.kunci');

// Registrasi Mandiri Publik via Ponsel Peserta
Route::get('/r/{kode}', [\App\Http\Controllers\RegistrasiMandiriController::class, 'index'])->name('registrasi.mandiri');
Route::get('/r/{kode}/cari', [\App\Http\Controllers\RegistrasiMandiriController::class, 'cari'])->name('registrasi.cari');

// Kebijakan Privasi Publik (UU No. 27/2022 PDP)
Route::view('/privasi', 'privasi')->name('privasi');

