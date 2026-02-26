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

    public function listDosenProdi()
    {
        /** @var \App\Models\User $kaprodi */
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

        $workload = Workload::firstOrCreate(
            ['academic_period_id' => $activePeriod->id, 'user_id' => $userId],
            ['total_sks_pendidikan' => 0]
        );

        $queryActivities = WorkloadActivitie::where('workload_id', $workload->id);

        /** @var \App\Models\User $currentUser */
        if (!$currentUser->hasRole('admin')) {
            $myProdi = Prodi::where('kaprodi_id', $currentUser->id)->first();

            if (!$myProdi) {
                return back()->with('error', 'Akses Ditolak. Anda bukan Kaprodi.');
            }

            $validActivityNames = \App\Models\CourseDistribution::query()
                ->where('academic_period_id', $activePeriod->id)
                ->whereHas('studyClass', function ($q) use ($myProdi) {
                    $q->where('prodi_id', $myProdi->id);
                })
                ->whereHas('teachingLecturers', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                })
                ->with(['course', 'studyClass'])
                ->get()
                ->map(function ($dist) {
                    $shiftLabel = ucfirst($dist->studyClass->shift);
                    return $dist->course->name . ' - Kelas ' . $dist->studyClass->full_name . ' (' . $shiftLabel . ')';
                })
                ->toArray();

            $queryActivities->whereIn('activity_name', $validActivityNames);
        }

        $activities = $queryActivities->get();

        if ($activities->isEmpty() && !$currentUser->hasRole('admin')) {
            //
        }

        return view('content.bkd.kaprodi_edit', compact('activePeriod', 'targetDosen', 'workload', 'activities'));
    }

    public function generate(Request $request)
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) return back()->with('error', 'Periode tidak aktif.');

        $targetUserId = $request->input('user_id', Auth::id());

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

    public function monitoringIndex(Request $request)
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $query = User::with('roles')->orderBy('id', 'DESC');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('nidn', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $data = $query->paginate(10);

        return view('content.bkd.monitor_bkd', compact('data', 'activePeriod'));
    }

    public function getDosenStats($userId)
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        $workload = Workload::where('user_id', $userId)
            ->where('academic_period_id', $activePeriod->id)
            ->with('activities') // Ambil kegiatan yang sudah ada
            ->first();

        if (!$workload || $workload->activities->isEmpty()) {
            return response()->json(['status' => 'empty']);
        }

        // Variabel untuk Chart
        $sksTeori = 0;
        $sksPraktik = 0;
        $sksLapangan = 0;

        // Variabel untuk List Summary
        $prodiSummary = [];
        $detailMatkul = [];

        // 2. Loop Kegiatan BKD
        foreach ($workload->activities as $act) {

            // Hanya proses kategori pendidikan untuk Chart & Tabel Matkul
            if ($act->category !== 'pendidikan') continue;

            $sksJatah = $act->sks_real > 0 ? $act->sks_real : $act->sks_assigned;

            // B. Logika Mencari Jenis Matkul (Teori/Praktik/Lapangan)
            // Karena di tabel workload_activities tidak ada FK ke course, kita cari manual via nama
            // atau kita asumsikan berdasarkan 'sks_load' (SKS Asli Matkul)

            // Coba cari distribusi asli untuk mendapatkan metadata course (Teori/Praktik/nya)
            $distribusiAsli = CourseDistribution::with('course', 'studyClass.prodi')
                ->where('academic_period_id', $activePeriod->id)
                ->get()
                ->first(function ($d) use ($act) {
                    // Pencocokan nama activity dengan nama generate-an distribusi
                    // Ini "Best Effort" matching string
                    $shiftLabel = ucfirst($d->studyClass->shift);
                    $genName = $d->course->name . ' - Kelas ' . $d->studyClass->full_name . ' (' . $shiftLabel . ')';
                    return $genName === $act->activity_name;
                });

            if ($distribusiAsli) {
                $course = $distribusiAsli->course;
                $sksTotalMatkul = $course->sks_teori + $course->sks_praktik + $course->sks_lapangan;
                $sksTotalMatkul = $sksTotalMatkul > 0 ? $sksTotalMatkul : 1; // Prevent division by zero

                // HITUNG PROPORSI CHART
                // Contoh: Matkul 3 SKS (2 Teori, 1 Praktik). Dosen dapat jatah 3 SKS.
                // Maka: Teori = (2/3) * 3 = 2. Praktik = (1/3) * 3 = 1.
                $ratioTeori = $course->sks_teori / $sksTotalMatkul;
                $ratioPraktik = $course->sks_praktik / $sksTotalMatkul;
                $ratioLapangan = $course->sks_lapangan / $sksTotalMatkul;

                $sksTeori += ($sksJatah * $ratioTeori);
                $sksPraktik += ($sksJatah * $ratioPraktik);
                $sksLapangan += ($sksJatah * $ratioLapangan);

                // Data untuk Tabel Detail
                $matkulName = $course->name; // Nama Matkul Murni
                $className  = $distribusiAsli->studyClass->full_name; // Nama Kelas (TI-1A)
                $shift      = strtolower($distribusiAsli->studyClass->shift); // pagi/malam
                $kode       = $course->code;
                $prodiName  = $distribusiAsli->studyClass->prodi->name ?? 'Umum';
                $jenisLabel = $this->getJenisSksLabel($course);
            } else {
                $parts = explode(' - Kelas ', $act->activity_name);

                $matkulName = $parts[0]; // Bagian depan (Nama Matkul)
                $fullClass  = $parts[1] ?? '-'; // Bagian belakang (Kelas + Shift)

                // Deteksi Shift manual dari string
                if (str_contains(strtolower($fullClass), 'malam')) {
                    $shift = 'malam';
                    $className = str_replace(['(Malam)', '()'], '', $fullClass);
                } else {
                    $shift = 'pagi'; // Default pagi
                    $className = str_replace(['(Pagi)', '()'], '', $fullClass);
                }

                $className  = trim($className);
                $sksTeori  += $sksJatah; // Default ke teori
                $kode       = '-';
                $prodiName  = 'Lainnya';
                $jenisLabel = 'Manual';
            }

            // C. Akumulasi Summary Prodi
            if (!isset($prodiSummary[$prodiName])) {
                $prodiSummary[$prodiName] = 0;
            }
            $prodiSummary[$prodiName] += $sksJatah;

            // D. Push Data Tabel
            $detailMatkul[] = [
                'matkul'    => $matkulName, // Hanya Nama Matkul
                'kode'      => $kode,
                'kelas'     => $className,  // Hanya Nama Kelas (Misal: TI-1A)
                'shift'     => $shift,      // 'pagi' atau 'malam' (untuk penentu warna badge)
                'prodi'     => $prodiName,
                'jenis'     => $jenisLabel,
                'sks_total' => number_format($sksJatah, 2)
            ];
        }

        return response()->json([
            'status' => 'success',
            'chart_data' => [
                round($sksTeori, 2),
                round($sksPraktik, 2),
                round($sksLapangan, 2)
            ],
            'prodi_data' => $prodiSummary,
            'detail_table' => $detailMatkul,
            // Total SKS diambil langsung dari perhitungan controller BKD agar konsisten
            'total_sks' => number_format($workload->total_sks_pendidikan, 2)
        ]);
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

    // Helper untuk menstruktur data agar sesuai gambar tabel
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

    // Helper kecil untuk label jenis (taruh di paling bawah class)
    private function getJenisSksLabel($course)
    {
        $labels = [];
        if ($course->sks_teori > 0) $labels[] = 'T';
        if ($course->sks_praktik > 0) $labels[] = 'P';
        if ($course->sks_lapangan > 0) $labels[] = 'L';
        return implode('/', $labels);
    }
}
