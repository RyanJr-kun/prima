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
use App\Http\Controllers\Master\AcademicPeriodController;
use App\Http\Controllers\dokumen\AcademicCalendarController;
use App\Http\Controllers\dokumen\AprovalDocumentController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post');
});

// Route untuk yang Sudah Login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [AnalisisController::class, 'index'])->name('dashboard');
    Route::get('/beban-kerja-dosen', [BkdController::class, 'index'])->name('beban-kerja-dosen');
    Route::get('/Jadwal', [JadwalController::class, 'index'])->name('jadwal');
    Route::get('/Ruangan', [RuanganController::class, 'index'])->name('ruang');
    Route::get('/Pengaturan', [PengaturanController::class, 'index'])->name('setting');
    Route::get('/Pengaturan/Notifikasi', [NotifController::class, 'index'])->name('notifikasi');
    Route::post('/users/sync-siakad', [UserController::class, 'syncSiakad'])->name('users.sync-siakad');
    Route::resource('user', UserController::class)->except('show', 'edit', 'create');

    Route::get('/ajax/get-courses-by-class/{classId}', [DistributionController::class, 'getCoursesByClass'])->name('ajax.courses');
    Route::get('/ajax/get-curriculums-by-prodi/{prodiId}', [StudyClassController::class, 'getKurikulumByProdi']);
    Route::patch('/periode-akademik/{id}/set-active', [AcademicPeriodController::class, 'setActive'])->name('periode-akademik.set-active');

    Route::post('/study-classes/copy', [StudyClassController::class, 'copyFromPeriod'])->name('study-classes.copy');

    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('kurikulum', KurikulumController::class)->except('show', 'edit', 'create');
        Route::resource('kelas', StudyClassController::class)->except('show', 'edit');
        Route::resource('mata-kuliah', CourseController::class)->except('show', 'edit', 'create');
        Route::resource('program-studi', ProdiController::class)->except('show', 'edit', 'create');
        Route::resource('ruangan', RoomController::class)->except('show', 'edit', 'create');
        Route::resource('periode-akademik', AcademicPeriodController::class)->except('show', 'edit', 'create');
    });

    Route::prefix('distributions')->name('distributions.')->group(function () {
        Route::post('/generate', [DistributionController::class, 'generate'])->name('generate');
        Route::get('/export/{period_id}', [DistributionController::class, 'export'])->name('export');
        Route::post('/import-update', [DistributionController::class, 'importUpdate'])->name('import-update');
    });
    Route::get('distribusi-matkul/template', [DistributionController::class, 'downloadTemplate'])->name('distribusi-matkul.template');
    Route::post('distribusi-matkul/import', [DistributionController::class, 'import'])->name('distribusi-matkul.import');
    Route::post('/distribusi/submit', [DistributionController::class, 'submitToKaprodi'])->name('distribusi.submit');
    Route::resource('distribusi-mata-kuliah', DistributionController::class);

    Route::get('kelas-perkuliahan/template', [StudyClassController::class, 'downloadTemplate'])->name('kelas-perkuliahan.template');
    Route::post('kelas-perkuliahan/import', [StudyClassController::class, 'import'])->name('kelas-perkuliahan.import');
    Route::get('mata-kuliah/template', [CourseController::class, 'downloadTemplate'])->name('mata-kuliah.template');
    Route::post('mata-kuliah/import', [CourseController::class, 'import'])->name('mata-kuliah.import');

    Route::resource('kalender-akademik', AcademicCalendarController::class)->except('show', 'edit', 'create');

    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/{id}/print', [AprovalDocumentController::class, 'printPdf'])->name('print');
        Route::post('/{id}/approve', [AprovalDocumentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [AprovalDocumentController::class, 'reject'])->name('reject');
        Route::post('/submit', [AprovalDocumentController::class, 'submit'])->name('submit');
    });

    Route::resource('dokumen', AprovalDocumentController::class)
        ->parameters(['dokumen' => 'document'])
        ->names([
            'index'   => 'documents.index',
            'show'    => 'documents.show',
            'destroy' => 'documents.destroy',
        ])
        ->only(['index', 'show', 'destroy']);
});
