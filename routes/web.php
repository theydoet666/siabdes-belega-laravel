<?php

use App\Http\Controllers\BerkasKehadiranController;
use App\Http\Controllers\BuktiKehadiranController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\RegistrasiMandiriController;
use App\Http\Controllers\VerifikasiDokumenController;
use App\Http\Livewire\Auth\HalamanLogin;
use App\Http\Livewire\Pengaturan\HalamanPengaturan;
use App\Http\Livewire\Rapat\DaftarRapat;
use App\Http\Livewire\Rapat\DetailRapat;
use App\Http\Livewire\Rapat\FormRapat;
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
    Route::get('/rapat', DaftarRapat::class)->name('rapat.index');
    Route::get('/rapat/baru', FormRapat::class)->name('rapat.baru');
    Route::get('/rapat/{id}', DetailRapat::class)->name('rapat.detail');
    Route::get('/rapat/{id}/cetak', [CetakController::class, 'a4'])->name('rapat.cetak');
    Route::get('/rapat/{id}/ekspor-excel', [CetakController::class, 'eksporExcel'])->name('rapat.ekspor-excel');
});

// Rute Terproteksi Admin Saja
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/pengaturan', HalamanPengaturan::class)->name('pengaturan');
});

// Mode Kiosk Meja Registrasi (Terkunci PIN)
Route::middleware(['kiosk.pin', 'throttle:publik'])->group(function () {
    Route::match(['get', 'post'], '/kiosk/{kode}', [KioskController::class, 'index'])->name('kiosk.index');
    Route::get('/kiosk/{kode}/data', [KioskController::class, 'data'])->name('kiosk.data');
    Route::post('/kiosk/{kode}/kunci', [KioskController::class, 'kunci'])->name('kiosk.kunci');
});

// Registrasi Mandiri Publik via Ponsel Peserta, Verifikasi Dokumen & Berkas Terproteksi
Route::middleware(['throttle:publik'])->group(function () {
    Route::get('/r/{kode}', [RegistrasiMandiriController::class, 'index'])->name('registrasi.mandiri');
    Route::get('/r/{kode}/cari', [RegistrasiMandiriController::class, 'cari'])->name('registrasi.cari');
    Route::get('/r/{kode}/bukti/{id}', [BuktiKehadiranController::class, 'bukaDariRapat'])->name('kehadiran.bukti.buka');
    Route::get('/v/{kode}', [VerifikasiDokumenController::class, 'show'])->name('dokumen.verifikasi');
    Route::get('/bukti/{id}', [BuktiKehadiranController::class, 'show'])->name('kehadiran.bukti')->middleware('signed');
    Route::get('/berkas/{jenis}/{id}', [BerkasKehadiranController::class, 'show'])->name('berkas.tampil');

});

// Kebijakan Privasi Publik (UU No. 27/2022 PDP)
Route::view('/privasi', 'privasi')->name('privasi');

// Melayani Berkas Publik Storage (Solusi Handal Shared Hosting Tanpa Symlink)
Route::get('/storage/{path}', function (string $path) {
    if (str_contains($path, '..')) {
        abort(403, 'Akses tidak sah.');
    }

    $filePath = storage_path('app/public/'.$path);

    if (! file_exists($filePath)) {
        abort(404, 'Berkas tidak ditemukan.');
    }

    return response()->file($filePath, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('storage.local');

