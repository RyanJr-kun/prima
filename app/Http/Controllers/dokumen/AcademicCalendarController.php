<?php

namespace App\Http\Controllers\dokumen;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Models\AprovalDocument;
use App\Models\AcademicCalendar;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Notifications\DocumentActionNotification;

class AcademicCalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        if (!$activePeriod) {
            return redirect()->back()->with('error', 'Belum ada Periode Akademik yang aktif.');
        }

        $events = AcademicCalendar::where('academic_period_id', $activePeriod->id)
            ->orderBy('start_date')
            ->get();

        $approvalDoc = AprovalDocument::where('academic_period_id', $activePeriod->id)
            ->where('type', 'kalender_akademik')
            ->whereNull('prodi_id')
            ->first();

        $availableSemesters = [1, 2, 3, 4, 5, 6, 7, 8];

        if ($activePeriod) {
            if (stripos($activePeriod->name, 'Ganjil') !== false) {
                $availableSemesters = [1, 3, 5, 7];
            } elseif (stripos($activePeriod->name, 'Genap') !== false) {
                $availableSemesters = [2, 4, 6, 8];
            }
        }

        return view('content.calendar.index', compact('activePeriod', 'events', 'approvalDoc', 'availableSemesters'));
    }

    public function getEvents(Request $request)
    {
        $activePeriodId = AcademicPeriod::where('is_active', true)->value('id');

        $events = AcademicCalendar::where('academic_period_id', $activePeriodId)
            ->get()
            ->map(function ($event) {
                $color = empty($event->target_semesters) ? '#696cff' : '#71dd37';

                return [
                    'id' => $event->id,
                    'title' => $event->name,
                    'start' => $event->start_date->format('Y-m-d'),
                    'end' => $event->end_date ? $event->end_date->format('Y-m-d') : null,
                    'description' => $event->description,
                    'semesters' => $event->target_semesters,
                    'backgroundColor' => $color,
                    'borderColor' => $color
                ];
            });

        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi (Otomatis redirect kembali jika gagal)
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_semesters' => 'nullable|array',
            'description' => 'nullable|string'
        ]);

        try {
            $activePeriodId = AcademicPeriod::where('is_active', true)->value('id');

            AcademicCalendar::create([
                'academic_period_id' => $activePeriodId,
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'target_semesters' => $request->input('target_semesters', []),
                'description' => $request->description,
            ]);

            // UBAH DI SINI: Redirect Back dengan Pesan Sukses
            return redirect()->back()->with('success', 'Agenda berhasil disimpan!');
        } catch (\Exception $e) {
            // Redirect Back dengan Pesan Error
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $event = AcademicCalendar::findOrFail($id);
        $event->delete();

        // UBAH DI SINI: Redirect Back
        return redirect()->back()->with('success', 'Agenda berhasil dihapus!');
    }

    public function submitValidation(Request $request)
    {
        $activePeriodId = AcademicPeriod::where('is_active', true)->value('id');

        // 1. Validasi Isi Kalender
        $count = AcademicCalendar::where('academic_period_id', $activePeriodId)->count();
        if ($count == 0) {
            return back()->with('error', 'Tidak bisa mengajukan! Data kalender masih kosong.');
        }

        // 2. Simpan/Update Dokumen Approval
        // Simpan ke variabel $doc untuk dikirim ke notifikasi
        $doc = AprovalDocument::updateOrCreate(
            [
                'academic_period_id' => $activePeriodId,
                'type' => 'kalender_akademik',
                'prodi_id' => null, // Global Campus Document
            ],
            [
                'status' => 'submitted', // Status naik ke Submitted
                'action_by_user_id' => Auth::id(), // Siapa yang mengajukan
                'feedback_message' => null // Reset feedback revisi jika ada
            ]
        );

        // 3. LOGIC NOTIFIKASI KE WADIR 1
        $currentUser = Auth::user();

        // Cari user dengan role 'wadir1'
        $wadir1 = User::role('wadir1')->first();

        // Kirim notif jika Wadir 1 ditemukan & bukan user yang sedang submit
        if ($wadir1 && $wadir1->id !== $currentUser->id) {
            $wadir1->notify(new DocumentActionNotification(
                $doc,
                'submitted',
                $currentUser->name
            ));
        }

        return back()->with('success', 'Kalender Akademik berhasil diajukan ke Wadir 1!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $doc = AprovalDocument::with(['academicPeriod', 'lastActionUser'])->findOrFail($id);
        $events = AcademicCalendar::where('academic_period_id', $doc->academic_period_id)
            ->orderBy('start_date')
            ->get();

        return view('content.calendar.show', compact('doc', 'events'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_semesters' => 'nullable|array',
            'description' => 'nullable|string'
        ]);

        try {
            $event = AcademicCalendar::findOrFail($id);

            $event->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'target_semesters' => $request->input('target_semesters', []),
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Agenda berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function printPdf($id)
    {
        $doc = AprovalDocument::with(['academicPeriod', 'lastActionUser'])->findOrFail($id);
        $events = AcademicCalendar::where('academic_period_id', $doc->academic_period_id)
            ->orderBy('start_date')
            ->get();

        $wadir1 = User::role('wadir1')->first();
        $direktur = User::role('direktur')->first();

        $pdf = PDF::loadView('content.dokumen.print.kalender_pdf', [
            'doc' => $doc,
            'events' => $events,
            'tahunAkademik' => $doc->academicPeriod->name,
            'wadir1' => $wadir1,
            'direktur' => $direktur
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Kalender_Akademik_' . str_replace('/', '-', $doc->academicPeriod->name) . '.pdf');
    }
}
