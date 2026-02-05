<?php

namespace App\Http\Controllers\dokumen;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Workload;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Models\AprovalDocument;
use App\Models\WorkloadActivitie;
use App\Models\CourseDistribution;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Notifications\DocumentActionNotification;

class WorkloadController extends Controller
{
    public function index()
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        if (!$activePeriod) {
            return redirect()->back()->with('error', 'Periode akademik belum diaktifkan.');
        }

        $userId = Auth::id();
        $workload = Workload::where('user_id', $userId)
            ->where('academic_period_id', $activePeriod->id)
            ->first();

        if (!$workload) {
            $this->processGeneration($activePeriod->id, $userId);
            $workload = Workload::where('user_id', $userId)
                ->where('academic_period_id', $activePeriod->id)
                ->first();
        }

        $activities = $workload
            ? WorkloadActivitie::where('workload_id', $workload->id)->get()
            : collect([]);

        return view('content.bkd.index', compact('activePeriod', 'workload', 'activities'));
    }

    public function generate(Request $request)
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) return back()->with('error', 'Periode tidak aktif.');

        $count = $this->processGeneration($activePeriod->id, Auth::id());

        return back()->with('success', "Data berhasil disinkronisasi. $count kegiatan baru ditambahkan.");
    }

    private function processGeneration($periodId, $userId)
    {
        $workload = Workload::firstOrCreate(
            ['academic_period_id' => $periodId, 'user_id' => $userId],
            [
                'total_sks_pendidikan' => 0,
                'conclusion' => 'belum_dihitung'
            ]
        );

        $assignments = CourseDistribution::with(['course', 'studyClass'])
            ->where('academic_period_id', $periodId)
            ->whereHas('teachingLecturers', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get();

        $count = 0;
        $totalSksPendidikan = 0;

        foreach ($assignments as $assign) {

            $shiftLabel = ucfirst($assign->studyClass->shift);
            $activityName = $assign->course->name . ' - Kelas ' . $assign->studyClass->full_name . ' (' . $shiftLabel . ')';

            $existingActivity = WorkloadActivitie::where('workload_id', $workload->id)
                ->where('activity_name', $activityName)
                ->first();

            if (!$existingActivity) {
                $sksLoad = $assign->course->sks_teori + $assign->course->sks_praktik;
                $sksReal = $sksLoad;

                WorkloadActivitie::create([
                    'workload_id'   => $workload->id,
                    'category'      => 'pendidikan',
                    'activity_name' => $activityName,
                    'sks_load'      => $sksLoad,
                    'realisasi_pertemuan' => 14,
                    'jenis_ujian'         => 'UTS, UAS',
                    'sks_real'            => $sksReal
                ]);
                $count++;
            }
        }
        $grandTotalSks = WorkloadActivitie::where('workload_id', $workload->id)
            ->where('category', 'pendidikan')
            ->sum('sks_real');

        $workload->update([
            'total_sks_pendidikan' => $grandTotalSks,
            'conclusion' => ($grandTotalSks >= 12) ? 'memenuhi' : 'tidak_memenuhi'
        ]);

        return $count;
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

        if (!$data) {
            return back()->with('warning', 'Tidak ada data yang disimpan.');
        }

        $totalSksPendidikan = 0;
        $workloadId = null;

        foreach ($data as $id => $values) {
            $activity = WorkloadActivitie::find($id);

            if ($activity && $activity->workload->user_id == Auth::id()) {

                $realisasi = (int) $values['realisasi_pertemuan'];

                $sksLoad = $activity->sks_load;
                $sksReal = ($realisasi / 14) * $sksLoad;

                $activity->update([
                    'realisasi_pertemuan' => $realisasi,
                    'jenis_ujian'         => $values['jenis_ujian'],
                    'sks_real'            => $sksReal
                ]);

                $totalSksPendidikan += $sksReal;
                $workloadId = $activity->workload_id;
            }
        }

        if ($workloadId) {
            $wl = Workload::find($workloadId);
            $wl->update([
                'total_sks_pendidikan' => $totalSksPendidikan,
                'conclusion' => ($totalSksPendidikan >= 12) ? 'memenuhi' : 'tidak_memenuhi'
            ]);
        }

        return back()->with('success', 'Data berhasil disimpan dan SKS dihitung ulang!');
    }

    public function rekapIndex(Request $request)
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $user = Auth::user();

        // Default variable
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

        // Siapkan Data Laporan (Logic sama untuk Show & Print)
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
        $validClassNames = \App\Models\StudyClass::with('prodi') // Wajib with('prodi') karena full_name butuh data prodi
            ->where('prodi_id', $doc->prodi_id)
            ->get() // Eksekusi query: SELECT * FROM study_classes...
            ->pluck('full_name') // Ini pluck versi Collection (PHP), bukan SQL. Aman.
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
                    $sksLoad = $subItems->first()->sks_load;
                    $jmlKelas = $subItems->count();
                    $totalSks = $sksLoad * $jmlKelas;
                    $pertemuan = $subItems->first()->realisasi_pertemuan;
                    $ujian = $subItems->first()->jenis_ujian;
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
                        'jml_kelas' => $jmlKelas,
                        'jml_sks_total' => $totalSks,
                        'pertemuan' => $pertemuan,
                        'ujian' => $ujian,
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
