<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workload;
use Illuminate\Http\Request;
use App\Models\AcademicPeriod;
use App\Models\WorkloadActivitie;
use App\Models\CourseDistribution;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class WorkloadController extends Controller
{
    public function index()
    {
        // 1. Ambil Periode Aktif
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        // Validasi jika admin lupa set periode
        if (!$activePeriod) {
            return redirect()->back()->with('error', 'Periode akademik belum diaktifkan.');
        }

        // 2. Ambil Header BKD User yang Login (Bukan hardcode ID 1)
        $workload = Workload::where('user_id', Auth::user()->id)
            ->where('academic_period_id', $activePeriod->id)
            ->first();

        // 3. Ambil Rincian Kegiatan (Jika Workload sudah ada)
        // Kita pakai collect([]) kosong jika workload belum digenerate
        $activities = $workload
            ? WorkloadActivitie::where('workload_id', $workload->id)->get()
            : collect([]);

        // 4. Kirim ke View (Perhatikan syntax compact pakai string 'nama_variabel')
        return view('content.bkd.index', compact('activePeriod', 'workload', 'activities'));
    }
    public function generate(Request $request)
    {
        // 1. Ambil Periode Aktif
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        // Validasi: Cek jika admin lupa set periode aktif
        if (!$activePeriod) {
            return back()->with('error', 'Tidak ada Periode Akademik yang aktif saat ini. Hubungi Admin.');
        }

        $userId = Auth::id(); // Menggunakan Auth::id() lebih singkat
        $periodId = $activePeriod->id;

        // 2. Buat Header BKD (Workload) jika belum ada
        // PENTING: Bagian ini harus dieksekusi sebelum looping, agar $workload->id tersedia.
        $workload = Workload::firstOrCreate(
            ['academic_period_id' => $periodId, 'user_id' => $userId],
            [
                // Default value jika baru dibuat
                'total_sks_pendidikan' => 0,
                'conclusion' => 'belum_dihitung'
            ]
        );

        // 3. Ambil Data Distribusi (LOGIKA PIVOT TEAM TEACHING)
        // Mencari course_distribution yang di dalam teachingLecturers-nya ada ID user yang login
        $assignments = CourseDistribution::with(['course', 'studyClass'])
            ->where('academic_period_id', $periodId)
            ->whereHas('teachingLecturers', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get();

        // Jika kosong, berarti user belum diinput sebagai dosen pengajar di menu Distribusi Matkul
        if ($assignments->isEmpty()) {
            return back()->with('warning', 'Anda belum memiliki jadwal mengajar di periode ini. Pastikan data Pengajar sudah diimport.');
        }

        // 4. Proses Snapshot Data
        $count = 0;
        foreach ($assignments as $assign) {

            // Buat nama kegiatan unik: "Pemrograman Web - Kelas TRPL 1A"
            $activityName = $assign->course->name . ' - Kelas ' . $assign->studyClass->full_name;

            // Cek duplicate biar tidak double input jika tombol ditekan berkali-kali
            $exists = WorkloadActivitie::where('workload_id', $workload->id)
                ->where('activity_name', $activityName)
                ->exists();

            if (!$exists) {
                // INSERT KE TABEL BKD (SNAPSHOT)
                WorkloadActivitie::create([
                    'workload_id'   => $workload->id,
                    'category'      => 'pendidikan', // Otomatis masuk kategori PENDIDIKAN
                    'activity_name' => $activityName,

                    // Ambil SKS dari Master Matkul
                    // Catatan: Jika Team Teaching membagi SKS, dosen harus edit manual nilainya nanti
                    'sks_load'      => $assign->course->sks_teori + $assign->course->sks_praktik,

                    // Default Value (Bisa diedit Dosen nanti)
                    'realisasi_pertemuan' => 14,
                    'jenis_ujian'         => 'UTS, UAS'
                ]);
                $count++;
            }
        }

        // 5. Update Total SKS Pendidikan di Header Workload
        // Menghitung ulang total dari rincian yang baru saja dimasukkan
        $totalSks = WorkloadActivitie::where('workload_id', $workload->id)
            ->where('category', 'pendidikan')
            ->sum('sks_load');

        $workload->update(['total_sks_pendidikan' => $totalSks]);

        return back()->with('success', "Berhasil menarik $count mata kuliah ke Draft BKD.");
    }

    public function printRekapProdi(Request $request)
    {
        $prodiId = $request->prodi_id;
        $periodId = $request->period_id;

        // 1. Ambil Semua Dosen di Prodi Tersebut
        $dosens = User::where('prodi_id', $prodiId)->get();

        $laporan = [];

        foreach ($dosens as $dosen) {
            // 2. Ambil Workload Dosen Ini
            $workload = Workload::where('user_id', $dosen->id)
                ->where('academic_period_id', $periodId)
                ->first();

            if (!$workload) continue;

            // 3. Ambil Kegiatan PENDIDIKAN saja
            $kegiatan = WorkloadActivitie::where('workload_id', $workload->id)
                ->where('category', 'pendidikan')
                ->get();

            // 4. GROUPING BERDASARKAN NAMA MATKUL (Logic PDF Anda)
            // Matkul "Pemrograman Web" Kelas A dan Kelas B akan digabung
            $grouped = $kegiatan->groupBy(function ($item) {
                // Kita perlu ambil nama matkul murni (buang nama kelas)
                // Asumsi format: "Pemrograman Web - Kelas TRPL 1A"
                return explode(' - Kelas ', $item->activity_name)[0];
            });

            $laporan[] = [
                'dosen' => $dosen,
                'matkul_grouped' => $grouped
            ];
        }

        // Load View PDF
        $pdf = PDF::loadView('pdf.bkd_rekap', compact('laporan'));
        return $pdf->download('BKD_Rekap_Prodi.pdf');
    }

    public function updateAllActivities(Request $request)
    {
        $data = $request->activities; // Mengambil array data

        if (!$data) {
            return back()->with('warning', 'Tidak ada data yang disimpan.');
        }

        foreach ($data as $id => $values) {
            // Cari dan Update per baris
            $activity = WorkloadActivitie::find($id);

            // Pastikan activity ada dan milik user yang login (Security Check)
            if ($activity && $activity->workload->user_id == Auth::id()) {
                $activity->update([
                    'realisasi_pertemuan' => $values['realisasi_pertemuan'],
                    'jenis_ujian'         => $values['jenis_ujian']
                ]);
            }
        }

        return back()->with('success', 'Semua perubahan berhasil disimpan!');
    }
}
