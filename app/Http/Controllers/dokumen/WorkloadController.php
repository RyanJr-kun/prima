<?php

namespace App\Http\Controllers\dokumen;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Workload;
use App\Models\StudyClass;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Models\AprovalDocument;
use App\Models\WorkloadActivitie;
use App\Models\CourseDistribution;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Notifications\DocumentActionNotification;

class WorkloadController extends Controller
{
    public function myWorkload()
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) return back()->with('error', 'Periode tidak aktif.');

        $userId = Auth::id();

        // 1. Cek apakah Workload sudah ada?
        $workload = Workload::with('activities')
            ->where('user_id', $userId)
            ->where('academic_period_id', $activePeriod->id)
            ->first();

        // 2. JIKA BELUM ADA, GENERATE OTOMATIS DARI DISTRIBUSI
        if (!$workload) {
            $this->processGeneration($activePeriod->id, $userId);

            // Ambil ulang data yang baru digenerate
            $workload = Workload::with('activities')
                ->where('user_id', $userId)
                ->where('academic_period_id', $activePeriod->id)
                ->first();
        }

        return view('content.bkd.dosen_view', compact('activePeriod', 'workload'));
    }

    /**
     * HALAMAN KAPRODI (Auto Sync semua dosen prodi saat dibuka)
     */
    public function listDosenProdi()
    {
        $kaprodi = Auth::user();
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $prodi = Prodi::where('kaprodi_id', $kaprodi->id)->first();

        if (!$prodi && $kaprodi->hasRole('admin')) {
            return back()->with('error', 'Anda login sebagai Admin tapi tidak terhubung ke Prodi.');
        }
        if (!$prodi) return back()->with('error', 'Anda tidak terdaftar sebagai Kaprodi.');

        $dosenQuery = User::role('dosen')
            ->whereHas('teachingDistributions', function ($qDistribusi) use ($prodi, $activePeriod) {
                $qDistribusi->where('academic_period_id', $activePeriod->id);
                $qDistribusi->whereHas('studyClass', function ($qKelas) use ($prodi) {
                    $qKelas->where('prodi_id', $prodi->id);
                });
            });

        $dosenIds = $dosenQuery->pluck('id');
        foreach ($dosenIds as $uid) {
            $exists = Workload::where('academic_period_id', $activePeriod->id)
                ->where('user_id', $uid)
                ->exists();

            if (!$exists) {
                // Generate diam-diam
                $this->processGeneration($activePeriod->id, $uid);
            }
        }

        $dosens = $dosenQuery
            ->with(['workloads' => function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                    ->with('activities');
            }])
            ->orderBy('name')
            ->get();

        $validActivityNames = CourseDistribution::query()
            ->where('academic_period_id', $activePeriod->id)
            ->whereHas('studyClass', function ($q) use ($prodi) {
                $q->where('prodi_id', $prodi->id);
            })
            ->with(['course', 'studyClass'])
            ->get()
            ->map(function ($dist) {
                $shiftLabel = ucfirst($dist->studyClass->shift);
                return $dist->course->name . ' - Kelas ' . $dist->studyClass->full_name . ' (' . $shiftLabel . ')';
            })
            ->toArray();


        foreach ($dosens as $dosen) {
            $wl = $dosen->workloads->first();
            if ($wl) {
                $sksProdi = $wl->activities
                    ->whereIn('activity_name', $validActivityNames)
                    ->sum('sks_real');
                $dosen->sks_prodi_ini = $sksProdi;
            } else {
                $dosen->sks_prodi_ini = 0;
            }
        }

        $approvalDoc = AprovalDocument::where([
            'academic_period_id' => $activePeriod->id,
            'prodi_id' => $prodi->id,
            'type' => 'beban_kerja_dosen'
        ])->first();

        return view('content.bkd.kaprodi_list', compact('activePeriod', 'dosens', 'approvalDoc', 'prodi'));
    }

    public function editDosenWorkload($userId)
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) return back()->with('error', 'Periode tidak aktif.');

        $targetDosen = User::findOrFail($userId);
        $currentUser = Auth::user();

        // 1. Pastikan Header Workload Ada
        $workload = Workload::firstOrCreate(
            ['academic_period_id' => $activePeriod->id, 'user_id' => $userId],
            ['total_sks_pendidikan' => 0]
        );

        // 2. LOGIC FILTER KEGIATAN BERDASARKAN PRODI
        $queryActivities = WorkloadActivitie::where('workload_id', $workload->id);

        if (!$currentUser->hasRole('admin')) {
            // Jika Kaprodi, ambil data Prodi-nya
            $myProdi = Prodi::where('kaprodi_id', $currentUser->id)->first();

            if (!$myProdi) {
                return back()->with('error', 'Akses Ditolak. Anda bukan Kaprodi.');
            }

            // A. Cari Daftar Mata Kuliah (Distribusi) yang Valid untuk Prodi Kaprodi ini
            // Logic ini meniru cara 'processGeneration' membuat nama activity
            $validActivityNames = \App\Models\CourseDistribution::query()
                ->where('academic_period_id', $activePeriod->id)
                // FILTER KUNCI: Hanya ambil kelas yang prodi_id nya sama dengan Kaprodi
                ->whereHas('studyClass', function ($q) use ($myProdi) {
                    $q->where('prodi_id', $myProdi->id);
                })
                // Filter Dosen Target
                ->whereHas('teachingLecturers', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                })
                ->with(['course', 'studyClass'])
                ->get()
                ->map(function ($dist) {
                    // Generate Nama Activity yang Sama Persis dengan processGeneration
                    $shiftLabel = ucfirst($dist->studyClass->shift);
                    return $dist->course->name . ' - Kelas ' . $dist->studyClass->full_name . ' (' . $shiftLabel . ')';
                })
                ->toArray();

            // B. Terapkan Filter ke Query
            // Hanya tampilkan kegiatan yang namanya ada dalam daftar valid di atas
            $queryActivities->whereIn('activity_name', $validActivityNames);
        }

        // 3. Eksekusi Query
        $activities = $queryActivities->get();

        // Validasi Tambahan: Jika kosong, mungkin dosen ini memang tidak mengajar di prodi kaprodi tersebut
        if ($activities->isEmpty() && !$currentUser->hasRole('admin')) {
            // Cek apakah memang tidak ada jadwal atau belum di-generate
            // Opsional: Bisa return back with warning, atau biarkan tampil kosong
        }

        return view('content.bkd.kaprodi_edit', compact('activePeriod', 'targetDosen', 'workload', 'activities'));
    }
    // Method API untuk AJAX Chart
    public function getDosenStats($userId)
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $workload = Workload::where('user_id', $userId)
            ->where('academic_period_id', $activePeriod->id)
            ->with('activities')
            ->first();

        if (!$workload) {
            return response()->json(['status' => 'empty']);
        }

        // Hitung Komposisi
        $stats = [
            'total_sks' => $workload->total_sks_pendidikan,
            'pendidikan' => $workload->activities->where('category', 'pendidikan')->sum('sks_real'),
            'penelitian' => $workload->total_sks_penelitian,
            'pengabdian' => $workload->total_sks_pengabdian,
            'detail_matkul' => $workload->activities->where('category', 'pendidikan')->map(function ($act) {
                return [
                    'name' => $act->activity_name,
                    'sks' => $act->sks_real,
                    'tugas' => ($act->is_uts_maker ? 'UTS ' : '') . ($act->is_uas_maker ? 'UAS' : '')
                ];
            })
        ];

        return response()->json(['status' => 'success', 'data' => $stats]);
    }

    public function generate(Request $request)
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) return back()->with('error', 'Periode tidak aktif.');

        // Support untuk Kaprodi men-generate punya orang lain
        $targetUserId = $request->input('user_id', Auth::id());

        // Validasi akses sederhana
        if ($targetUserId != Auth::id() && !Auth::user()->hasRole(['kaprodi', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        $count = $this->processGeneration($activePeriod->id, $targetUserId);

        return back()->with('success', "Sinkronisasi selesai. $count kegiatan diperbarui.");
    }

    private function processGeneration($periodId, $userId)
    {
        // 1. Buat/Ambil Header Workload
        $workload = Workload::firstOrCreate(
            ['academic_period_id' => $periodId, 'user_id' => $userId],
            ['total_sks_pendidikan' => 0, 'conclusion' => 'belum_dihitung']
        );

        // 2. Ambil Data dari Tabel Pivot Course Lecturer
        // Logika: Cari distribusi yang memiliki teachingLecturers dengan user_id ini
        $assignments = CourseDistribution::with(['course', 'studyClass', 'teachingLecturers'])
            ->where('academic_period_id', $periodId)
            ->whereHas('teachingLecturers', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get();

        $count = 0;

        foreach ($assignments as $assign) {
            $shiftLabel = ucfirst($assign->studyClass->shift);
            $activityName = $assign->course->name . ' - Kelas ' . $assign->studyClass->full_name . ' (' . $shiftLabel . ')';

            // Cek apakah item ini sudah ada di BKD?
            $existingActivity = WorkloadActivitie::where('workload_id', $workload->id)
                ->where('activity_name', $activityName)
                ->first();

            // --- HITUNG SKS DEFAULT (Auto Split Team Teaching) ---
            $sksMatkulUtuh = $assign->course->sksTotal; // Menggunakan Accessor model Course
            $jumlahDosen = $assign->teachingLecturers->count();

            // Default: Bagi Rata (Sesuai request Revisi 2)
            // Nanti Kaprodi bisa edit 'sks_assigned' di halaman edit jika tidak rata
            $sksPerDosenDefault = ($jumlahDosen > 0) ? ($sksMatkulUtuh / $jumlahDosen) : $sksMatkulUtuh;

            if (!$existingActivity) {
                WorkloadActivitie::create([
                    'workload_id'   => $workload->id,
                    'category'      => 'pendidikan',
                    'activity_name' => $activityName,
                    'sks_load'      => $sksMatkulUtuh,      // SKS Asli Matkul
                    'sks_assigned'  => $sksPerDosenDefault, // SKS Jatah (Default Bagi Rata)
                    'sks_real'      => $sksPerDosenDefault, // Awalnya Real = Jatah (Asumsi 16 pertemuan)
                    'realisasi_pertemuan' => 16,            // Default Full
                    'is_uts_maker'  => false,
                    'is_uas_maker'  => false
                ]);
                $count++;
            }
        }

        $workload->update(['is_verified' => false]);

        // Hitung ulang total di header workload
        $this->recalculateTotal($workload->id);

        return $count;
    }

    private function recalculateTotal($workloadId)
    {
        $wl = Workload::find($workloadId);
        $total = WorkloadActivitie::where('workload_id', $workloadId)
            ->where('category', 'pendidikan')
            ->sum('sks_real');

        // Hitung total dengan kategori lain (jika ada fitur penelitian/pengabdian di masa depan)
        $grandTotal = $total + $wl->total_sks_penelitian + $wl->total_sks_pengabdian + $wl->total_sks_penunjang;

        $wl->update([
            'total_sks_pendidikan' => $total,
            'conclusion' => ($grandTotal >= 12) ? 'memenuhi' : 'tidak_memenuhi'
        ]);
    }

    public function printRekapProdi(Request $request)
    {
        $prodiId = $request->prodi_id;
        $periodId = $request->period_id;

        $dosens = User::where('prodi_id', $prodiId)->get();

        $laporan = [];

        foreach ($dosens as $dosen) {
            $workload = Workload::where('user_id', $dosen->id)
                ->where('academic_period_id', $periodId)
                ->first();

            if (!$workload) continue;

            $kegiatan = WorkloadActivitie::where('workload_id', $workload->id)
                ->where('category', 'pendidikan')
                ->get();

            $grouped = $kegiatan->groupBy(function ($item) {
                return explode(' - Kelas ', $item->activity_name)[0];
            });

            $laporan[] = [
                'dosen' => $dosen,
                'matkul_grouped' => $grouped
            ];
        }
        $pdf = PDF::loadView('pdf.bkd_rekap', compact('laporan'));
        return $pdf->download('BKD_Rekap_Prodi.pdf');
    }

    public function updateAllActivities(Request $request)
    {
        $data = $request->activities;
        if (!$data) return back()->with('warning', 'Tidak ada data.');

        $workloadId = null;

        DB::beginTransaction();
        try {
            foreach ($data as $id => $values) {
                $activity = WorkloadActivitie::findOrFail($id);
                $workloadId = $activity->workload_id;

                $realisasi = (int) ($values['realisasi_pertemuan'] ?? 0);

                // solusi Kasus Praktikum Farmasi (Dosen isi 2 SKS walau tim teaching)
                $sksAssigned = isset($values['sks_assigned']) ? (float)$values['sks_assigned'] : $activity->sks_assigned;

                // 2. Hitung SKS Real
                // Rumus: (Realisasi / Standar Pertemuan) * SKS Jatah
                // Kita pakai pembagi 14 atau 16? Biasanya 16 (14 tatap muka + 2 ujian). 
                // Tapi untuk aman kita pakai input realisasi max 16.
                // Jika realisasi 16, maka dapat 100% dari sks_assigned.
                $pembagi = 16;
                $sksReal = ($realisasi / $pembagi) * $sksAssigned;

                // 3. Simpan
                $activity->update([
                    'realisasi_pertemuan' => $realisasi,
                    'is_uts_maker'        => isset($values['is_uts_maker']) ? 1 : 0,
                    'is_uas_maker'        => isset($values['is_uas_maker']) ? 1 : 0,
                    'sks_assigned'        => $sksAssigned, // Simpan manualan Kaprodi
                    'sks_real'            => $sksReal
                ]);
            }

            if ($workloadId) {
                $this->recalculateTotal($workloadId);

                $wl = Workload::find($workloadId);
                $wl->update(['is_verified' => true]);
            }

            DB::commit();
            return back()->with('success', 'Perubahan beban kerja berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }



    public function rekapIndex(Request $request)
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $user = Auth::user();

        // Default variable
        /** @var User $user */
        $prodiId = null;
        $isKaprodi = $user->hasRole('kaprodi');
        $dosens = collect([]);
        $approvalDoc = null;

        if ($isKaprodi) {
            $myProdi = Prodi::where('kaprodi_id', $user->id)->first();

            if ($myProdi) {
                $prodiId = $myProdi->id;
            } else {
                return back()->with('error', 'Akun Anda terdaftar sebagai Kaprodi, namun belum dipetakan ke Program Studi manapun.');
            }
        } else {
            // Jika Admin/BAAK, ambil dari Input Filter
            $prodiId = $request->input('prodi_id');
        }

        // 2. EKSEKUSI QUERY HANYA JIKA PRODI ID ADA
        // (Kaprodi otomatis ada, Admin menunggu input)
        if ($prodiId) {
            $dosens = User::role('dosen')
                ->whereHas('teachingDistributions', function ($qDist) use ($prodiId, $activePeriod) {
                    $qDist->where('academic_period_id', $activePeriod->id ?? 0);
                    $qDist->whereHas('studyClass', function ($qClass) use ($prodiId) {
                        $qClass->where('prodi_id', $prodiId);
                    });
                })
                ->with(['prodi', 'workloads' => function ($q) use ($activePeriod) {
                    $q->where('academic_period_id', $activePeriod->id ?? 0);
                }])
                ->orderBy('name')
                ->get();

            // Cek Dokumen Approval
            if ($activePeriod) {
                $approvalDoc = AprovalDocument::where([
                    'academic_period_id' => $activePeriod->id,
                    'prodi_id' => $prodiId,
                    'type' => 'beban_kerja_dosen'
                ])->first();
            }
        }

        // 3. AMBIL LIST PRODI UNTUK DROPDOWN
        $prodis = Prodi::orderBy('name')->get();

        return view('content.bkd.admin_rekap', compact(
            'activePeriod',
            'dosens',
            'prodis',
            'prodiId',
            'approvalDoc',
            'isKaprodi'
        ));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required',
            'academic_period_id' => 'required'
        ]);

        // 1. Simpan/Update Dokumen
        // Tampung ke variabel $doc agar bisa dikirim ke notifikasi
        $doc = AprovalDocument::updateOrCreate(
            [
                'academic_period_id' => $request->academic_period_id,
                'prodi_id' => $request->prodi_id,
                'type' => 'beban_kerja_dosen',
            ],
            [
                'status' => 'submitted',
                'feedback_message' => null,
                'action_by_user_id' => Auth::id()
            ]
        );

        // 2. LOGIC NOTIFIKASI KE KAPRODI
        $prodi = Prodi::find($request->prodi_id);
        $currentUser = Auth::user();


        if ($prodi && $prodi->kaprodi_id) {
            $kaprodi = User::find($prodi->kaprodi_id);
            if ($kaprodi && $kaprodi->id !== $currentUser->id) {
                $kaprodi->notify(new DocumentActionNotification(
                    $doc,
                    'submitted',
                    $currentUser->name
                ));
            }
        } elseif ($currentUser->hasRole('kaprodi')) {
            $wadir1 = User::role('wadir1')->first();
            if ($wadir1) {
                $wadir1->notify(new DocumentActionNotification($doc, 'submitted', $currentUser->name));
            }
        }


        return back()->with('success', 'Dokumen Rekap BKD berhasil diajukan!');
    }

    // Tambahkan di WorkloadController.php

    public function showDoc($id)
    {
        $doc = AprovalDocument::with(['prodi', 'academicPeriod'])->findOrFail($id);
        $reportData = $this->prepareReportData($doc);

        return view('content.bkd.show', compact('doc', 'reportData'));
    }

    public function printDoc($id)
    {
        $doc = AprovalDocument::with(['prodi', 'academicPeriod'])->findOrFail($id);
        $reportData = $this->prepareReportData($doc);

        // Load PDF
        $pdf = PDF::loadView('content.dokumen.print.bkd_pdf', compact('doc', 'reportData'));
        $pdf->setPaper('f4', 'potrait'); // Landscape agar tabel muat

        return $pdf->stream('Laporan_BKD_' . $doc->prodi->code . '.pdf');
    }

    /**
     * Helper untuk menstruktur data agar sesuai gambar tabel
     */
    private function prepareReportData($doc)
    {
        $validClassNames = StudyClass::with('prodi')
            ->where('prodi_id', $doc->prodi_id)
            ->get()
            ->pluck('full_name')
            ->map(fn($name) => strtolower(trim($name)))
            ->toArray();

        $dosens = User::role('dosen')
            ->whereHas('workloads', function ($q) use ($doc) {
                $q->where('academic_period_id', $doc->academic_period_id);
            })
            ->with(['workloads' => function ($q) use ($doc) {
                $q->where('academic_period_id', $doc->academic_period_id);
            }, 'workloads.activities' => function ($q) {
                $q->where('category', 'pendidikan');
            }])
            ->get();

        $finalData = [];

        foreach ($dosens as $dosen) {
            $workload = $dosen->workloads->first();
            if (!$workload) continue;
            $activities = $workload->activities->filter(function ($act) use ($validClassNames) {
                $parts = explode(' - Kelas ', $act->activity_name);

                if (count($parts) < 2) return false;

                $kelasPart = $parts[1];
                $kelasNameClean = trim(str_replace(['(Pagi)', '(Malam)'], '', $kelasPart));

                return in_array(strtolower($kelasNameClean), $validClassNames);
            });

            if ($activities->isEmpty()) continue;

            $groupedMatkul = $activities->groupBy(function ($item) {
                $parts = explode(' - Kelas ', $item->activity_name);
                return $parts[0] ?? $item->activity_name;
            });

            $dosenRow = [
                'user' => $dosen,
                'matkuls' => [],
                'total_sks_bkd' => 0,
                'total_sks_real' => 0
            ];

            foreach ($groupedMatkul as $namaMatkul => $items) {

                $groupedShift = $items->groupBy(function ($item) {
                    if (\Illuminate\Support\Str::contains(strtolower($item->activity_name), 'malam')) return 'Reg 2';
                    return 'Reg 1';
                });

                foreach ($groupedShift as $shift => $subItems) {

                    $firstItem = $subItems->first();
                    $sksLoad = $firstItem->sks_load;
                    $sksAssigned = $firstItem->sks_assigned;
                    $jmlKelas = $subItems->count();
                    $totalSks = $sksAssigned * $jmlKelas;
                    $pertemuan = $firstItem->realisasi_pertemuan;

                    $isUts = $firstItem->is_uts_maker;
                    $isUas = $firstItem->is_uas_maker;

                    $sksRealTotal = $subItems->sum('sks_real');

                    $kelasNames = $subItems->map(function ($act) {
                        $parts = explode(' - Kelas ', $act->activity_name);
                        $belakang = $parts[1] ?? '';
                        return trim(str_replace(['(Pagi)', '(Malam)'], '', $belakang));
                    })->join(', ');

                    $dosenRow['matkuls'][] = [
                        'nama_matkul' => $namaMatkul,
                        'kelas_type' => $shift,
                        'daftar_kelas' => $kelasNames,
                        'sks_per_mk' => $sksLoad,
                        'sks_assigned' => $sksAssigned,
                        'jml_kelas' => $jmlKelas,
                        'jml_sks_total' => $totalSks,
                        'pertemuan' => $pertemuan,
                        'is_uts' => $isUts,
                        'is_uas' => $isUas,
                        'sks_real' => $sksRealTotal
                    ];

                    $dosenRow['total_sks_bkd'] += $totalSks;
                    $dosenRow['total_sks_real'] += $sksRealTotal;
                }
            }

            $finalData[] = $dosenRow;
        }

        usort($finalData, fn($a, $b) => strcmp($a['user']->name, $b['user']->name));

        return $finalData;
    }
}
