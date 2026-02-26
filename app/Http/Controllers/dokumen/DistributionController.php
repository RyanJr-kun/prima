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
use App\Models\AprovalDocument;
use Illuminate\Support\Facades\Auth;
use App\Notifications\DocumentActionNotification;
use Barryvdh\DomPDF\Facade\Pdf as PDF;


class DistributionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $periods = AcademicPeriod::orderBy('name', 'desc')->get();

        // Periode Aktif
        if ($request->has('period_id')) {
            $activePeriod = $periods->find($request->period_id);
        } else {
            $activePeriod = $periods->where('is_active', true)->first() ?? $periods->first();
        }

        if (!$activePeriod) {
            return redirect()->back()->with('error', 'Belum ada Periode Akademik yang tersedia!');
        }
        /** @var User $user */
        $isKaprodi = $user->hasRole('kaprodi');
        $managedProdiId = null;

        if ($isKaprodi) {
            $managedProdi = $user->managedProdi;
            if ($managedProdi) {
                $managedProdiId = $managedProdi->id;
                $request->merge(['prodi_id' => $managedProdiId]);
            }
        }

        // Query Data Distribusi
        $query = CourseDistribution::query()
            ->with([
                'studyClass.prodi',
                'studyClass.academicAdvisor',
                'studyClass.kurikulum',
                'course',
                'teachingLecturers',
                'pddiktiLecturers'
            ])
            ->where('academic_period_id', $activePeriod->id);

        // Filter Prodi admin kalo kaprodi otomatis.
        if ($request->filled('prodi_id')) {
            $query->whereHas('studyClass', function ($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        // Filter Semester
        if ($request->filled('semester')) {
            $query->whereHas('studyClass', function ($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        $distributions = $query->get()->sortBy([
            ['studyClass.prodi_id', 'asc'],
            ['studyClass.semester', 'asc'],
            ['studyClass.name', 'asc'],
            ['course.name', 'asc'],
        ]);

        // Data kelas sesuai filter
        $classesQuery = StudyClass::with('prodi')
            ->where('academic_period_id', $activePeriod->id);

        if ($request->filled('prodi_id')) {
            $classesQuery->where('prodi_id', $request->prodi_id);
        }
        $classes = $classesQuery->orderBy('name')->get(); // Data untuk Modal Generate

        // Data Prodi sesuai Filter
        if ($isKaprodi && $managedProdiId) {
            $prodis = Prodi::where('id', $managedProdiId)->get();
        } else {
            $prodis = Prodi::all();
        }

        $dosens = User::role('dosen')->select('id', 'name')->orderBy('name')->get();

        // Cek Status Dokumen Approval
        $documentStatus = 'draft';
        $documentData = null;
        $checkProdiId = $request->prodi_id;

        if ($checkProdiId) {
            $documentData = AprovalDocument::where([
                'academic_period_id' => $activePeriod->id,
                'prodi_id'           => $checkProdiId,
                'type'               => 'distribusi_matkul'
            ])->first();

            if ($documentData) {
                $documentStatus = $documentData->status;
            }
        }

        return view('content.distribution.index', compact(
            'distributions',
            'activePeriod',
            'periods',
            'prodis',
            'dosens',
            'classes',
            'documentData',
            'documentStatus',
            'isKaprodi'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'study_class_ids'   => 'required|array',
            'study_class_ids.*' => 'exists:study_classes,id',
            'course_id'         => 'required|exists:courses,id',
            'teaching_ids'      => 'nullable|array',
            'pddikti_ids'       => 'nullable|array',
            'referensi'         => 'nullable|string',
            'luaran'            => 'nullable|string',
        ]);

        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) {
            return back()->with('error', 'Gagal: Tidak ada Periode Akademik yang aktif.');
        }

        DB::beginTransaction();

        $successCount = 0;
        $skippedCount = 0;

        try {
            foreach ($request->study_class_ids as $classId) {

                // Cek Duplikasi (Agar tidak double matkul di kelas yg sama)
                $exists = CourseDistribution::where([
                    'academic_period_id' => $activePeriod->id,
                    'study_class_id'     => $classId,
                    'course_id'          => $request->course_id,
                ])->exists();

                if ($exists) {
                    $skippedCount++;
                    continue; // Lewati kelas ini jika sudah ada
                }

                // Buat Data Distribusi Utama
                $dist = CourseDistribution::create([
                    'academic_period_id' => $activePeriod->id,
                    'study_class_id'     => $classId,
                    'course_id'          => $request->course_id,
                    'referensi'          => $request->referensi,
                    'luaran'             => $request->luaran,
                ]);

                // Simpan Dosen ke Pivot Table
                // 1. Dosen Pengajar (Real Teaching)
                if ($request->teaching_ids) {
                    foreach ($request->teaching_ids as $uid) {
                        $dist->teachingLecturers()->attach($uid, ['category' => 'real_teaching']);
                    }
                }

                // 2. Dosen Pelapor (PDDIKTI)
                if ($request->pddikti_ids) {
                    foreach ($request->pddikti_ids as $uid) {
                        try {
                            $dist->pddiktiLecturers()->attach($uid, ['category' => 'pddikti_reporting']);
                        } catch (\Exception $e) {
                            // Ignore jika duplicate entry error (safety)
                        }
                    }
                }

                $successCount++;
            }

            DB::commit();

            // Feedback ke User
            if ($successCount == 0 && $skippedCount > 0) {
                return back()->with('error', 'Semua data gagal ditambahkan karena mata kuliah tersebut sudah ada di semua kelas yang dipilih.');
            }

            $msg = "Berhasil menambahkan $successCount distribusi.";
            if ($skippedCount > 0) {
                $msg .= " ($skippedCount data dilewati karena sudah ada).";
            }

            return redirect()
                ->route('distribusi-mata-kuliah.index')
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan server: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'study_class_ids' => 'required|array',
            'original_ids'    => 'nullable|array',
            'course_id'       => 'required',
            'teaching_ids'   => 'array',
            'pddikti_ids'    => 'array',
            'teaching_ids.*' => 'exists:users,id',
            'pddikti_ids.*'  => 'exists:users,id',

        ]);
        try {
            $dist = CourseDistribution::findOrFail($id);
            $newClassId = $request->study_class_ids[0];

            $dist->update([
                'study_class_id' => $newClassId,
                'course_id'      => $request->course_id,
                'referensi'      => $request->referensi,
                'luaran'         => $request->luaran,
            ]);

            $dist->allLecturers()->detach();

            if ($request->teaching_ids) {
                foreach ($request->teaching_ids as $uid) {
                    $dist->teachingLecturers()->attach($uid, ['category' => 'real_teaching']);
                }
            }
            if ($request->pddikti_ids) {
                foreach ($request->pddikti_ids as $uid) {
                    try {
                        $dist->pddiktiLecturers()->attach($uid, ['category' => 'pddikti_reporting']);
                    } catch (\Exception $e) {
                    }
                }
            }

            return back()->with('success', 'Data berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    private function syncLecturers($dist, $request)
    {
        $dist->allLecturers()->detach();

        // Pasang Dosen Pengajar
        if ($request->teaching_ids) {
            foreach ($request->teaching_ids as $uid) {
                $dist->teachingLecturers()->attach($uid, ['category' => 'real_teaching']);
            }
        }

        // Pasang Dosen PDDIKTI
        if ($request->pddikti_ids) {
            foreach ($request->pddikti_ids as $uid) {
                // Cek unique agar tidak error jika dosen sama
                try {
                    $dist->pddiktiLecturers()->attach($uid, ['category' => 'pddikti_reporting']);
                } catch (\Exception $e) {
                }
            }
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

    public function create()
    {
        //
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

    public function generate(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:academic_periods,id',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:study_classes,id'
        ]);

        $classes = StudyClass::whereIn('id', $request->class_ids)
            ->where('academic_period_id', $request->period_id)
            ->get();

        if ($classes->isEmpty()) {
            return back()->with('error', 'Tidak ada data kelas valid yang dipilih.');
        }

        $createdCount = 0;
        $existingCount = 0;

        DB::beginTransaction();

        try {
            $groupedClasses = $classes->groupBy(function ($item) {
                return $item->kurikulum_id . '-' . $item->semester;
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
                                // Optional: default values (user_id kosong dulu)
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

            DB::commit();

            $message = "Generate sukses! $createdCount item baru.";
            if ($existingCount > 0) {
                $message .= " ($existingCount item dilewati karena sudah ada).";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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

        $doc = AprovalDocument::updateOrCreate(
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

        $prodi = Prodi::find($request->prodi_id);

        if ($prodi && $prodi->kaprodi_id) {

            $kaprodi = User::find($prodi->kaprodi_id);
            if ($kaprodi && $kaprodi->id !== Auth::id()) {

                // Di Controller, ubah bagian notifikasi jadi begini:
                // try {
                //     $kaprodi->notify(new DocumentActionNotification($doc, 'submitted', Auth::user()->name));
                //     dd("Email berhasil dikirim (seharusnya)!"); // Cek apakah script sampai sini
                // } catch (\Exception $e) {
                //     dd($e->getMessage()); // Ini akan menampilkan error SMTP di layar jika ada
                // }

                $kaprodi->notify(new DocumentActionNotification($doc, 'submitted', Auth::user()->name));
            }
        }

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
