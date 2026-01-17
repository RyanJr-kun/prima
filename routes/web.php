<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\bkd\BkdController;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\dashboard\NotifController;
use App\Http\Controllers\dokumen\DokumenController;
use App\Http\Controllers\dashboard\AnalisisController;
use App\Http\Controllers\authentications\AuthController;
use App\Http\Controllers\authentications\UserController;
use App\Http\Controllers\jadwal\JadwalController;
use App\Http\Controllers\kurikulum\KurikulumController;
use App\Http\Controllers\ruang\RuanganController;
use App\Http\Controllers\setting\PengaturanController;

Route::middleware('guest')->group(function () {
    Route::get('/auth/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post');
});

// Route untuk yang Sudah Login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [AnalisisController::class, 'index'])->name('dashboard');
    Route::get('/Notifikasi', [NotifController::class, 'index'])->name('notifikasi');
    Route::get('/Dokumen', [DokumenController::class, 'index'])->name('dokumen');
    Route::get('/BKD', [BkdController::class, 'index'])->name('bkd');
    Route::get('/Jadwal', [JadwalController::class, 'index'])->name('jadwal');
    Route::get('/Ruangan', [RuanganController::class, 'index'])->name('ruang');
    Route::get('/Kurikulum', [KurikulumController::class, 'index'])->name('kurikulum');
    Route::get('/Pengaturan', [PengaturanController::class, 'index'])->name('setting');

    Route::resource('/user', UserController::class)
        ->except('show', 'edit', 'create')
        ->names([
            'index' => 'user',
            'store' => 'user.store',
            'update' => 'user.update',
            'destroy' => 'user.destroy'
        ]);
});
