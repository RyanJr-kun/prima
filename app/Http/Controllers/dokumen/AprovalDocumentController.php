<?php

namespace App\Http\Controllers\dokumen;


use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\DocumentActionNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\AcademicPeriod;
use App\Models\AprovalDocument;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


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
            $query->where(function ($q) use ($prodiId) {
                $q->where('prodi_id', $prodiId)
                    ->orWhereNull('prodi_id'); // Dokumen Global (Kalender Akademik)
            });
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
        $doc = AprovalDocument::with('prodi')->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $currentStatus = $doc->status;
        $nextStatus = null;
        $feedbackMsg = null;

        if (in_array($doc->type, ['kalender_akademik', 'jadwal_perkuliahan'])) {
            //global dokumen
            //Alur: admin/baak -> Wadir 1 -> Direktur
            if ($user->hasRole('wadir1') && $currentStatus == 'submitted') {
                $nextStatus = 'approved_wadir1';
                $feedbackMsg = 'Disetujui Wadir 1. Menunggu Direktur.';
            } elseif ($user->hasRole('direktur') && $currentStatus == 'approved_wadir1') {
                $nextStatus = 'approved_direktur';
                $feedbackMsg = 'Dokumen Disahkan oleh Direktur!';
            }
        } else {
            // distribusi matkul + bkd
            // Alur: Kaprodi -> Wadir 1 -> Wadir 2 -> Direktur
            if ($user->hasRole('kaprodi') && $currentStatus == 'submitted') {
                $nextStatus = 'approved_kaprodi';
            } elseif ($user->hasRole('wadir1') && $currentStatus == 'approved_kaprodi') {
                $nextStatus = 'approved_wadir1';
            } elseif ($user->hasRole('wadir2') && $currentStatus == 'approved_wadir1') {
                $nextStatus = 'approved_wadir2';
            } elseif ($user->hasRole('direktur') && $currentStatus == 'approved_wadir2') {
                $nextStatus = 'approved_direktur';
            }
        }

        if (!$nextStatus) {
            return back()->with('error', 'Gagal: Status dokumen tidak sesuai atau Anda tidak memiliki akses approval.');
        }

        $doc->update([
            'status' => $nextStatus,
            'action_by_user_id' => $user->id,
            'feedback_message' => null
        ]);

        if ($doc->prodi_id) {
            $kaprodi = User::find($doc->prodi->kaprodi_id);
            if ($kaprodi && $kaprodi->id != $user->id) {
                $kaprodi->notify(new DocumentActionNotification($doc, 'approved', $user->name));
            }
        } else {
            if ($nextStatus == 'approved_direktur') {
                $wadir1 = User::role('wadir1')->first();
                if ($wadir1 && $wadir1->id != $user->id) {
                    $wadir1->notify(new DocumentActionNotification($doc, 'approved', $user->name));
                }
            }
        }

        $nextRole = null;

        switch ($nextStatus) {
            case 'approved_kaprodi':
                $nextRole = 'wadir1';
                break;

            case 'approved_wadir1':
                $docsDirectToDirector = ['jadwal_perkuliahan', 'kalender_akademik'];

                if (in_array($doc->type, $docsDirectToDirector)) {

                    $nextRole = 'direktur';
                } else {
                    $nextRole = 'wadir2';
                }
                break;

            case 'approved_wadir2':
                $nextRole = 'direktur';
                break;
        }

        // Kirim Notif ke Next Role
        if ($nextRole) {
            $receivers = User::role($nextRole)->get();
            Notification::send($receivers, new DocumentActionNotification($doc, 'submitted', $user->name));
        }

        $msg = $feedbackMsg ?? 'Dokumen berhasil disetujui dan diteruskan.';
        return back()->with('success', $msg);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'feedback_message' => 'required|string|max:1000'
        ]);

        $doc = AprovalDocument::with('prodi')->findOrFail($id);
        $user = Auth::user();

        $doc->update([
            'status' => 'rejected',
            'feedback_message' => $request->feedback_message,
            'action_by_user_id' => $user->id
        ]);

        if ($doc->prodi_id) {
            $kaprodi = User::find($doc->prodi->kaprodi_id);
            if ($kaprodi) {
                $kaprodi->notify(new DocumentActionNotification($doc, 'rejected', $user->name));
            }
        } elseif ($doc->type == 'kalender_akademik') {
            $wadir1 = User::role('wadir1')->first();
            if ($wadir1) {
                $wadir1->notify(new DocumentActionNotification($doc, 'rejected', $user->name));
            }
        }

        $admins = User::role(['admin', 'baak'])->get();
        $adminsToNotify = $admins->reject(function ($admin) use ($user) {
            return $admin->id === $user->id;
        });

        if ($adminsToNotify->isNotEmpty()) {
            Notification::send($adminsToNotify, new DocumentActionNotification($doc, 'rejected', $user->name));
        }

        return back()->with('success', 'Dokumen dikembalikan untuk revisi.');
    }

    public function submit(Request $request)
    {

        $doc = AprovalDocument::findOrFail($request->id ?? $request->document_id);

        if (!in_array($doc->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Dokumen sedang dalam proses approval, tidak bisa disubmit ulang.');
        }

        $doc->update([
            'status' => 'submitted',
            'feedback_message' => null,
            'action_by_user_id' => Auth::id()
        ]);


        $user = Auth::user();

        $targetRole = 'wadir1';
        if ($doc->type == 'kalender_akademik') $targetRole = 'wadir1';

        $receivers = User::role($targetRole)->get();
        Notification::send($receivers, new DocumentActionNotification($doc, 'submitted', $user->name));

        return back()->with('success', 'Dokumen berhasil diajukan ulang.');
    }
}
