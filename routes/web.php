<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\bkd\BkdController;
use App\Http\Controllers\master\RoomController;
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
use App\Http\Controllers\dokumen\DistributionController;

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
    Route::post('/users/sync-siakad', [UserController::class, 'syncSiakad'])->name('users.sync-siakad');
    Route::resource('user', UserController::class)->except('show', 'edit', 'create');
    
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('kurikulum', KurikulumController::class)->except('show', 'edit', 'create');
        Route::resource('kelas', StudyClassController::class)->except('show', 'edit', 'create');
        Route::resource('mata-kuliah', CourseController::class)->except('show', 'edit', 'create');
        route::resource('program-studi', ProdiController::class)->except('show', 'edit', 'create');
        route::resource('ruangan', RoomController::class)->except('show', 'edit', 'create');
    });

    Route::get('course-distributions/template', [DistributionController::class, 'downloadTemplate'])->name('course-distributions.template');
    Route::post('course-distributions/import', [DistributionController::class, 'import'])->name('course-distributions.import');
    Route::resource('distributions', DistributionController::class);
    Route::get('/ajax/get-courses-by-class/{classId}', [DistributionController::class, 'getCoursesByClass'])->name('ajax.courses');
    Route::get('/ajax/get-curriculums-by-prodi/{prodiId}', [StudyClassController::class, 'getKurikulumByProdi']);
});
