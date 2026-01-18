<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\bkd\BkdController;
use App\Http\Controllers\Master\ProdiController;
use App\Http\Controllers\jadwal\JadwalController;
use App\Http\Controllers\Master\CourseController;
use App\Http\Controllers\ruang\RuanganController;
use App\Http\Controllers\dashboard\NotifController;
use App\Http\Controllers\dokumen\DokumenController;
use App\Http\Controllers\Master\KurikulumController;
use App\Http\Controllers\Master\StudyClassController;
use App\Http\Controllers\dashboard\AnalisisController;
use App\Http\Controllers\setting\PengaturanController;
use App\Http\Controllers\authentications\AuthController;
use App\Http\Controllers\authentications\UserController;

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
    Route::get('/Pengaturan', [PengaturanController::class, 'index'])->name('setting');
    Route::resource('/user', UserController::class)->except('show', 'edit', 'create');
    
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('kurikulum', KurikulumController::class);
        Route::resource('kelas', StudyClassController::class);
        Route::resource('mata-kuliah', CourseController::class);
        route::resource('program-studi', ProdiController::class);
    });
});
