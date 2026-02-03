<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\master\RoomController;
use App\Http\Controllers\Master\ProdiController;
use App\Http\Controllers\Master\CourseController;
use App\Http\Controllers\dashboard\NotifController;
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
use App\Http\Controllers\dokumen\ScheduleController;
use App\Http\Controllers\dokumen\WorkloadController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post');
});

// Route untuk yang Sudah Login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [AnalisisController::class, 'index'])->name('dashboard');
    Route::get('/Pengaturan', [PengaturanController::class, 'index'])->name('setting');
    Route::get('/Pengaturan/Notifikasi', [NotifController::class, 'index'])->name('notifikasi');
    Route::post('/users/sync-siakad', [UserController::class, 'syncSiakad'])->name('users.sync-siakad');
    Route::resource('user', UserController::class)->except('show', 'edit', 'create');

    // Data master
    Route::patch('/periode-akademik/{id}/set-active', [AcademicPeriodController::class, 'setActive'])->name('periode-akademik.set-active');
    Route::post('/study-classes/copy', [StudyClassController::class, 'copyFromPeriod'])->name('study-classes.copy');
    Route::get('/ajax/get-courses-by-class/{classId}', [DistributionController::class, 'getCoursesByClass'])->name('ajax.courses');
    Route::get('/ajax/get-curriculums-by-prodi/{prodiId}', [StudyClassController::class, 'getKurikulumByProdi']);

    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('kurikulum', KurikulumController::class)->except('show', 'edit', 'create');
        Route::resource('kelas', StudyClassController::class)->except('show', 'edit');
        Route::resource('mata-kuliah', CourseController::class)->except('show', 'edit', 'create');
        Route::resource('program-studi', ProdiController::class)->except('show', 'edit', 'create');
        Route::resource('ruangan', RoomController::class)->except('show', 'edit', 'create');
        Route::resource('periode-akademik', AcademicPeriodController::class)->except('show', 'edit', 'create');
    });
    Route::post('kurikulum/sync-siakad', [KurikulumController::class, 'syncSiakad'])->name('kurikulums.sync-siakad');

    Route::prefix('kelas-perkuliahan')->name('kelas-perkuliahan.')->group(function () {
        Route::get('/template', [StudyClassController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [StudyClassController::class, 'import'])->name('import');
        Route::post('/sync-siakad', [StudyClassController::class, 'syncSiakad'])->name('sync-siakad');
    });

    Route::prefix('mata-kuliah')->name('mata-kuliah.')->group(function () {
        Route::get('/template', [CourseController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [CourseController::class, 'import'])->name('import');
        Route::post('/sync-siakad', [CourseController::class, 'syncSiakad'])->name('sync-siakad');
    });



    // Route Distribusi Matkul
    Route::prefix('distribusi-mata-kuliah')->name('distribusi-mata-kuliah.')->group(function () {
        Route::delete('/bulk-destroy', [DistributionController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::get('/template', [DistributionController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [DistributionController::class, 'import'])->name('import');
        Route::post('/submit', [DistributionController::class, 'submitToKaprodi'])->name('submit');
        Route::get('/doc/{document}', [DistributionController::class, 'show'])->name('show-doc');
        Route::get('/{id}/print', [DistributionController::class, 'printPDF'])->name('print');
    });
    Route::resource('distribusi-mata-kuliah', DistributionController::class)->except('show');
    Route::prefix('distributions')->name('distributions.')->group(function () {
        Route::post('/generate', [DistributionController::class, 'generate'])->name('generate');
        Route::get('/export/{period_id}', [DistributionController::class, 'export'])->name('export');
        Route::post('/import-update', [DistributionController::class, 'importUpdate'])->name('import-update');
    });

    // Beban kerja dosen
    Route::prefix('beban-kerja-dosen')->name('beban-kerja-dosen.')->group(function () {
        // Route::get('/doc/{document}', [WorkloadController::class, 'show'])->name('beban-kerja-dosen.show-doc');
        // Route::post('/submit', [WorkloadController::class, 'submitValidation'])->name('beban-kerja-dosen.submit');
        Route::get('/', [WorkloadController::class, 'index'])->name('index');
        Route::post('/generate', [WorkloadController::class, 'generate'])->name('generate');
        Route::put('/update-all', [WorkloadController::class, 'updateAllActivities'])->name('update-all');
        Route::get('/rekapitulasi', [WorkloadController::class, 'rekapIndex'])->name('rekap');
        Route::post('/submit', [WorkloadController::class, 'submit'])->name('submit');
        Route::get('/document/{id}', [WorkloadController::class, 'showDoc'])->name('show-doc');
        Route::get('/document/{id}/print', [WorkloadController::class, 'printDoc'])->name('print-doc');
    });
    // Route::resource('beban-kerja-dosen', WorkloadController::class);

    // jadwal Perkuliahan
    Route::prefix('jadwal-perkuliahan')->name('jadwal-perkuliahan.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::post('/submit', [ScheduleController::class, 'submit'])->name('submit');
        Route::get('/get-events', [ScheduleController::class, 'getEvents'])->name('get-events');
        Route::post('/store', [ScheduleController::class, 'store'])->name('store');
        Route::put('/{id}', [ScheduleController::class, 'update'])->name('update');
        Route::patch('/{id}/resize', [ScheduleController::class, 'resize'])->name('resize');
        Route::delete('/{id}', [ScheduleController::class, 'destroy'])->name('destroy');
        Route::get('/doc', [ScheduleController::class, 'show'])->name('show');
        Route::post('/auto-generate', [ScheduleController::class, 'autoGenerate'])->name('auto-generate');
        Route::get('{id}/print', [ScheduleController::class, 'printPDF'])->name('print');
    });

    // Route Kalender Akademik
    Route::resource('kalender-akademik', AcademicCalendarController::class)->except('show', 'edit', 'create');
    Route::prefix('kalender-akademik')->name('kalender-akademik.')->group(function () {
        Route::get('doc/{document}', [AcademicCalendarController::class, 'show'])->name('show-doc');
        Route::post('submit', [AcademicCalendarController::class, 'submitValidation'])->name('submit');
        Route::get('events', [AcademicCalendarController::class, 'getEvents'])->name('events');
        Route::get('{id}/print', [AcademicCalendarController::class, 'printPdf'])->name('print');
    });

    //dokumen aproval
    Route::resource('documents', AprovalDocumentController::class)->only(['index', 'show', 'destroy']);
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::post('/{id}/approve', [AprovalDocumentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [AprovalDocumentController::class, 'reject'])->name('reject');
        Route::post('/submit', [AprovalDocumentController::class, 'submit'])->name('submit');
    });
});
