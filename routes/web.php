<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Master\RoomController;
use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\Master\ProdiController;
use App\Http\Controllers\Master\CourseController;
use App\Http\Controllers\dashboard\NotifController;

// Master Controllers
use App\Http\Controllers\dokumen\ScheduleController;
use App\Http\Controllers\dokumen\WorkloadController;
use App\Http\Controllers\Master\KurikulumController;
use App\Http\Controllers\Master\StudyClassController;
use App\Http\Controllers\dashboard\AnalisisController;
use App\Http\Controllers\setting\PengaturanController;

// Dokumen & Akademik Controllers
use App\Http\Controllers\authentications\AuthController;
use App\Http\Controllers\authentications\UserController;
use App\Http\Controllers\dashboard\MyScheduleController;
use App\Http\Controllers\dokumen\DistributionController;
use App\Http\Controllers\Master\AcademicPeriodController;
use App\Http\Controllers\dokumen\AprovalDocumentController;
use App\Http\Controllers\dokumen\AcademicCalendarController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post');

    // Landing Page & Jadwal Publik
    Route::get('/', [PublicController::class, 'index'])->name('home');
    Route::get('/jadwal-publik', [PublicController::class, 'jadwal'])->name('public.jadwal');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // --- Global Actions ---
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/Dashboard', [AnalisisController::class, 'index'])->name('dashboard');
    Route::get('/Pengaturan', [PengaturanController::class, 'index'])->name('setting');
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('/pengaturan/update', [PengaturanController::class, 'update'])->name('pengaturan.update');
    Route::get('/notifikasi', [NotifController::class, 'index'])->name('notifikasi');
    Route::put('/notifikasi/update', [NotifController::class, 'update'])->name('notifikasi.update');
    Route::get('/notifikasi/read-all', [NotifController::class, 'markAllRead'])->name('notifikasi.readAll');
    Route::delete('/pengaturan/delete-avatar', [PengaturanController::class, 'deleteAvatar'])->name('pengaturan.deleteAvatar');


    Route::middleware(['role:admin|baak|dosen'])->group(function () {
        Route::post('/booking/store', [RoomBookingController::class, 'store'])->name('booking.store');
        Route::patch('/booking/{id}/approve', [RoomBookingController::class, 'approve'])->name('booking.approve');
        Route::patch('/booking/{id}/reject', [RoomBookingController::class, 'reject'])->name('booking.reject');

        // Jadwal Saya
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/jadwal-saya', [MyScheduleController::class, 'index'])->name('jadwal-saya');
            Route::post('/jadwal-saya/pic', [MyScheduleController::class, 'storePic'])->name('jadwal-saya.pic');
        });

        // Beban Kerja Dosen Pibadi
        Route::get('/bkd-saya', [WorkloadController::class, 'myWorkload'])->name('bkd.saya');
    });


    Route::middleware(['role:admin|baak|kaprodi|wadir1|wadir2|wadir3|direktur'])->group(function () {
        // Beban Kerja Dosen
        Route::prefix('beban-kerja-dosen')->name('beban-kerja-dosen.')->group(function () {
            Route::get('/document/{id}', [WorkloadController::class, 'showDoc'])->name('show-doc');
            Route::get('/document/{id}/print', [WorkloadController::class, 'printDoc'])->name('print-doc');
        });
        Route::get('/monitoring-bkd', [WorkloadController::class, 'monitoringIndex'])->name('monitoring.bkd');
        Route::get('/api/dosen-stats/{userId}', [WorkloadController::class, 'getDosenStats'])->name('api.dosen-stats');

        // Approval Document System (Global)
        Route::resource('documents', AprovalDocumentController::class)->only(['index', 'show']);
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::post('/{id}/approve', [AprovalDocumentController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [AprovalDocumentController::class, 'reject'])->name('reject');
            Route::post('/submit', [AprovalDocumentController::class, 'submit'])->name('submit'); // Submit Dokumen Approval
        });

        Route::get('distribusi-mata-kuliah/doc/{document}', [DistributionController::class, 'show'])->name('distribusi-mata-kuliah.show-doc');
        Route::get('distribusi-mata-kuliah/{id}/print', [DistributionController::class, 'printPDF'])->name('distribusi-mata-kuliah.print');
        Route::get('jadwal-perkuliahan/doc', [ScheduleController::class, 'show'])->name('jadwal-perkuliahan.show');
        Route::get('jadwal-perkuliahan/{id}/print', [ScheduleController::class, 'printPDF'])->name('jadwal-perkuliahan.print');
        Route::get('kalender-akademik/doc/{document}', [AcademicCalendarController::class, 'show'])->name('kalender-akademik.show-doc');
        Route::get('kalender-akademik/{id}/print', [AcademicCalendarController::class, 'printPdf'])->name('kalender-akademik.print');
    });


    Route::middleware(['role:admin|baak|kaprodi'])->group(function () {
        // kurikulum & matkul
        Route::prefix('master')->name('master.')->group(function () {
            Route::resource('kurikulum', KurikulumController::class)->except('show', 'edit', 'create');
            Route::resource('mata-kuliah', CourseController::class)->except('show', 'edit', 'create');
        });

        Route::prefix('mata-kuliah')->name('mata-kuliah.')->group(function () {
            Route::get('/template', [CourseController::class, 'downloadTemplate'])->name('template');
            Route::post('/import', [CourseController::class, 'import'])->name('import');
            Route::post('/sync-siakad', [CourseController::class, 'syncSiakad'])->name('sync-siakad');
        });

        Route::get('/ajax/get-curriculums-by-prodi/{prodiId}', [StudyClassController::class, 'getKurikulumByProdi']);
        Route::post('kurikulum/sync-siakad', [KurikulumController::class, 'syncSiakad'])->name('kurikulums.sync-siakad');

        // Beban Kerja Dosen
        Route::prefix('bkd-dosen')->name('bkd-dosen.')->group(function () {
            Route::get('/list-dosen', [WorkloadController::class, 'listDosenProdi'])->name('list');
            Route::get('/{userId}/edit', [WorkloadController::class, 'editDosenWorkload'])->name('edit');
            Route::post('/generate', [WorkloadController::class, 'generate'])->name('generate');
            Route::put('/update-all', [WorkloadController::class, 'updateAllActivities'])->name('update-all');
            Route::post('/submit-document', [WorkloadController::class, 'submit'])->name('submit');
        });

        // Distribusi Mata Kuliah
        Route::prefix('distribusi-mata-kuliah')->name('distribusi-mata-kuliah.')->group(function () {
            Route::delete('/bulk-destroy', [DistributionController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::get('/template', [DistributionController::class, 'downloadTemplate'])->name('template');
            Route::post('/import', [DistributionController::class, 'import'])->name('import');
            Route::post('/submit', [DistributionController::class, 'submitToKaprodi'])->name('submit');
        });
        Route::resource('distribusi-mata-kuliah', DistributionController::class)->except('show');

        // Helper AJAX Distribusi
        Route::get('/ajax/get-courses-by-class/{classId}', [DistributionController::class, 'getCoursesByClass'])->name('ajax.courses');
        Route::prefix('distributions')->name('distributions.')->group(function () {
            Route::post('/generate', [DistributionController::class, 'generate'])->name('generate');
            Route::get('/export/{period_id}', [DistributionController::class, 'export'])->name('export');
            Route::post('/import-update', [DistributionController::class, 'importUpdate'])->name('import-update');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | 5. ADMIN & BAAK AREA (Data Master & Penjadwalan Global)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|baak'])->group(function () {

        // --- Manajemen Jadwal Perkuliahan (Global) ---
        Route::prefix('jadwal-perkuliahan')->name('jadwal-perkuliahan.')->group(function () {
            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::post('/submit', [ScheduleController::class, 'submit'])->name('submit');
            Route::get('/get-events', [ScheduleController::class, 'getEvents'])->name('get-events');
            Route::post('/store', [ScheduleController::class, 'store'])->name('store');
            Route::put('/{id}', [ScheduleController::class, 'update'])->name('update');
            Route::patch('/{id}/resize', [ScheduleController::class, 'resize'])->name('resize');
            Route::delete('/{id}', [ScheduleController::class, 'destroy'])->name('destroy');
            Route::post('/auto-generate', [ScheduleController::class, 'autoGenerate'])->name('auto-generate');
        });

        // --- Kalender Akademik ---
        Route::resource('kalender-akademik', AcademicCalendarController::class)->except('show', 'edit', 'create');
        Route::prefix('kalender-akademik')->name('kalender-akademik.')->group(function () {
            Route::post('submit', [AcademicCalendarController::class, 'submitValidation'])->name('submit');
            Route::get('events', [AcademicCalendarController::class, 'getEvents'])->name('events');
        });


        // --- Data Master Resources ---
        Route::prefix('master')->name('master.')->group(function () {
            Route::resource('kelas', StudyClassController::class)->except('show', 'edit');
            Route::resource('program-studi', ProdiController::class)->except('show', 'edit', 'create');
            Route::resource('ruangan', RoomController::class)->except('show', 'edit', 'create');
            Route::resource('periode-akademik', AcademicPeriodController::class)->except('show', 'edit', 'create');
        });

        // --- Helpers Master (Sync/Import/Ajax) ---
        Route::patch('/periode-akademik/{id}/set-active', [AcademicPeriodController::class, 'setActive'])->name('periode-akademik.set-active');
        Route::post('/study-classes/copy', [StudyClassController::class, 'copyFromPeriod'])->name('study-classes.copy');


        Route::prefix('kelas-perkuliahan')->name('kelas-perkuliahan.')->group(function () {
            Route::get('/template', [StudyClassController::class, 'downloadTemplate'])->name('template');
            Route::post('/import', [StudyClassController::class, 'import'])->name('import');
            Route::post('/sync-siakad', [StudyClassController::class, 'syncSiakad'])->name('sync-siakad');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | 6. SUPER ADMIN AREA (Role: Admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/users/sync-siakad', [UserController::class, 'syncSiakad'])->name('users.sync-siakad');
        Route::resource('user', UserController::class)->except('show', 'edit', 'create');
    });
});
