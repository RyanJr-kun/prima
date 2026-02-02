<?php

namespace App\Http\Controllers\dokumen;

use App\Models\User;
use App\Exports\DistributionExport;
use App\Imports\DistributionUpdateImport;
use App\Models\Prodi;
use App\Models\Course;
use App\Models\StudyClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Models\CourseDistribution;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use App\Imports\CourseDistributionImport;
use App\Exports\CourseDistributionTemplateExport;
use App\Models\AprovalDocument;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;


class DistributionController extends Controller
{
    public function index(Request $request)
    {
        $periods = AcademicPeriod::orderBy('name', 'desc')->get();

        if ($request->has('period_id')) {
            $activePeriod = $periods->where('id', $request->period_id)->first();
        } else {
            $activePeriod = $periods->where('is_active', true)->first();
        }

        if (!$activePeriod) {
            $activePeriod = $periods->first();
        }

        if (!$activePeriod) {
            return redirect()->back()->with('error', 'Belum ada Periode Akademik yang tersedia!');
        }

        $prodis = Prodi::all();
        $query = CourseDistribution::with([
            'studyClass.prodi',
            'course',
            'teachingLecturers',
            'pddiktiLecturers'
        ])
            ->where('academic_period_id', $activePeriod->id);

        if ($request->filled('prodi_id')) {
            $query->whereHas('studyClass', function ($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        if ($request->filled('semester')) {
            $query->whereHas('studyClass', function ($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        $rawDistributions = $query->get();

        $distributions = $rawDistributions->groupBy(function ($item) {
            return $item->studyClass->prodi_id . '-' .
                $item->studyClass->semester . '-' .
                $item->studyClass->angkatan . '-' .
                $item->studyClass->shift;
        });

        $study_classes = StudyClass::with('prodi')
            ->where('academic_period_id', $activePeriod->id)
            ->get();

        $activePeriod = $periods->firstwhere('is_active', true)->firstOrFail();
        $classes = StudyClass::where('academic_period_id', $activePeriod->id)->get();
        $dosens = User::role('dosen')->select('id', 'name')->orderBy('name')->get();

        $documentStatus = null;
        $documentData = null;

        if ($activePeriod && $request->filled('prodi_id')) {
            $documentData = AprovalDocument::where([
                'academic_period_id' => $activePeriod->id,
                'prodi_id'           => $request->prodi_id,
                'type'               => 'distribusi_matkul'
            ])->first();

            $documentStatus = $documentData ? $documentData->status : 'draft';
        }

        return view('content.distribution.index', compact(
            'distributions',
            'activePeriod',
            'periods',
            'study_classes',
            'prodis',
            'dosens',
            'classes',
            'documentData',
            'documentStatus'
        ));
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $request->validate([
            'study_class_id' => 'required|exists:study_classes,id',
            'course_id'      => 'required|exists:courses,id',
            'user_id'        => 'nullable|exists:users,id',
            'referensi'      => 'nullable|string',
            'luaran'         => 'nullable|string',
        ]);

        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) {
            return back()->with('error', 'Gagal: Tidak ada Periode Akademik yang aktif.');
        }

        $exists = CourseDistribution::where([
            'academic_period_id' => $activePeriod->id,
            'study_class_id'     => $request->study_class_id,
            'course_id'          => $request->course_id,
        ])->exists();

        if ($exists) {
            return back()->with('error', 'Mata kuliah ini sudah didistribusikan di kelas tersebut!');
        }

        DB::beginTransaction();

        try {
            $dist = CourseDistribution::create([
                'academic_period_id' => $activePeriod->id,
                'study_class_id'     => $request->study_class_id,
                'course_id'          => $request->course_id,
                'referensi'          => $request->referensi,
                'luaran'             => $request->luaran,
            ]);

            if ($request->filled('user_id')) {
                $dist->teachingLecturers()->attach($request->user_id, [
                    'category' => 'real_teaching'
                ]);

                $dist->pddiktiLecturers()->attach($request->user_id, [
                    'category' => 'pddikti_reporting'
                ]);
            }

            DB::commit();

            return redirect()
                ->route('distribusi-mata-kuliah.index')
                ->with('success', 'Distribusi berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan server: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $doc = AprovalDocument::with(['academicPeriod', 'prodi', 'lastActionUser'])->findOrFail($id);

        $distributions = CourseDistribution::with(['course', 'studyClass'])
            ->where('academic_period_id', $doc->academic_period_id)
            ->whereHas('studyClass', function ($q) use ($doc) {
                $q->where('prodi_id', $doc->prodi_id);
            })
            ->orderBy('study_class_id')
            ->get()
            ->groupBy('study_class_id');

        return view('content.distribution.show', compact('doc', 'distributions'));
    }

    public function edit($id)
    {
        $distribution = CourseDistribution::with(['teachingLecturers', 'pddiktiLecturers'])
            ->findOrFail($id);
        return response()->json($distribution);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'teaching_ids'   => 'array',
            'pddikti_ids'    => 'array',
            'teaching_ids.*' => 'exists:users,id',
            'pddikti_ids.*'  => 'exists:users,id',

        ]);

        try {
            $distribution = CourseDistribution::findOrFail($id);

            $distribution->update([
                'referensi' => $request->referensi,
                'luaran'    => $request->luaran,
            ]);

            DB::table('course_lecturers')->where('course_distribution_id', $id)->delete();
            $pivotData = [];
            if ($request->teaching_ids) {
                foreach ($request->teaching_ids as $uid) {
                    $pivotData[] = [
                        'course_distribution_id' => $id,
                        'user_id' => $uid,
                        'category' => 'real_teaching',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            // 4. Masukkan Pelapor PDDIKTI
            if ($request->pddikti_ids) {
                foreach ($request->pddikti_ids as $uid) {
                    $pivotData[] = [
                        'course_distribution_id' => $id,
                        'user_id' => $uid,
                        'category' => 'pddikti_reporting',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            if (!empty($pivotData)) {
                DB::table('course_lecturers')->insert($pivotData);
            }

            DB::commit();
            return back()->with('success', 'Data Tim Pengajar berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $distribution = CourseDistribution::findOrFail($id);

            $distribution->delete();
            return redirect()->route('distribusi-mata-kuliah.index')
                ->with('success', 'Distribusi mata kuliah berhasil dihapus!');
        } catch (QueryException $e) {

            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('distribusi-mata-kuliah.index')
                    ->with('error', 'Gagal menghapus: Data distribusi mata kuliah ini sedang digunakan.');
            }

            return redirect()->route('distribusi-mata-kuliah.index')->with('error', 'Terjadi kesalahan sistem saat menghapus data distribusi mata kuliah.');
        }
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:course_distributions,id',
        ]);

        $ids = $request->ids;
        $successCount = 0;
        $failCount = 0;

        foreach ($ids as $id) {
            try {
                $dist = CourseDistribution::findOrFail($id);
                $dist->delete();
                $successCount++;
            } catch (\Illuminate\Database\QueryException $e) {
                // Error 1451: Constraint Fails (Data sedang digunakan di Jadwal/Nilai)
                if ($e->errorInfo[1] == 1451) {
                    $failCount++;
                }
            } catch (\Exception $e) {
                $failCount++;
            }
        }

        $message = "Berhasil menghapus $successCount data.";
        if ($failCount > 0) {
            $message .= " ($failCount data gagal dihapus karena sedang digunakan).";
            return redirect()->back()->with('warning', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    public function getCoursesByClass($classId)
    {
        $kelas = StudyClass::with('kurikulum')->find($classId);
        $courses = \App\Models\Course::where('kurikulum_id', $kelas->kurikulum_id)
            ->orderBy('semester', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($courses);
    }

    public function downloadTemplate()
    {
        return Excel::download(new CourseDistributionTemplateExport, 'template_distribusi.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'study_class_id' => 'required|exists:study_classes,id',
        ]);

        set_time_limit(300);

        try {
            Excel::import(new CourseDistributionImport($request->study_class_id), $request->file('file'));
            return back()->with('success', 'Import Berhasil!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function generate(Request $request)
    {
        $request->validate(['period_id' => 'required|exists:academic_periods,id']);
        $classes = StudyClass::where('academic_period_id', $request->period_id)->get();

        if ($classes->isEmpty()) {
            return back()->with('error', 'Tidak ada kelas pada periode ini. Import kelas dulu!');
        }

        $createdCount = 0;
        $existingCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $groupedClasses = $classes->groupBy(function ($item) {
                return $item->kurikulum_id . '-' . $item->semester . '-' . $item->prodi_id;
            });

            foreach ($groupedClasses as $groupKey => $classList) {
                $sampleClass = $classList->first();
                $courses = Course::where('kurikulum_id', $sampleClass->kurikulum_id)
                    ->where('semester', $sampleClass->semester)
                    ->get();

                if ($courses->isEmpty()) continue;

                foreach ($classList as $kelas) {
                    foreach ($courses as $course) {
                        $distribution = CourseDistribution::firstOrCreate(
                            [
                                'academic_period_id' => $request->period_id,
                                'study_class_id'     => $kelas->id,
                                'course_id'          => $course->id,
                            ],
                            [
                                //
                            ]
                        );

                        if ($distribution->wasRecentlyCreated) {
                            $createdCount++;
                        } else {
                            $existingCount++;
                        }
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            $message = "Sinkronisasi selesai! $createdCount data baru ditambahkan.";
            if ($existingCount > 0) {
                $message .= " ($existingCount data sudah ada/terupdate).";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal generate: ' . $e->getMessage());
        }
    }

    public function export(Request $request, $period_id)
    {
        $prodiId = $request->query('prodi_id');
        $semester = $request->query('semester');
        return Excel::download(
            new DistributionExport($period_id, $prodiId, $semester),
            'Distribusi Mata Kuliah_' . $period_id . '.xlsx'
        );
    }

    public function importUpdate(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new DistributionUpdateImport, $request->file('file'));
            return back()->with('success', 'Update Data Dosen Berhasil!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    public function submitToKaprodi(Request $request)
    {
        $request->validate([
            'period_id' => 'required',
            'prodi_id'  => 'required'
        ]);

        AprovalDocument::updateOrCreate(
            [
                'academic_period_id' => $request->period_id,
                'prodi_id'           => $request->prodi_id,
                'type'               => 'distribusi_matkul'
            ],
            [
                'status'            => 'submitted',
                'action_by_user_id' => Auth::id(),
                'feedback_message'  => null
            ]
        );

        return back()->with('success', 'Distribusi berhasil diajukan ke Kaprodi!');
    }

    public function printPdf($id)
    {
        $doc = AprovalDocument::with(['prodi', 'academicPeriod'])->findOrFail($id);

        if ($doc->status != 'approved_direktur') {
            return back()->with('error', 'Dokumen belum final, tidak bisa dicetak.');
        }

        $periodName = $doc->academicPeriod->name;
        $semesterLabel = str_contains(strtolower($periodName), 'ganjil') || str_ends_with($periodName, '1') ? 'Ganjil' : 'Genap';

        $tahunAkademik = $doc->academicPeriod->name;
        $tahunFile = str_replace(['/', '\\'], '-', $tahunAkademik);

        $dataIsi = \App\Models\CourseDistribution::with([
            'course',
            'studyClass.academicAdvisor', // Untuk Info PA di header
            'studyClass.prodi',
            // Koordinator
            'teachingLecturers',  // Pivot Real (PENTING)
            'pddiktiLecturers'    // Pivot PDDIKTI (PENTING)
        ])
            ->where('academic_period_id', $doc->academic_period_id)
            ->whereHas('studyClass', function ($q) use ($doc) {
                $q->where('prodi_id', $doc->prodi_id);
            })
            ->get()
            // KITA GROUP BY SEMESTER DULU UNTUK HALAMAN PDF
            ->groupBy('studyClass.semester');


        $pdf = PDF::loadView('content.dokumen.print.distribusi_pdf', compact(
            'doc',
            'semesterLabel',
            'tahunAkademik',
            'dataIsi'
        ));

        $pdf->setPaper('legal', 'landscape');

        return $pdf->download('Distribusi_Matkul_' . $doc->prodi->code . '_' . $tahunFile . '.pdf');
    }
}
