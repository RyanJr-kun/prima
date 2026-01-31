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
use App\Http\Controllers\dokumen\ScheduleController;
use App\Http\Controllers\WorkloadController;

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
    Route::resource('distribusi-mata-kuliah', DistributionController::class)->except('show');
    Route::prefix('distributions')->name('distributions.')->group(function () {
        Route::post('/generate', [DistributionController::class, 'generate'])->name('generate');
        Route::get('/export/{period_id}', [DistributionController::class, 'export'])->name('export');
        Route::post('/import-update', [DistributionController::class, 'importUpdate'])->name('import-update');
    });
    Route::get('distribusi-matkul/template', [DistributionController::class, 'downloadTemplate'])->name('distribusi-matkul.template');
    Route::post('distribusi-matkul/import', [DistributionController::class, 'import'])->name('distribusi-matkul.import');
    Route::post('/distribusi/submit', [DistributionController::class, 'submitToKaprodi'])->name('distribusi.submit');
    Route::get('distribusi-matkul/doc/{document}', [DistributionController::class, 'show'])->name('distribusi-matkul.show-doc');
    Route::get('distribusi-matkul/{id}/print', [DistributionController::class, 'printPDF'])->name('distribusi-matkul.print');

    // Beban kerja dosen
    Route::resource('/beban-kerja-dosen', WorkloadController::class);
    Route::prefix('beban-kerja-dosen')->name('beban-kerja-dosen.')->group(function () {
        // Route::get('/doc/{document}', [WorkloadController::class, 'show'])->name('beban-kerja-dosen.show-doc');
        // Route::post('/submit', [WorkloadController::class, 'submitValidation'])->name('beban-kerja-dosen.submit');
        Route::post('/generate', [WorkloadController::class, 'generate'])->name('generate');
        Route::post('/update-all', [WorkloadController::class, 'updateAllActivities'])->name('update-all');
    });

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
    });

    // Route Kalender Akademik
    Route::get('kalender-akademik/doc/{document}', [AcademicCalendarController::class, 'show'])->name('kalender-akademik.show-doc');
    Route::post('kalender-akademik/submit', [AcademicCalendarController::class, 'submitValidation'])->name('kalender-akademik.submit');
    Route::get('kalender-akademik/events', [AcademicCalendarController::class, 'getEvents'])->name('kalender-akademik.events');
    Route::get('kalender-akademik/{id}/print', [AcademicCalendarController::class, 'printPdf'])->name('kalender-akademik.print');
    Route::resource('kalender-akademik', AcademicCalendarController::class)->except('show', 'edit', 'create');

    //dokumen aproval
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::resource('/', AprovalDocumentController::class)->only(['index', 'show', 'destroy']);
        Route::post('/{id}/approve', [AprovalDocumentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [AprovalDocumentController::class, 'reject'])->name('reject');
        Route::post('/submit', [AprovalDocumentController::class, 'submit'])->name('submit');
    });
});
