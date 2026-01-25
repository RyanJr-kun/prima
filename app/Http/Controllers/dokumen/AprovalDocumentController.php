<?php

namespace App\Http\Controllers\dokumen;

use App\Models\Prodi;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Models\AprovalDocument;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PDF;

class AprovalDocumentController extends Controller
{
    public function index(Request $request)
    {
        $periods = AcademicPeriod::orderBy('name', 'desc')->get();

        if ($request->has('period_id')) {
            $activePeriod = $periods->where('id', $request->period_id)->first();
        } else {
            $activePeriod = $periods->where('is_active', true)->first() ?? $periods->first();
        }

        if (!$activePeriod) {
            return redirect()->back()->with('error', 'Belum ada data Periode Akademik.');
        }

        $query = AprovalDocument::with(['prodi', 'academicPeriod', 'lastActionUser'])
            ->where('academic_period_id', $activePeriod->id);

        // hak akses (siapa liat apa?)
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($user->hasRole('kaprodi')) {
            $prodiId = $user->managedProdi->id ?? 0;
            $query->where('prodi_id', $prodiId);
        } elseif ($user->hasRole(['wadir1', 'wadir2', 'direktur'])) {
            $query->where('status', '!=', 'draft');
        } else {
            //
        }

        $documents = $query->latest('updated_at')->get();
        $groupedDocs = $documents->groupBy('type');

        return view('content.dokumen.index', compact(
            'documents',
            'groupedDocs',
            'activePeriod',
            'periods'
        ));
    }

    public function show($id)
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function approve(Request $request, $id)
    {
        $doc = AprovalDocument::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $nextStatus = null;

        if ($user->hasRole('kaprodi') && $doc->status == 'submitted') {
            $nextStatus = 'approved_kaprodi';
        } elseif ($user->hasRole('wadir1') && $doc->status == 'approved_kaprodi') {
            $nextStatus = 'approved_wadir1';
        } elseif ($user->hasRole('wadir2') && $doc->status == 'approved_wadir1') {
            $nextStatus = 'approved_wadir2';
        } elseif ($user->hasRole('direktur') && $doc->status == 'approved_wadir2') {
            $nextStatus = 'approved_direktur';
        } else {
            return back()->with('error', 'Gagal: Status dokumen tidak sesuai atau Anda tidak memiliki akses.');
        }

        $doc->update([
            'status' => $nextStatus,
            'action_by_user_id' => $user->id,
            'feedback_message' => null
        ]);

        return back()->with('success', 'Dokumen berhasil disetujui dan diteruskan.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'feedback_message' => 'required|string|max:1000'
        ]);

        $doc = AprovalDocument::findOrFail($id);

        $doc->update([
            'status' => 'rejected',
            'feedback_message' => $request->feedback_message,
            'action_by_user_id' => Auth::id()
        ]);

        return back()->with('success', 'Dokumen dikembalikan untuk revisi.');
    }

    public function submit(Request $request)
    {

        $docId = $request->id ?? $request->document_id;

        $doc = AprovalDocument::findOrFail($docId);


        if (!in_array($doc->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Dokumen sedang dalam proses approval, tidak bisa disubmit ulang.');
        }

        $doc->update([
            'status' => 'submitted',
            'feedback_message' => null,
            'action_by_user_id' => Auth::id()
        ]);

        return back()->with('success', 'Dokumen berhasil diajukan ulang.');
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

        $dataIsi = [];
        if ($doc->type == 'distribusi_matkul') {
            $dataIsi = \App\Models\CourseDistribution::with(['course', 'user', 'studyClass'])
                ->where('academic_period_id', $doc->academic_period_id)
                ->whereHas('studyClass', function ($q) use ($doc) {
                    $q->where('prodi_id', $doc->prodi_id);
                })
                ->get()
                ->groupBy('studyClass.semester');
        }

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
