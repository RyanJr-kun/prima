<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Schedule;
use App\Models\TimeSlots;
use App\Models\CourseDistribution;
use App\Models\AcademicPeriod;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoScheduleService
{
    protected $logs = [];
    protected $successCount = 0;
    protected $failCount = 0;

    /**
     * Jalankan Generator Jadwal
     */
    public function generate($campus, $shift, $prodiId = null)
    {
        $this->logs = [];
        $this->successCount = 0;
        $this->failCount = 0;

        DB::beginTransaction();

        try {
            $activePeriodId = AcademicPeriod::where('is_active', true)->value('id');

            $approvedProdiIds = \App\Models\AprovalDocument::where('academic_period_id', $activePeriodId)
                ->where('type', 'distribusi_matkul')
                ->where('status', 'approved_direktur') // <--- KUNCI UTAMA
                ->pluck('prodi_id')
                ->toArray();

            // 1. AMBIL DATA DISTRIBUSI YANG BELUM TERJADWAL
            // Filter berdasarkan Kampus, Shift, dan Prodi (jika ada)
            if (empty($approvedProdiIds)) {
                return ['status' => false, 'message' => 'Belum ada Dokumen Distribusi Mata Kuliah yang disetujui Direktur.'];
            }

            $query = CourseDistribution::query()
                ->where('academic_period_id', $activePeriodId)
                ->has('teachingLecturers')
                ->doesntHave('schedule')
                ->with(['course', 'studyClass', 'teachingLecturers'])
                ->whereHas('studyClass', function ($q) use ($shift, $prodiId, $campus, $approvedProdiIds) {
                    $q->where('shift', $shift);
                    $q->whereIn('prodi_id', $approvedProdiIds);
                    if ($prodiId) {
                        $q->where('prodi_id', $prodiId);
                    }
                    $q->whereHas('prodi', fn($p) => $p->where('primary_campus', $campus));
                });

            $query->whereHas('course', function ($q) {
                $ignoredKeywords = ['Praktik Industri', 'Magang', 'Skripsi', 'Tugas Akhir', 'KKN', 'PKL'];

                $q->where(function ($sub) use ($ignoredKeywords) {
                    foreach ($ignoredKeywords as $keyword) {
                        $sub->where('name', 'NOT LIKE', "%{$keyword}%");
                    }
                });

                // Filter SKS menggunakan Raw SQL (sesuai perbaikan sebelumnya)
                $q->whereRaw('(COALESCE(sks_teori, 0) + COALESCE(sks_praktik, 0) + COALESCE(sks_lapangan, 0)) <= 6');
            });

            $distributions = $query->get();
            // ------------------------------------

            if ($distributions->isEmpty()) {
                return ['status' => false, 'message' => 'Tidak ada mata kuliah valid (Approved & Ada Dosen) yang perlu dijadwalkan.'];
            }

            // 2. SORTING (HEURISTIC)
            // Urutkan dari yang paling susah:
            // - Butuh Lab (Tags)
            // - SKS Terbesar (Durasi panjang susah cari slot)
            // - Jumlah Mahasiswa Terbanyak (Butuh ruang besar)
            $sortedDistributions = $distributions->sortByDesc(function ($dist) {
                $needsLab = str_contains(json_encode($dist->course->required_tags), 'lab') ? 1000 : 0;
                $sks = $dist->course->sks_total * 100;
                $students = $dist->studyClass->total_students;
                return $needsLab + $sks + $students;
            });

            // 3. SIAPKAN RESOURCES (RUANGAN & SLOT WAKTU) 
            $rooms = Room::where('location', $campus)
                ->orderBy('capacity', 'asc') // Cari ruangan yang "pas" dulu (Smallest Fit)
                ->get();

            if ($rooms->isEmpty()) {
                return ['status' => false, 'message' => "Gagal: Tidak ada ruangan ditemukan di lokasi $campus"];
            }

            // Ambil Slot Waktu Sesuai Shift
            $isKaryawan = ($shift === 'malam');
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $timeSlotsGrouped = [];
            foreach ($days as $day) {
                // Logika Sabtu Malam = Pagi (seperti request sebelumnya)
                $checkShift = ($day == 'Saturday' && $shift == 'malam') ? false : $isKaryawan;

                $slots = TimeSlots::forDay($day, $checkShift)
                    ->orderBy('start_time')
                    ->get();

                if ($slots->isNotEmpty()) {
                    $timeSlotsGrouped[$day] = $slots;
                }
            }

            if (empty($timeSlotsGrouped)) {
                return ['status' => false, 'message' => "Gagal: Tidak ada Time Slot (Jam) yang tersedia untuk shift $shift di database."];
            }

            // 4. ALGORITMA GREEDY
            foreach ($sortedDistributions as $dist) {
                $assigned = false;

                // Ambil Data Matkul
                $sks = $dist->course->sks_total;

                if ($sks > 1) {
                    $effectiveSks = $sks - 1;
                } else {
                    $effectiveSks = 1; // Minimal 1 sesi tatap muka
                }

                $requiredMinutes = $effectiveSks * 50;

                // Handle Tags
                $rawTags = $dist->course->required_tags;
                $requiredTags = is_array($rawTags) ? $rawTags : json_decode($rawTags ?? '[]', true);
                if (!is_array($requiredTags)) $requiredTags = [];

                $studentsCount = $dist->studyClass->total_students;
                $lecturerId = $dist->teachingLecturers->first()->id ?? null;
                $semester = $dist->studyClass->semester;
                $prodiIdClass = $dist->studyClass->prodi_id;

                // DIAGNOSTIC FLAGS
                $debugReasons = [];

                // LOOP HARI
                foreach ($timeSlotsGrouped as $day => $slots) {
                    if ($assigned) break;

                    // LOOP RUANGAN
                    foreach ($rooms as $room) {
                        if ($assigned) break;

                        // DIAGNOSA 1: KAPASITAS
                        if ($room->capacity < $studentsCount) {
                            $debugReasons['capacity'] = "Kapasitas ruangan kurang (Butuh: $studentsCount)";
                            continue;
                        }

                        // DIAGNOSA 2: TAGS / FASILITAS
                        $rawRoomTags = $room->facility_tags;
                        $roomTags = is_array($rawRoomTags) ? $rawRoomTags : json_decode($rawRoomTags ?? '[]', true);
                        if (!is_array($roomTags)) $roomTags = [];

                        if (!empty(array_diff($requiredTags, $roomTags))) {
                            $missing = implode(',', array_diff($requiredTags, $roomTags));
                            $debugReasons['tags'] = "Fasilitas tidak sesuai (Kurang: $missing)";
                            continue;
                        }

                        // LOOP SLOT WAKTU
                        foreach ($slots as $startIndex => $slot) {

                            // Hitung Durasi
                            $potentialSlots = [];
                            $accumulatedMinutes = 0;
                            $checkSlots = $slots->slice($startIndex);

                            // Variabel untuk cek urutan (Continuity Check)
                            $prevEndTime = null;
                            $isConsecutive = true;

                            foreach ($checkSlots as $checkSlot) {
                                // 1. CEK KESINAMBUNGAN WAKTU
                                // Jika ini bukan slot pertama, dan Start Time slot ini BEDA dengan End Time slot sebelumnya
                                // Berarti ada GAP (Istirahat atau Lompat Jam), maka berhenti.
                                if ($prevEndTime && $checkSlot->start_time != $prevEndTime) {
                                    $isConsecutive = false;
                                    break;
                                }

                                // 2. HITUNG DURASI YANG LEBIH AMAN
                                // Tambahkan tanggal hari ini agar Carbon tidak bingung konteks hari
                                $startC = Carbon::parse(date('Y-m-d') . ' ' . $checkSlot->start_time);
                                $endC   = Carbon::parse(date('Y-m-d') . ' ' . $checkSlot->end_time);

                                // Pakai abs() untuk memastikan positif
                                $dur = abs($endC->diffInMinutes($startC));

                                $accumulatedMinutes += $dur;
                                $potentialSlots[] = $checkSlot->id;
                                $prevEndTime = $checkSlot->end_time;

                                // Jika sudah cukup, berhenti loop checking
                                if ($accumulatedMinutes >= $requiredMinutes) break;
                            }

                            // DIAGNOSA 3: DURASI
                            if (!$isConsecutive || $accumulatedMinutes < $requiredMinutes) {
                                // Kita hanya log error jika ini percobaan di slot pagi (biar log tidak penuh sampah)
                                if ($startIndex == 0) {
                                    $debugReasons['duration'] = "Durasi/Urutan slot tidak valid (Butuh $requiredMinutes, Tersedia urut: $accumulatedMinutes)";
                                }
                                continue;
                            }

                            // DIAGNOSA 4: BENTROK JADWAL
                            if ($this->isSlotSafe($day, $potentialSlots, $room->id, $lecturerId, $dist->studyClass->id, $prodiIdClass, $semester)) {

                                // SUKSES!
                                Schedule::create([
                                    'course_distribution_id' => $dist->id,
                                    'study_class_id' => $dist->studyClass->id,
                                    'course_id' => $dist->course_id,
                                    'room_id' => $room->id,
                                    'user_id' => $lecturerId,
                                    'day' => $day,
                                    'time_slot_ids' => $potentialSlots
                                ]);

                                $this->successCount++;
                                $assigned = true;
                                break;
                            } else {
                                $debugReasons['clash'] = "Bentrok dengan jadwal lain (Dosen/Ruang/Semester)";
                            }
                        }
                    }
                }

                if (!$assigned) {
                    $this->failCount++;
                    // Ambil satu alasan terkuat
                    $mainReason = implode(' | ', array_unique($debugReasons));
                    $this->logs[] = "Gagal: {$dist->course->name} ($sks SKS) - {$mainReason}";
                }
            }

            DB::commit(); // Tetap commit yang sukses (kalau ada)

            return [
                'status' => true,
                'message' => "Selesai. Sukses: {$this->successCount}, Gagal: {$this->failCount}",
                'logs' => $this->logs
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => false, 'message' => 'Error System: ' . $e->getMessage()];
        }
    }

    /**
     * Cek Bentrok Lengkap
     */
    private function isSlotSafe($day, $slotIds, $roomId, $lecturerId, $classId, $prodiId, $semester)
    {
        // Query Jadwal Existing di Hari yang sama
        // Kita gunakan whereIn time_slot_ids agak tricky karena JSON array, 
        // tapi karena kita simpan array of int di kolom JSON/Text, kita bisa cek irisan.
        // CARA PALING EFEKTIF DI LARAVEL UNTUK ARRAY INTERSECT DI DB ADALAH PAKE `whereJsonContains` (Kalau MySQL 5.7+)
        // Atau ambil semua jadwal hari itu lalu filter di PHP (Lebih lambat tapi kompatibel).

        // Optimasi: Ambil jadwal hari itu, lalu cek PHP side.
        // Karena Auto Schedule ini batch process, load agak berat gapapa asal akurat.

        $existingSchedules = Schedule::with(['studyClass'])
            ->where('day', $day)
            ->get();

        foreach ($existingSchedules as $existing) {
            // Cek Irisan Waktu
            $intersect = array_intersect($existing->time_slot_ids ?? [], $slotIds);
            if (empty($intersect)) continue; // Waktunya beda, aman.

            // 1. Bentrok Ruangan
            if ($existing->room_id == $roomId) return false;

            // 2. Bentrok Dosen
            if ($lecturerId && $existing->user_id == $lecturerId) return false;

            // 3. Bentrok Kelas (Mahasiswanya sama)
            if ($existing->study_class_id == $classId) return false;

            // 4. BENTROK SEMESTER (PENTING!)
            // Anak Semester 1 Prodi TI tidak boleh punya 2 jadwal di jam yang sama
            if (
                $existing->studyClass->prodi_id == $prodiId &&
                $existing->studyClass->semester == $semester
            ) {
                return false;
            }
        }

        return true;
    }
}
